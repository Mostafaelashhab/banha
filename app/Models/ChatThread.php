<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['pair_key', 'last_message_user_id', 'last_message_preview', 'last_message_at'])]
class ChatThread extends Model
{
    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_thread_users', 'thread_id', 'user_id')
            ->withPivot('last_read_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'thread_id');
    }

    /** Build deterministic pair key for two users (so we can look up existing 1-on-1 thread). */
    public static function pairKey(int $a, int $b): string
    {
        return min($a, $b).'-'.max($a, $b);
    }

    /** Find or create a thread between two users. */
    public static function between(int $a, int $b): self
    {
        $key = self::pairKey($a, $b);
        $thread = self::firstOrCreate(['pair_key' => $key]);
        if ($thread->wasRecentlyCreated) {
            $thread->users()->syncWithoutDetaching([$a, $b]);
        }
        return $thread;
    }
}
