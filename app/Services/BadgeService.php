<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Price;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BadgeService
{
    /**
     * Award a badge to a user (idempotent).
     */
    public static function award(User $user, string $slug): bool
    {
        $badge = Badge::where('slug', $slug)->first();
        if (! $badge) return false;

        return DB::table('user_badges')->insertOrIgnore([
            'user_id'   => $user->id,
            'badge_id'  => $badge->id,
            'earned_at' => now(),
        ]) > 0;
    }

    /**
     * Run all checks relevant to "user posted something".
     */
    public static function onPost(User $user): void
    {
        $count = Post::where('user_id', $user->id)->count();

        if ($count >= 1)   self::award($user, 'first-post');
        if ($count >= 10)  self::award($user, 'ten-posts');
        if ($count >= 50)  self::award($user, 'fifty-posts');

        $anonCount = Post::where('user_id', $user->id)->where('is_anonymous', true)->count();
        if ($anonCount >= 10) self::award($user, 'whisper-friend');

        $hot = Post::where('user_id', $user->id)
            ->whereRaw('(upvotes - downvotes) >= 50')
            ->count();
        if ($hot >= 1)  self::award($user, 'first-hot');
        if ($hot >= 5)  self::award($user, 'trend-king');

        VerificationService::recheckSilver($user);
    }

    public static function onComment(User $user): void
    {
        $count = Comment::where('user_id', $user->id)->count();
        if ($count >= 1)   self::award($user, 'first-comment');
        if ($count >= 100) self::award($user, 'commenter-100');
    }

    public static function onPriceSubmit(User $user): void
    {
        $count = Price::where('user_id', $user->id)->count();
        if ($count >= 1)  self::award($user, 'first-price');
        if ($count >= 25) self::award($user, 'price-radar');
    }

    public static function onAlertSubmit(User $user): void
    {
        self::award($user, 'first-alert');
    }

    public static function onAlertVerified(User $owner): void
    {
        self::award($owner, 'verified-alert');
    }

    public static function onSignup(User $user): void
    {
        if ($user->id <= 1000) self::award($user, 'early-bird');
    }

    public static function onReputationChange(User $user): void
    {
        if ($user->reputation >= 100) self::award($user, 'rep-100');
        if ($user->reputation >= 500) self::award($user, 'rep-500');

        VerificationService::recheckSilver($user);
    }
}
