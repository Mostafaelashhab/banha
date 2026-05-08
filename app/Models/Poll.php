<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Fillable(['post_id', 'question', 'options', 'closes_at'])]
class Poll extends Model
{
    protected function casts(): array
    {
        return [
            'options'   => 'array',
            'closes_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function isClosed(): bool
    {
        return $this->closes_at && $this->closes_at->isPast();
    }

    /** [option_index => votes_count] */
    public function counts(): array
    {
        return DB::table('poll_votes')
            ->where('poll_id', $this->id)
            ->select('option_index', DB::raw('count(*) as c'))
            ->groupBy('option_index')
            ->pluck('c', 'option_index')
            ->all();
    }

    public function totalVotes(): int
    {
        return DB::table('poll_votes')->where('poll_id', $this->id)->count();
    }

    public function userVote(): ?int
    {
        if (! Auth::check()) return null;
        $val = DB::table('poll_votes')
            ->where('poll_id', $this->id)
            ->where('user_id', Auth::id())
            ->value('option_index');
        return $val !== null ? (int) $val : null;
    }
}
