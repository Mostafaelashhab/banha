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

#[Fillable(['phone', 'username', 'password', 'zone_id', 'avatar_seed', 'avatar_url', 'persona', 'reputation', 'level', 'is_banned', 'is_verified', 'verification_tier', 'verified_at', 'is_admin'])]
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
            'password'    => 'hashed',
            'is_banned'   => 'boolean',
            'is_verified' => 'boolean',
            'is_admin'    => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
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
}
