<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Vote;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $tab    = $request->query('tab', 'hot');
        $zoneId = $request->query('zone');

        $query = Post::active()->with(['user:id,username,avatar_seed', 'zone:id,name']);

        if ($zoneId) {
            $query->where('zone_id', $zoneId);
        }

        $posts = match ($tab) {
            'new'    => $query->latest()->paginate(20)->withQueryString(),
            default  => $query->orderByDesc('hot_score')->latest()->paginate(20)->withQueryString(),
        };

        $userVotes = [];
        if (Auth::check() && $posts->isNotEmpty()) {
            $userVotes = Vote::where('user_id', Auth::id())
                ->whereIn('post_id', $posts->pluck('id'))
                ->pluck('value', 'post_id')
                ->all();
        }

        // Infinite-scroll partial (AJAX)
        if ($request->boolean('partial') || $request->ajax()) {
            return view('partials.feed-page', compact('posts', 'userVotes'));
        }

        return view('feed', [
            'posts'      => $posts,
            'tab'        => $tab,
            'zones'      => Zone::orderBy('sort')->get(),
            'activeZone' => $zoneId ? (int) $zoneId : null,
            'userVotes'  => $userVotes,
            'categories' => Post::CATEGORIES,
        ]);
    }
}
