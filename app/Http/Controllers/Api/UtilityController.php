<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Area;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function openNow(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $query = Business::query()
            ->where('is_active', true)
            ->where(function ($w) {
                $w->where('is_24h', true)
                  ->orWhereNotNull('hours_schedule');
            });

        if (isset($data['lat'], $data['lng'])) {
            $lat = (float) $data['lat'];
            $lng = (float) $data['lng'];
            $haversine = '(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat))))';
            $query->selectRaw("businesses.*, $haversine AS distance_km", [$lat, $lng, $lat])
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->orderBy('distance_km');
        } else {
            $query->orderByDesc('rating_avg');
        }

        $paginator = $query->paginate(perPage: 20, page: (int) $request->query('page', 1));

        $items = collect($paginator->items())->map(function (Business $b) {
            if (isset($b->distance_km)) {
                $b->distance_m = (float) $b->distance_km * 1000;
            }
            return new BusinessResource($b);
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function offers(Request $request): JsonResponse
    {
        $paginator = Business::query()
            ->where('is_active', true)
            ->where('promoted_until', '>', now())
            ->orderByDesc('promoted_until')
            ->paginate(perPage: 20, page: (int) $request->query('page', 1));

        return response()->json([
            'data' => BusinessResource::collection(collect($paginator->items())),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function nearestArea(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];

        $haversine = '(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat))))';

        $area = Area::query()
            ->selectRaw("areas.*, $haversine AS distance_km", [$lat, $lng, $lat])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->orderBy('distance_km')
            ->first();

        if (! $area) {
            return response()->json(['message' => 'No area found'], 404);
        }

        return response()->json([
            'area' => [
                'id'   => $area->id,
                'name' => $area->name,
            ],
        ]);
    }
}
