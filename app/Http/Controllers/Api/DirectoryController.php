<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirectoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category'  => ['nullable', 'string', 'max:40'],
            'q'         => ['nullable', 'string', 'max:120'],
            'lat'       => ['nullable', 'numeric', 'between:-90,90'],
            'lng'       => ['nullable', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'between:0.1,50'],
            'open_now'  => ['nullable', 'boolean'],
            'page'      => ['nullable', 'integer', 'min:1'],
        ]);

        $category = $data['category'] ?? null;
        $q        = $data['q'] ?? null;
        $lat      = isset($data['lat']) ? (float) $data['lat'] : null;
        $lng      = isset($data['lng']) ? (float) $data['lng'] : null;
        $radius   = isset($data['radius_km']) ? (float) $data['radius_km'] : 5.0;
        $page     = max(1, (int) ($data['page'] ?? 1));

        $query = Business::query()->where('is_active', true);

        if ($category) {
            $query->where('category', $category);
        }
        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                  ->orWhere('description', 'like', "%$q%");
            });
        }

        if ($lat !== null && $lng !== null) {
            $haversine = '(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat))))';
            $query->select('*')
                ->selectRaw("$haversine AS distance_km", [$lat, $lng, $lat])
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->having('distance_km', '<=', $radius)
                ->orderBy('distance_km');
        } else {
            $query->orderByDesc('rating_avg')->orderByDesc('ratings_count');
        }

        $paginator = $query->paginate(perPage: 20, page: $page);

        $businesses = collect($paginator->items())->map(function (Business $b) {
            if (isset($b->distance_km)) {
                $b->distance_m = (float) $b->distance_km * 1000;
            }
            return new BusinessResource($b);
        });

        return response()->json([
            'data' => $businesses,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        return response()->json([
            'business' => new BusinessResource($business),
        ]);
    }

    public function categories(): JsonResponse
    {
        $counts = Business::where('is_active', true)
            ->select('category', DB::raw('COUNT(*) as c'))
            ->groupBy('category')
            ->pluck('c', 'category');

        $list = collect(Business::CATEGORIES)->map(function ($meta, $slug) use ($counts) {
            return [
                'slug'  => $slug,
                'label' => $meta['label'] ?? $slug,
                'icon'  => $meta['icon'] ?? null,
                'count' => (int) ($counts[$slug] ?? 0),
            ];
        })->values();

        return response()->json(['categories' => $list]);
    }

    public function trackClick(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_id' => ['required', 'integer', 'exists:businesses,id'],
        ]);

        try {
            \App\Models\BusinessClickEvent::create([
                'business_id' => $data['business_id'],
                'user_id'     => $request->user()?->id,
                'ip'          => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Silently swallow — click tracking is best-effort
        }

        return response()->json(['ok' => true]);
    }
}
