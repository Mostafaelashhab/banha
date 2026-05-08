<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['tag', 'uses_count'])]
class Hashtag extends Model
{
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'hashtag_post');
    }

    /**
     * Pull #tags out of free text. Supports Arabic + English.
     * Returns lowercased, deduped list (max 10 per post).
     */
    public static function extract(string $text): array
    {
        preg_match_all('/(?:^|\s)#([\p{L}\p{N}_]{2,40})/u', $text, $m);
        $tags = array_map(fn ($t) => mb_strtolower($t), $m[1] ?? []);
        return array_slice(array_values(array_unique($tags)), 0, 10);
    }

    /** Sync a post's hashtags. Creates new tags + updates uses_count. */
    public static function syncForPost(Post $post, string $text): void
    {
        $tags = self::extract($text);
        if (empty($tags)) {
            $post->hashtags()->detach();
            return;
        }

        $ids = [];
        foreach ($tags as $tag) {
            $h = self::firstOrCreate(['tag' => $tag], ['uses_count' => 0]);
            $ids[] = $h->id;
        }
        $post->hashtags()->sync($ids);

        // Recalculate uses_count for affected tags
        self::whereIn('id', $ids)->each(function ($h) {
            $h->update(['uses_count' => $h->posts()->count()]);
        });
    }
}
