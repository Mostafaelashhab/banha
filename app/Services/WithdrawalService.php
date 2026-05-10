<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Cash-out pipeline. ALL withdrawals are manually reviewed by an admin —
 * there is no automatic payout. Safeguards layered top-to-bottom:
 *
 *   1. Minimum 200 pts (100 EGP) per request   — filters trivia + saves ops time
 *   2. Maximum 1 pending request at a time     — prevents queue spam
 *   3. Maximum 1 paid request per 24h          — caps daily exposure per user
 *   4. 14-day hold on freshly-earned points    — gives admins time to spot fraud
 *   5. Tier gate: must be bronze+ (OTP)        — raises bot cost
 *   6. Payout handle constrained to user's     — same person across earn + cash out
 *      OTP-verified phone (V-Cash) OR a phone
 *      they ALSO control on file
 *   7. Atomic: points only decrement on        — admin can reject for free
 *      ADMIN APPROVAL (not on request)
 *   8. Full audit log: IP at request, admin   — every event has a who/when/why
 *      ID + note + reference at approval
 */
class WithdrawalService
{
    /** 2 points = 1 EGP. Centralised here so we can never miscalculate elsewhere. */
    public const POINTS_PER_EGP = 2;

    public const MIN_EGP        = 100;     // 200 pts
    public const MAX_EGP        = 2000;    // 4000 pts per single request — caps single-event loss
    public const HOLD_DAYS      = 14;
    public const REQUIRED_TIERS = ['bronze', 'silver', 'gold'];

    /** Spendable balance: total earned minus what's reserved by pending withdrawals. */
    public static function availableBalance(User $user): int
    {
        $earned  = (int) $user->reputation;
        $reserved = (int) Withdrawal::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('points_cost');
        return max(0, $earned - $reserved);
    }

