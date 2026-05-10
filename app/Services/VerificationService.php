<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;

class VerificationService
{
    public const SILVER_DAYS         = 30;
    public const SILVER_REPUTATION   = 100;
    public const SILVER_POSTS        = 10;
    public const SILVER_MAX_REPORTS  = 0;

    /**
     * Mark user as bronze immediately on signup.
     * NOTE: this is a placeholder until WhatsApp OTP is wired in,
     * at which point we'll only set bronze after OTP confirmation.
     */
    public static function markBronzeOnSignup(User $user): void
    {
        $user->refresh();
        $wasUnverified = $user->verification_tier === 'none' || $user->verification_tier === null;
        if (! in_array($user->verification_tier, ['silver', 'gold'], true)) {
            $user->verification_tier = 'bronze';
            $user->verified_at       = now();
            $user->save();

            // First-time activation grants the signup bonus (DB UNIQUE blocks re-awards).
            if ($wasUnverified) {
                \App\Services\PointsService::award($user->fresh(), 'signup');
            }
        }
    }

    /**
     * Re-evaluate silver eligibility. Called after posts, votes, etc.
     * Idempotent: only upgrades, never downgrades.
     */
    public static function recheckSilver(User $user): bool
    {
        if (in_array($user->verification_tier, ['silver', 'gold'], true)) {
            return false;
        }
        if ($user->verification_tier !== 'bronze') {
            return false;
        }

        $days       = $user->daysActive();
        $reputation = (int) $user->reputation;
        $posts      = Post::where('user_id', $user->id)->where('status', 'active')->count();
        $reports    = (int) $user->valid_reports_count;

        if ($days       < self::SILVER_DAYS)        return false;
        if ($reputation < self::SILVER_REPUTATION)  return false;
        if ($posts      < self::SILVER_POSTS)       return false;
        if ($reports    > self::SILVER_MAX_REPORTS) return false;

        $user->verification_tier = 'silver';
        $user->verified_at       = now();
        $user->save();

        BadgeService::award($user, 'silver-verified');

        return true;
    }

    public static function silverProgress(User $user): array
    {
        $days       = $user->daysActive();
        $reputation = (int) $user->reputation;
        $posts      = Post::where('user_id', $user->id)->where('status', 'active')->count();

        return [
            'days'         => ['current' => $days,       'target' => self::SILVER_DAYS],
            'reputation'   => ['current' => $reputation, 'target' => self::SILVER_REPUTATION],
            'posts'        => ['current' => $posts,      'target' => self::SILVER_POSTS],
            'clean_record' => ['current' => $user->valid_reports_count <= self::SILVER_MAX_REPORTS],
        ];
    }
}
