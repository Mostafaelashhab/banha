<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['phone', 'username', 'password', 'zone_id', 'avatar_seed', 'avatar_url', 'persona', 'reputation', 'level', 'is_banned', 'is_verified', 'verification_tier', 'verified_at', 'is_admin', 'last_seen_at', 'prayer_notify', 'referred_by', 'referral_settled'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const TIERS = [
        'none'   => ['label' => 'غير مفعّل',  'short' => '',        'color' => '#84848E'],
        'bronze' => ['label' => 'مفعّل',      'short' => 'برونزي',  'color' => '#CD7F32'],
        'silver' => ['label' => 'موثّق فضي',  'short' => 'فضي',     'color' => '#9CA3AF'],
        'gold'   => ['label' => 'موثّق ذهبي', 'short' => 'ذهبي',    'color' => '#FFB85C'],
    ];

    protected function casts(): array
    {
        return [
            'password'      => 'hashed',
            'is_banned'     => 'boolean',
            'is_verified'   => 'boolean',
            'is_admin'      => 'boolean',
            'verified_at'   => 'datetime',
            'last_seen_at'  => 'datetime',
            'prayer_notify' => 'boolean',
        ];
    }

    /** Online if active in the last 5 minutes. */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
    }

    public function lastSeenLabel(): string
    {
        if (! $this->last_seen_at) return 'مش معروف';
        if ($this->isOnline()) return 'أونلاين الآن';
        return 'آخر ظهور '.$this->last_seen_at->diffForHumans();
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /** The user who invited me (nullable). */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /** Users I've invited (any tier). */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /** Short, stable referral code derived from the user id. URL-safe. */
    public function referralCode(): string
    {
        // base36 keeps it short + lowercase
        return strtolower(base_convert((string) $this->id, 10, 36));
    }

    /** Public invite URL. */
    public function inviteUrl(): string
    {
        return url('/signup?ref='.$this->referralCode());
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')
            ->orderByPivot('earned_at', 'desc');
    }

    public function tierMeta(): array
    {
        return self::TIERS[$this->verification_tier] ?? self::TIERS['none'];
    }

    public function daysActive(): int
    {
        return (int) $this->created_at->diffInDays(now());
    }

    /** Users I follow */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'followed_id')
            ->withTimestamps();
    }

    /** Users following me */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_follows', 'followed_id', 'follower_id')
            ->withTimestamps();
    }

    public function isFollowing(int $userId): bool
    {
        return \Illuminate\Support\Facades\DB::table('user_follows')
            ->where('follower_id', $this->id)
            ->where('followed_id', $userId)
            ->exists();
    }
}
