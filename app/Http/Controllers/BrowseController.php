<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Vote;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BrowseController extends Controller
{
    public function discover(Request $request)
    {
        $q        = trim((string) $request->query('q', ''));
        $category = $request->query('category');

        $base = Post::active()->with(['user:id,username,avatar_seed', 'zone:id,name']);

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('body', 'like', "%{$q}%")
                  ->orWhere('title', 'like', "%{$q}%");
            });
        }

        if ($category) {
            $base->where('category', $category);
        }

        $results = ($q !== '' || $category)
            ? $base->orderByDesc('hot_score')->paginate(20)->withQueryString()
            : null;

        // Top week
        $topWeek = ($q === '' && ! $category)
            ? Post::active()
                ->with(['user:id,username,avatar_seed', 'zone:id,name'])
                ->where('created_at', '>=', now()->subDays(7))
                ->orderByDesc('hot_score')
                ->limit(5)
                ->get()
            : collect();

        // Category counts
        $categoryCounts = Post::active()
            ->select('category', DB::raw('count(*) as c'))
            ->groupBy('category')
            ->pluck('c', 'category')
            ->all();

        // User's votes for any visible posts
        $userVotes = [];
        $visible = collect();
        if ($results) $visible = $visible->merge($results->pluck('id'));
        if ($topWeek->isNotEmpty()) $visible = $visible->merge($topWeek->pluck('id'));
        if (Auth::check() && $visible->isNotEmpty()) {
            $userVotes = Vote::where('user_id', Auth::id())
                ->whereIn('post_id', $visible->unique())
                ->pluck('value', 'post_id')
                ->all();
        }

        return view('discover', [
            'q'              => $q,
            'category'       => $category,
            'results'        => $results,
            'topWeek'        => $topWeek,
            'categoryCounts' => $categoryCounts,
            'categories'     => Post::CATEGORIES,
            'userVotes'      => $userVotes,
        ]);
    }

    public function zones(Request $request)
    {
        $zones = Zone::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->get();

        // Stats per zone
        $stats = Post::active()
            ->select(
                'zone_id',
                DB::raw('count(*) as posts_count'),
                DB::raw('max(created_at) as last_post_at')
            )
            ->groupBy('zone_id')
            ->get()
            ->keyBy('zone_id');

        $hottest = Post::active()
            ->with(['user:id,username,avatar_seed'])
            ->whereNotNull('zone_id')
            ->where('created_at', '>=', now()->subHours(24))
            ->orderByDesc('hot_score')
            ->limit(20)
            ->get()
            ->groupBy('zone_id');

        return view('zones', compact('zones', 'stats', 'hottest'));
    }
}