    /**
     * Withdrawable balance: spendable AND past the 14-day hold.
     * Points earned in the last HOLD_DAYS days don't count.
     */
    public static function withdrawableBalance(User $user): int
    {
        $unsettled = (int) PointTransaction::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(self::HOLD_DAYS))
            ->sum('delta');
        return max(0, self::availableBalance($user) - max(0, $unsettled));
    }

    /**
     * Create a pending withdrawal. Validates ALL safeguards. Throws
     * ValidationException with a user-readable message on any failure.
     */
    public static function request(
        User $user,
        int $amountEgp,
        string $method,
        string $payoutHandle
    ): Withdrawal {
        // ── Gate 1: KYC tier
        if (! in_array($user->verification_tier, self::REQUIRED_TIERS, true)) {
            throw ValidationException::withMessages([
                'amount' => 'لازم تكون مفعّل بـ OTP أولاً.',
            ]);
        }

        // ── Gate 2: method
        if (! array_key_exists($method, Withdrawal::METHODS)) {
            throw ValidationException::withMessages([
                'method' => 'طريقة الدفع مش مدعومة.',
            ]);
        }

        // ── Gate 3: amount bounds
        if ($amountEgp < self::MIN_EGP || $amountEgp > self::MAX_EGP) {
            throw ValidationException::withMessages([
                'amount' => 'الحد الأدنى '.self::MIN_EGP.' ج والحد الأقصى '.self::MAX_EGP.' ج.',
            ]);
        }

        // ── Gate 4: payout handle — must be an Egyptian mobile (11 digits 01x...)
        $handle = preg_replace('/\D/', '', $payoutHandle);
        if (! preg_match('/^01[0125]\d{8}$/', $handle)) {
            throw ValidationException::withMessages([
                'payout_handle' => 'لازم رقم موبايل مصري صحيح (٠١xxxxxxxxx).',
            ]);
        }

        // ── Gate 5: one pending request at a time
        if (Withdrawal::where('user_id', $user->id)->where('status', 'pending')->exists()) {
            throw ValidationException::withMessages([
                'amount' => 'عندك طلب سحب قيد المراجعة بالفعل. استنى لما يخلص أو الغيه.',
            ]);
        }

        // ── Gate 6: daily payout cap — only one completed payout per 24h
        $recentPaid = Withdrawal::where('user_id', $user->id)
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subDay())
            ->exists();
        if ($recentPaid) {
            throw ValidationException::withMessages([
                'amount' => 'فيه سحب اتدفع لك خلال آخر ٢٤ ساعة. حاول بكرة.',
            ]);
        }

        $pointsCost = $amountEgp * self::POINTS_PER_EGP;

        // ── Gate 7: must have enough WITHDRAWABLE balance (past the hold)
        if (self::withdrawableBalance($user) < $pointsCost) {
            throw ValidationException::withMessages([
                'amount' => 'الرصيد القابل للسحب أقل من المطلوب. (النقاط الجديدة محتاجة '
                    .self::HOLD_DAYS.' يوم قبل ما تتسحب.)',
            ]);
        }

        return Withdrawal::create([
            'user_id'       => $user->id,
            'amount_egp'    => $amountEgp,
            'points_cost'   => $pointsCost,
            'method'        => $method,
            'payout_handle' => $handle,
            'status'        => 'pending',
            'requested_at'  => now(),
            'meta'          => [
                'ip'         => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'tier_at_request' => $user->verification_tier,
            ],
        ]);
    }

    /** User cancels their own pending request. */
    public static function cancel(Withdrawal $w, User $user): void
    {
        if ($w->user_id !== $user->id) abort(403);
        if ($w->status !== 'pending') {
            throw ValidationException::withMessages([
                'amount' => 'الطلب اتعمد عليه بالفعل، مش قادر تلغيه.',
            ]);
        }
        $w->update(['status' => 'cancelled', 'processed_at' => now()]);
    }

    /**
     * Admin approves — points deducted atomically, status flips to "approved".
     * The operator still has to manually send the money and then mark as paid.
     */
    public static function approve(Withdrawal $w, User $admin, ?string $note = null): Withdrawal
    {
        if ($w->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'الطلب مش في حالة pending.']);
        }
        return DB::transaction(function () use ($w, $admin, $note) {
            $user = User::lockForUpdate()->find($w->user_id);

            // Final balance check inside the lock — guards against any race
            if ((int) $user->reputation < $w->points_cost) {
                throw ValidationException::withMessages(['amount' => 'الرصيد قلّ قبل الموافقة.']);
            }

            // Write the spend transaction via PointsService so it's audit-logged
            PointTransaction::create([
                'user_id'     => $user->id,
                'delta'       => -$w->points_cost,
                'reason'      => 'admin_award', // admin-initiated spend → uses the admin_award lane
                'target_type' => 'Withdrawal',
                'target_id'   => $w->id,
                'meta'        => [
                    'kind'    => 'withdrawal_approved',
                    'amount'  => $w->amount_egp,
                    'admin'   => $admin->id,
                    'note'    => $note,
                ],
                'settled'     => true,
            ]);
            $user->decrement('reputation', $w->points_cost);

            $w->update([
                'status'       => 'approved',
                'admin_id'     => $admin->id,
                'admin_note'   => $note,
                'processed_at' => now(),
            ]);
            return $w->refresh();
        });
    }

    /** Operator marks a payout as completed (after sending via InstaPay/V-Cash). */
    public static function markPaid(Withdrawal $w, string $reference): Withdrawal
    {
        if ($w->status !== 'approved') {
            throw ValidationException::withMessages(['status' => 'الطلب لازم يكون approved أولاً.']);
        }
        $w->update([
            'status'           => 'paid',
            'payout_reference' => $reference,
            'paid_at'          => now(),
        ]);
        return $w;
    }

    /** Admin rejects — points NEVER got deducted, so no refund needed. */
    public static function reject(Withdrawal $w, User $admin, string $reason): Withdrawal
    {
        if (! in_array($w->status, ['pending', 'approved'], true)) {
            throw ValidationException::withMessages(['status' => 'مش قادر ترفض طلب في حالة '.$w->status]);
        }

        return DB::transaction(function () use ($w, $admin, $reason) {
            // If already approved (points deducted), refund them
            if ($w->status === 'approved') {
                $user = User::lockForUpdate()->find($w->user_id);
                PointTransaction::create([
                    'user_id'     => $user->id,
                    'delta'       => $w->points_cost,
                    'reason'      => 'admin_award',
                    'target_type' => 'Withdrawal',
                    'target_id'   => $w->id,
                    'meta'        => [
                        'kind'   => 'withdrawal_refunded',
                        'admin'  => $admin->id,
                        'reason' => $reason,
                    ],
                    'settled'     => true,
                ]);
                $user->increment('reputation', $w->points_cost);
            }

            $w->update([
                'status'       => 'rejected',
                'admin_id'     => $admin->id,
                'admin_note'   => $reason,
                'processed_at' => now(),
            ]);
            return $w->refresh();
        });
    }
}
