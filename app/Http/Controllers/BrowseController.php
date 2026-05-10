<?php

namespace App\Http\Controllers;

use App\Models\Business;
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

        $base = Post::active()->with(['user:id,username,avatar_seed,avatar_url,verification_tier,is_admin', 'zone:id,name']);

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
                ->with(['user:id,username,avatar_seed,avatar_url,verification_tier,is_admin', 'zone:id,name'])
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

    public function users(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // Base: most active first (posts_count + reputation)
        $query = \App\Models\User::query()
            ->where('is_banned', false)
            ->withCount(['posts as posts_count' => fn ($q) => $q->where('status', 'active')])
            ->withCount(['followers as followers_count']);

        if ($q !== '') {
            $query->where('username', 'like', "%{$q}%");
        }

        $users = $query
            ->orderByDesc('verification_tier')
            ->orderByDesc('reputation')
            ->orderByDesc('posts_count')
            ->paginate(30)
            ->withQueryString();

        // Who I follow (so we can show "متابع/+ تابع" on each card)
        $followingIds = Auth::check()
            ? DB::table('user_follows')->where('follower_id', Auth::id())->pluck('followed_id')->all()
            : [];

        if ($request->boolean('partial') || $request->ajax()) {
            return view('partials.users-page', compact('users', 'followingIds'));
        }

        return view('users', compact('users', 'q', 'followingIds'));
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
            ->with(['user:id,username,avatar_seed,avatar_url,verification_tier,is_admin'])
            ->whereNotNull('zone_id')
            ->where('created_at', '>=', now()->subHours(24))
            ->orderByDesc('hot_score')
            ->limit(20)
            ->get()
            ->groupBy('zone_id');

        return view('zones', compact('zones', 'stats', 'hottest'));
    }

    /**
     * Per-zone landing page — high-value SEO target ("مطاعم بنها قسم أول"…).
     * Aggregates the most relevant directory listings + local activity for the zone.
     */
    public function zoneShow(string $slug)
    {
        $zone = Zone::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $businesses = Business::where('is_active', true)
            ->where('zone_id', $zone->id)
            ->orderByRaw('CASE WHEN promoted_until > NOW() THEN 0 ELSE 1 END')
            ->orderByDesc('is_verified')
            ->orderByDesc('rating_avg')
            ->limit(60)
            ->get();

        $byCategory = $businesses->groupBy('category');

        $categoryLabels = collect(Business::CATEGORIES)
            ->map(fn ($c, $key) => ['key' => $key, 'label' => $c['label'], 'icon' => $c['icon'] ?? 'bag'])
            ->values();

        $totalCount = Business::where('is_active', true)->where('zone_id', $zone->id)->count();

        return view('directory.zone-show', compact('zone', 'byCategory', 'categoryLabels', 'totalCount'));
    }
}
