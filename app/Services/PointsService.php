<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Single entry point for any point-balance change in the app.
 *
 * Guarantees:
 *   1. **Atomic** — DB transaction wraps the insert + user.reputation update.
 *   2. **Idempotent at the DB level** — UNIQUE constraint on
 *      (user_id, reason, target_type, target_id) catches duplicate awards
 *      even if two requests race past the service layer.
 *   3. **Audit-logged** — every change leaves a row, with `meta` JSON
 *      capturing IP and any reviewer-relevant context.
 *   4. **Reversible** — admin can call `revoke()` which writes a negating
 *      transaction (never deletes history).
 *
 * Earning rules table (single source of truth for caps):
 *   RULES[code] => ['delta' => int, 'daily_cap' => int|null]
 *
 * Server-side checks (defense in depth):
 *   - Only OTP-verified users can earn or spend (bot cost)
 *   - daily_cap is enforced by counting today's same-reason rows for the user
 *   - Self-targets (user voting their own post) are not allowed by caller
 */
class PointsService
{
    /** Canonical rules. Adding a code here is the ONLY place to define a reward. */
    public const RULES = [
        'signup'             => ['delta' => 50,   'daily_cap' => 1],   // once-ever via DB unique
        'daily_login'        => ['delta' => 2,    'daily_cap' => 1],
        'first_alert'        => ['delta' => 25,   'daily_cap' => 1],
        'alert_confirmed'    => ['delta' => 10,   'daily_cap' => 3],   // per confirmed alert, max 3/day
        'review_business'    => ['delta' => 5,    'daily_cap' => 5],
        'business_claimed'   => ['delta' => 200,  'daily_cap' => null],
        'business_verified'  => ['delta' => 100,  'daily_cap' => null],
        'business_quality'   => ['delta' => 50,   'daily_cap' => null],
        'invite_settled'     => ['delta' => 30,   'daily_cap' => 5],
        'admin_award'        => ['delta' => 0,    'daily_cap' => null], // delta passed in
        'spam_penalty'       => ['delta' => -30,  'daily_cap' => null],

        // Spends — delta MUST be negative in the rule table
        'spend_promote_7'    => ['delta' => -500,  'daily_cap' => null],
        'spend_promote_30'   => ['delta' => -1800, 'daily_cap' => null],
        'spend_avatar_frame' => ['delta' => -200,  'daily_cap' => null],
        'spend_silver_badge' => ['delta' => -1000, 'daily_cap' => null],
        'spend_skip_queue'   => ['delta' => -100,  'daily_cap' => null],
        'spend_market_feat'  => ['delta' => -300,  'daily_cap' => null],
    ];

    /** Anti-abuse: must be verified (tier != none) to earn anything but signup. */
    private const VERIFIED_TIERS = ['bronze', 'silver', 'gold'];

    /**
     * Award (or charge) points to a user.
     *
     * @param  User    $user    Recipient
     * @param  string  $reason  Code from self::RULES
     * @param  mixed   $target  Eloquent model (uses class + id) or null
     * @param  int|null $delta  Override (only used when rule allows, e.g. admin_award)
     * @param  array   $meta    Extra audit context
     * @return PointTransaction|null  null = silently ignored (duplicate, capped, ungated)
     */
    public static function award(
        User $user,
        string $reason,
        $target = null,
        ?int $delta = null,
        array $meta = []
    ): ?PointTransaction {
        if (! isset(self::RULES[$reason])) {
            throw new \InvalidArgumentException("Unknown points reason: {$reason}");
        }
        $rule = self::RULES[$reason];

        // Gate: only OTP-activated users earn (signup itself is the exception)
        if ($reason !== 'signup' && ! in_array($user->verification_tier, self::VERIFIED_TIERS, true)) {
            return null;
        }

        // Determine delta
        $finalDelta = $delta !== null && in_array($reason, ['admin_award'], true)
            ? $delta
            : $rule['delta'];
        if ($finalDelta === 0) return null;

        // Target polymorphism — when null, store sentinel "self:0" so the unique
        // constraint still has a stable shape (NULL columns in MySQL allow duplicates)
        if ($target instanceof \Illuminate\Database\Eloquent\Model) {
            $targetType = class_basename($target);
            $targetId   = (int) $target->getKey();
        } else {
            $targetType = 'self';
            $targetId   = $user->id;       // makes "daily_login self:userId" unique-per-user
        }

        // Daily cap (per-reason rolling 24h)
        if ($rule['daily_cap'] !== null) {
            // For daily_login we want one-per-calendar-date, not 24h sliding.
            // Implement via a calendar-date target on the same row.
            if ($reason === 'daily_login') {
                $targetType = 'date';
                $targetId   = (int) now()->format('Ymd');     // YYYYMMDD as int
            } else {
                $count = PointTransaction::where('user_id', $user->id)
                    ->where('reason', $reason)
                    ->where('created_at', '>=', now()->subDay())
                    ->count();
                if ($count >= $rule['daily_cap']) {
                    return null;
                }
            }
        }

        // Atomic insert + balance update; UNIQUE blocks dupes at the DB layer
        try {
            $tx = DB::transaction(function () use ($user, $reason, $finalDelta, $targetType, $targetId, $meta) {
                // Lock the user row so reputation can't go negative under race
                $locked = User::where('id', $user->id)->lockForUpdate()->first();
                if (! $locked) return null;

                // Spends cannot drop below 0
                if ($finalDelta < 0 && ($locked->reputation + $finalDelta) < 0) {
                    return null;
                }

                $tx = PointTransaction::create([
                    'user_id'     => $user->id,
                    'delta'       => $finalDelta,
                    'reason'      => $reason,
                    'target_type' => $targetType,
                    'target_id'   => $targetId,
                    'meta'        => $meta + [
                        'ip' => request()?->ip(),
                    ],
                    'settled'     => true,
                ]);

                $locked->increment('reputation', $finalDelta);
                return $tx;
            });
        } catch (QueryException $e) {
            // 23000 = integrity constraint violation — i.e. UNIQUE blocked a dupe
            if ($e->getCode() === '23000') return null;
            throw $e;
        }

        // After a positive earn, check if this awardee crossed the referral
        // settlement threshold (50+ organic pts) → pay out their inviter.
        // Skip when the reason itself is `invite_settled` to avoid recursion.
        if ($tx && $finalDelta > 0 && $reason !== 'invite_settled') {
            ReferralService::maybeSettle($user->fresh());
        }

        return $tx;
    }

    /** Admin reversal — writes a negating row, never deletes history. */
    public static function revoke(PointTransaction $tx, ?string $note = null): PointTransaction
    {
        return DB::transaction(function () use ($tx, $note) {
            $user = User::lockForUpdate()->find($tx->user_id);
            $reversed = PointTransaction::create([
                'user_id'     => $tx->user_id,
                'delta'       => -$tx->delta,
                'reason'      => 'admin_revoke',
                'target_type' => 'tx',
                'target_id'   => $tx->id,
                'meta'        => ['note' => $note, 'ip' => request()?->ip()],
                'settled'     => true,
            ]);
            $user->decrement('reputation', $tx->delta);
            return $reversed;
        });
    }

    /** Current balance, computed fresh from the log (use sparingly — prefer cached column). */
    public static function balance(User $user): int
    {
        return (int) PointTransaction::where('user_id', $user->id)->sum('delta');
    }
}
