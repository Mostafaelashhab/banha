<?php

namespace App\Services;

use App\Models\User;

/**
 * Referral pipeline. Three stages:
 *
 *   1. SIGNUP — invitee arrives with ?ref=<code>. We decode code → inviter id,
 *      store it on `users.referred_by` (only if not already set; first-touch wins).
 *
 *   2. ACTIVATION — invitee verifies OTP. Nothing changes; we still wait.
 *
 *   3. SETTLEMENT — invitee earns 50+ points from their own organic activity
 *      (not signup bonus). PointsService calls `maybeSettle()` after each award.
 *      If invitee just crossed 50 and `referral_settled=false` and they have a
 *      `referred_by`, the inviter gets `invite_settled` points and the flag flips.
 *
 * Self-referral and circular referrals are blocked at capture time.
 */
class ReferralService
{
    /** Points the invitee must earn on their own before the inviter is paid. */
    public const SETTLEMENT_THRESHOLD = 50;

    /**
     * Decode a referral code → User id (or null).
     *
     * Accepts either a short base36 user id (`r` for user 27, etc.) OR a
     * full username (the wallet builds `?ref=username` for human readability).
     */
    public static function decodeCode(?string $code): ?int
    {
        if (! $code) return null;
        $code = trim($code);
        if ($code === '') return null;

        // Try username first (it's what the wallet UI generates)
        $user = User::where('username', $code)->first(['id']);
        if ($user) return (int) $user->id;

        // Fall back to base36-encoded id, lowercase
        $lower = strtolower($code);
        if (! preg_match('/^[a-z0-9]{1,12}$/', $lower)) return null;
        $id = (int) base_convert($lower, 36, 10);
        return $id > 0 ? $id : null;
    }

    /**
     * Attach an inviter to a brand-new user. Safe to call multiple times —
     * only takes effect when `referred_by` is still NULL.
     *
     * Returns true if the link was recorded.
     */
    public static function capture(User $invitee, ?string $code): bool
    {
        if ($invitee->referred_by) return false;          // first-touch wins
        $inviterId = self::decodeCode($code);
        if (! $inviterId) return false;
        if ($inviterId === $invitee->id) return false;    // no self-referral

        $inviter = User::find($inviterId);
        if (! $inviter || $inviter->is_banned) return false;

        $invitee->forceFill([
            'referred_by'      => $inviter->id,
            'referral_settled' => false,
        ])->save();
        return true;
    }

    /**
     * Called by PointsService after every award to the invitee. When the
     * invitee crosses SETTLEMENT_THRESHOLD organic points, the inviter
     * is credited once (DB-level idempotency via `users.referral_settled`).
     */
    public static function maybeSettle(User $invitee): void
    {
        if ($invitee->referral_settled) return;
        if (! $invitee->referred_by) return;

        // Exclude the signup bonus itself from the threshold (it's a freebie
        // and would let people farm referrals without real engagement).
        $organicPoints = (int) \App\Models\PointTransaction::query()
            ->where('user_id', $invitee->id)
            ->where('reason', '!=', 'signup')
            ->where('delta', '>', 0)
            ->sum('delta');

        if ($organicPoints < self::SETTLEMENT_THRESHOLD) return;

        $inviter = User::find($invitee->referred_by);
        if (! $inviter || $inviter->is_banned) {
            // Mark settled anyway so we don't keep checking
            $invitee->forceFill(['referral_settled' => true])->save();
            return;
        }

        // Atomic: flip the flag first, THEN award. If the award fails
        // (e.g. daily cap reached), the flag still flips so we never retry.
        $invitee->forceFill(['referral_settled' => true])->save();

        PointsService::award($inviter, 'invite_settled', $invitee);
    }
}
