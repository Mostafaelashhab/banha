<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;

class HashtagController extends Controller
{
    public function show(string $tag)
    {
        $tag = mb_strtolower(trim($tag));

        $hashtag = Hashtag::where('tag', $tag)->first();
        if (! $hashtag) abort(404);

        $posts = $hashtag->posts()
            ->where('posts.status', 'active')
            ->with(['user:id,username,avatar_seed,avatar_url,verification_tier', 'zone:id,name'])
            ->latest('posts.created_at')
            ->paginate(20);

        $userVotes = [];
        if (Auth::check() && $posts->isNotEmpty()) {
            $userVotes = Vote::where('user_id', Auth::id())
                ->whereIn('post_id', $posts->pluck('id'))
                ->pluck('value', 'post_id')
                ->all();
        }

        return view('hashtags.show', compact('hashtag', 'posts', 'userVotes'));
    }

    public function trending()
    {
        $tags = Hashtag::orderByDesc('uses_count')
            ->where('uses_count', '>', 0)
            ->limit(50)
            ->get();
        return view('hashtags.trending', compact('tags'));
    }
}
