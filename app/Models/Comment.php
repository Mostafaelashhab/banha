<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Fillable(['post_id', 'user_id', 'parent_id', 'is_anonymous', 'anon_seed', 'body', 'status'])]
class Comment extends Model
{
    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->where('status', 'active')
            ->oldest();
    }

    public function isLikedByCurrentUser(): bool
    {
        if (! Auth::check()) return false;
        return DB::table('comment_likes')
            ->where('user_id', Auth::id())
            ->where('comment_id', $this->id)
            ->exists();
    }
}
