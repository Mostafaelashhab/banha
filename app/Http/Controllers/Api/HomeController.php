<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use App\Models\PromoBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Categories with counts — top 10 by activity for the home circle row
        $counts = Business::where('is_active', true)
            ->select('category', DB::raw('COUNT(*) as c'))
            ->groupBy('category')
            ->pluck('c', 'category');

        $categories = collect(Business::CATEGORIES)
            ->map(fn ($meta, $slug) => [
                'slug'  => $slug,
                'label' => $meta['label'] ?? $slug,
                'icon'  => $meta['icon'] ?? null,
                'color' => $meta['color'] ?? null,
                'count' => (int) ($counts[$slug] ?? 0),
            ])
            ->sortByDesc('count')
            ->values();

        // Sponsored / promoted — top of the home feed
        $promoted = Business::query()
            ->where('is_active', true)
            ->where('promoted_until', '>', now())
            ->orderByDesc('promoted_until')
            ->limit(12)
            ->get();

        // Top-rated — "الأكتر تقييم في بنها"
        $topRated = Business::query()
            ->where('is_active', true)
            ->whereNotNull('rating_avg')
            ->orderByDesc('rating_avg')
            ->orderByDesc('ratings_count')
            ->limit(12)
            ->get();

        // Promo banners (optional)
        $banners = collect();
        try {
            $banners = PromoBanner::query()
                ->where('is_active', true)
                ->orderBy('sort')
                ->limit(6)
                ->get()
                ->map(fn ($b) => [
                    'id'       => $b->id,
                    'title'    => $b->title,
                    'desc'     => $b->description,
                    'cta'      => $b->cta_text,
                    'image'    => $b->image_url,
                    'href'     => method_exists($b, 'destinationUrl') ? $b->destinationUrl() : null,
                    'bg_from'  => $b->bg_from,
                    'bg_to'    => $b->bg_to,
                ]);
        } catch (\Throwable $e) {
            // PromoBanner model/table may not exist in some environments
        }

        // Popular searches (static seed for now — derive from search logs later)
        $popularSearches = ['كشري', 'صيدلية مفتوحة', 'قهوة', 'دكتور أسنان', 'فرن عيش', 'كوافير', 'جيم', 'دليفري'];

        // Utility shortcuts — same set the PWA home shows
        $shortcuts = [
            ['key' => 'craftsmen',  'label' => 'صنايعية',      'icon' => 'shield',   'href' => '/craftsmen'],
            ['key' => 'offers',     'label' => 'عروض',         'icon' => 'bolt',     'href' => '/offers'],
            ['key' => 'bookings',   'label' => 'احجز موعد',    'icon' => 'check',    'href' => '/bookings'],
            ['key' => 'open-now',   'label' => 'مفتوح دلوقتي', 'icon' => 'clock',    'href' => '/open-now'],
            ['key' => 'jobs',       'label' => 'وظايف',        'icon' => 'cart',     'href' => '/jobs'],
            ['key' => 'trains',     'label' => 'القطارات',     'icon' => 'compass',  'href' => '/trains'],
            ['key' => 'lost-found', 'label' => 'مفقودات',      'icon' => 'search',   'href' => '/lost-found'],
            ['key' => 'emergency',  'label' => 'طوارئ',        'icon' => 'shield',   'href' => '/emergency'],
            ['key' => 'university', 'label' => 'الجامعة',      'icon' => 'star',     'href' => '/university'],
            ['key' => 'marketplace','label' => 'سوق',          'icon' => 'cart',     'href' => '/marketplace'],
        ];

        $unread = 0;
        if ($request->user()) {
            $unread = (int) \App\Models\Notification::where('user_id', $request->user()->id)
                ->whereNull('read_at')->count();
        }

        return response()->json([
            'shortcuts'        => $shortcuts,
            'popular_searches' => $popularSearches,
            'categories'       => $categories,
            'promoted'         => BusinessResource::collection($promoted),
            'top_rated'        => BusinessResource::collection($topRated),
            'banners'          => $banners,
            'unread_count'     => $unread,
        ]);
    }
}
