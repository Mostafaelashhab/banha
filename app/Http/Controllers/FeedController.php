<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Business;
use App\Models\Post;
use App\Models\Price;
use App\Models\Vote;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    private const PER_PAGE          = 20;
    private const BUSINESS_AD_EVERY = 3;   // sponsor card after every N content items
    private const MAX_STREAM        = 200; // hard cap on stream length

    public function index(Request $request)
    {
        $tab    = $request->query('tab', 'new');
        $zoneId = $request->query('zone');
        $page   = max(1, (int) $request->query('page', 1));

        // ─── Pull each content type ────────────────────────────
        $posts = Post::active()
            ->with(['user:id,username,avatar_seed,avatar_url,verification_tier,is_admin,last_seen_at', 'zone:id,name'])
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->orderByDesc($tab === 'new' ? 'created_at' : 'hot_score')
            ->orderByDesc('created_at')
            ->limit(60)
            ->get();

        $alerts = Alert::active()
            ->with(['user:id,username,avatar_seed,avatar_url,verification_tier,is_admin,last_seen_at', 'zone:id,name'])
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->latest()
            ->limit(20)
            ->get();

        $prices = Price::query()
            ->with(['product:id,name,emoji,unit', 'zone:id,name', 'user:id,username,avatar_url'])
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->latest()
            ->limit(20)
            ->get();

        // Marketplace listings — featured first, then recent
        $listings = \App\Models\Listing::query()
            ->where('status', 'active')
            ->with(['user:id,username,avatar_seed,avatar_url', 'zone:id,name'])
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->orderByRaw('CASE WHEN featured_until > NOW() THEN 0 ELSE 1 END')
            ->latest()
            ->limit(20)
            ->get();

        $businesses = Business::query()
            ->where('is_active', true)
            ->with('zone:id,name')
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->orderByDesc('rating_avg')
            ->orderByDesc('ratings_count')
            ->limit(40)
            ->get()
            ->shuffle();

        // ─── Build chronological content stream ───────────────
        $content = collect()
            ->concat($posts->map(fn ($p)    => ['kind' => 'post',    'at' => $p->created_at, 'data' => $p]))
            ->concat($alerts->map(fn ($a)   => ['kind' => 'alert',   'at' => $a->created_at, 'data' => $a]))
            ->concat($prices->map(fn ($p)   => ['kind' => 'price',   'at' => $p->created_at, 'data' => $p]))
            ->concat($listings->map(fn ($l) => ['kind' => 'listing', 'at' => $l->created_at, 'data' => $l]));

        if ($tab === 'hot') {
            $content = $content->sortBy(function ($i) {
                $age   = now()->diffInHours($i['at']);
                $boost = match ($i['kind']) {
                    'alert' => -50,
                    'price' => 10,
                    default => 0,
                };
                return $age + $boost;
            })->values();
        } else {
            $content = $content->sortByDesc(fn ($i) => $i['at']->timestamp)->values();
        }

        // ─── Sprinkle business "ads" — adapts to content density ──
        $stream   = collect();
        $bizQueue = $businesses->values();
        $bizIdx   = 0;

        // If content is sparse, show an ad after every content item.
        // If content is dense, show an ad every Nth.
        $contentCount = $content->count();
        $adGap = $contentCount < 5 ? 1 : self::BUSINESS_AD_EVERY;

        foreach ($content as $i => $item) {
            $stream->push($item);
            if ((($i + 1) % $adGap) === 0 && $bizIdx < $bizQueue->count()) {
                $stream->push(['kind' => 'business', 'data' => $bizQueue[$bizIdx++], 'is_ad' => true]);
            }
        }

        // Pad the page with business ads so it never looks empty
        while ($stream->count() < self::PER_PAGE * 2 && $bizIdx < $bizQueue->count()) {
            $stream->push(['kind' => 'business', 'data' => $bizQueue[$bizIdx++], 'is_ad' => true]);
        }

        $stream = $stream->take(self::MAX_STREAM)->values();

        // ─── Paginate manually ────────────────────────────────
        $sliced = $stream->slice(($page - 1) * self::PER_PAGE, self::PER_PAGE)->values();

        $paginator = new LengthAwarePaginator(
            $sliced,
            $stream->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // ─── User votes for visible posts ─────────────────────
        $visiblePostIds = $sliced->where('kind', 'post')->pluck('data.id')->filter()->values();
        $userVotes = [];
        if (Auth::check() && $visiblePostIds->isNotEmpty()) {
            $userVotes = Vote::where('user_id', Auth::id())
                ->whereIn('post_id', $visiblePostIds)
                ->pluck('value', 'post_id')
                ->all();
        }

        if ($request->boolean('partial') || $request->ajax()) {
            return view('partials.feed-page', [
                'items'     => $sliced,
                'paginator' => $paginator,
                'userVotes' => $userVotes,
            ]);
        }

        // Active stories for top strip — group by user, latest first
        $stories = \App\Models\Story::query()
            ->where('expires_at', '>', now())
            ->with('user:id,username,avatar_seed,avatar_url')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('user_id');

        return view('feed', [
            'items'      => $sliced,
            'paginator'  => $paginator,
            'tab'        => $tab,
            'zones'      => Zone::orderBy('sort')->get(),
            'activeZone' => $zoneId ? (int) $zoneId : null,
            'userVotes'  => $userVotes,
            'categories' => Post::CATEGORIES,
            'stories'    => $stories,
        ]);
    }
}
