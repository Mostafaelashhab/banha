<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'zone_id', 'is_anonymous', 'anon_seed', 'category', 'title', 'body', 'image_url', 'is_sponsored', 'is_announcement', 'status'])]
class Post extends Model
{
    public const CATEGORIES = [
        'confession' => 'اعتراف',
        'question'   => 'سؤال',
        'complaint'  => 'شكوى',
        'review'     => 'تقييم',
        'news'       => 'خبر',
        'meme'       => 'ميمز',
        'help'       => 'مساعدة',
        'sale'       => 'بيع',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous'    => 'boolean',
            'is_sponsored'    => 'boolean',
            'is_announcement' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function score(): int
    {
        return (int) $this->upvotes - (int) $this->downvotes;
    }

    public function recomputeHotScore(): void
    {
        $score   = $this->score();
        $sign    = $score === 0 ? 0 : ($score > 0 ? 1 : -1);
        $order   = log10(max(abs($score), 1));
        $seconds = $this->created_at->timestamp - 1700000000;
        $this->hot_score = round($order + ($sign * $seconds / 45000), 7);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class, 'hashtag_post');
    }

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class);
    }
}
