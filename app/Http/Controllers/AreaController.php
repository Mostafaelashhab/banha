<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaController extends Controller
{
    /**
     * GET /areas/nearest?lat=&lng=
     *
     * Returns the nearest known area to the given coordinates. Used by the
     * cart UI to pre-select a delivery area from the device's geolocation.
     */
    public function nearest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        // Only match Banha areas — non-Banha coverage is paused.
        $result = Area::nearest((float) $data['lat'], (float) $data['lng'], 'بنها');
        $area     = $result['area'];
        $distance = $result['distance_km'];

        if (! $area) {
            return response()->json([
                'ok'          => false,
                'reason'      => $distance === null ? 'no_coverage' : 'out_of_range',
                'distance_km' => $distance,
            ]);
        }

        return response()->json([
            'ok'          => true,
            'distance_km' => $distance,
            'area'        => [
                'id'     => $area->id,
                'name'   => $area->name,
                'parent' => $area->parent,
            ],
        ]);
    }

    /**
     * POST /me/area  — remember a logged-in user's preferred area so it
     * stays selected next time.
     */
    public function setDefault(Request $request): JsonResponse
    {
        $data = $request->validate([
            'area_id' => ['nullable', 'integer', 'exists:areas,id'],
        ]);

        $user = Auth::user();
        if ($user) {
            $user->update(['default_area_id' => $data['area_id'] ?? null]);
        }
        return response()->json(['ok' => true]);
    }
}
