<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Business;
use App\Models\Post;
use App\Models\PromoBanner;
use App\Models\Vote;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

        // Prices & stories are temporarily hidden — focus is on businesses
        $prices = collect();

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

        return view('feed', [
            'items'      => $sliced,
            'paginator'  => $paginator,
            'tab'        => $tab,
            'zones'      => Zone::orderBy('sort')->get(),
            'activeZone' => $zoneId ? (int) $zoneId : null,
            'userVotes'  => $userVotes,
            'categories' => Post::CATEGORIES,
            ...$this->homepageData($zoneId ? (int) $zoneId : null),
        ]);
    }

    /**
     * Data for the homepage sections rendered at the top of feed.blade.php.
     *
     * Caches only the *expensive* parts: which business IDs go in each rail, plus
     * the popular-searches list. The cheap part — re-loading those rows with their
     * relations — runs fresh per request. This avoids serializing Eloquent models
     * to cache (which can blow up under the database/file drivers when unserialize
     * encounters a partial class shape) while still saving the costly bits: the
     * in-PHP `isOpenNow()` scan over 60 rows, the JOINs, and the hashtag query.
     */
    private function homepageData(?int $zoneId = null): array
    {
        $cacheKey = 'homepage-ids:v4:'.($zoneId ?: 'all');

        $resolved = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $promoted = Business::query()
                ->where('is_active', true)
                ->where('promoted_until', '>', now())
                ->orderByDesc('promoted_until')
                ->limit(6)
                ->pluck('id')
                ->all();

            $featured = Business::query()
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('is_verified', true)->orWhere('rating_avg', '>=', 4);
                })
                ->orderByDesc('is_verified')
                ->orderByDesc('rating_avg')
                ->orderByDesc('views_count')
                ->limit(12)
                ->pluck('id')
                ->all();

            // The expensive part: 60-row PHP scan calling isOpenNow().
            $openNow = Business::query()
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('is_24h', true)->orWhereNotNull('hours_schedule');
                })
                ->orderByDesc('is_verified')
                ->orderByDesc('rating_avg')
                ->orderByDesc('views_count')
                ->limit(60)
                ->get(['id', 'is_24h', 'hours_schedule'])
                ->filter(fn ($b) => $b->isOpenNow() === true)
                ->take(12)
                ->pluck('id')
                ->all();

            $newListings = \App\Models\Listing::query()
                ->where('status', 'active')
                ->latest()
                ->limit(8)
                ->pluck('id')
                ->all();

            $popularSearches = [];
            try {
                $popularSearches = \App\Models\Hashtag::query()
                    ->orderByDesc('uses_count')
                    ->limit(8)
                    ->pluck('tag')
                    ->all();
            } catch (\Throwable $e) {
                $popularSearches = [];
            }
            if (empty($popularSearches)) {
                $popularSearches = ['كشري', 'دكتور أسنان', 'صيدلية', 'كافيه', 'صنايعي', 'شقة للإيجار', 'كورس', 'عرض'];
            }

            return compact('promoted', 'featured', 'openNow', 'newListings', 'popularSearches');
        });

        // Fresh load — the IN-clause on indexed PK is cheap.
        $bizCols  = ['id', 'name', 'category', 'sub_type', 'custom_sub_type',
                     'zone_id', 'owner_user_id', 'phone', 'whatsapp', 'hotline',
                     'address', 'lat', 'lng', 'is_24h', 'hours_schedule',
                     'is_verified', 'promoted_until', 'photo_url', 'has_menu',
                     'rating_avg', 'ratings_count', 'views_count'];

        $rehydrate = function (array $ids) use ($bizCols) {
            if (empty($ids)) return collect();
            $models = Business::query()
                ->select($bizCols)
                ->whereIn('id', $ids)
                ->with(['zone:id,name', 'photos:id,business_id,url'])
                ->get()
                ->keyBy('id');
            // Preserve the cached order.
            return collect($ids)->map(fn ($id) => $models[$id] ?? null)->filter()->values();
        };

        $promoted = $rehydrate($resolved['promoted']);
        $featured = $rehydrate($resolved['featured']);
        $openNow  = $rehydrate($resolved['openNow']);

        $newListings = empty($resolved['newListings'])
            ? collect()
            : \App\Models\Listing::query()
                ->whereIn('id', $resolved['newListings'])
                ->with(['zone:id,name'])
                ->get()
                ->keyBy('id')
                ->only($resolved['newListings'])
                ->values();

        // PromoBanners are tiny — load fresh, no caching needed.
        $promoBanners = PromoBanner::live()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $homeCatKeys = ['food', 'medical', 'shops', 'services', 'transport', 'education', 'tourist', 'craftsmen'];
        $homeCats    = collect($homeCatKeys)
            ->map(fn ($k) => ['key' => $k] + (Business::CATEGORIES[$k] ?? []));

        return compact(
            'promoBanners', 'promoted', 'featured', 'openNow',
            'homeCats', 'newListings',
        ) + ['popularSearches' => $resolved['popularSearches']];
    }
}
