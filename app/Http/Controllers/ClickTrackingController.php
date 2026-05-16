<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessClickEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Beacons for the "popular times" histogram. The frontend posts here once
 * per click on phone / whatsapp / directions buttons. Returns 204 always
 * (don't block UX on tracking failure).
 */
class ClickTrackingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_id' => ['required', 'integer'],
            'kind'        => ['required', 'string', 'in:phone,whatsapp,directions,order,menu'],
        ]);

        // Verify business exists — we don't care about the row, just the FK guard.
        if (! Business::query()->whereKey($data['business_id'])->exists()) {
            return response()->json(['ok' => true]);
        }

        $now = now('Africa/Cairo');

        try {
            BusinessClickEvent::create([
                'business_id' => $data['business_id'],
                'kind'        => $data['kind'],
                'hour'        => (int) $now->format('G'),
                'dow'         => (int) $now->format('w'),
            ]);
            // Bust the cached histogram so big spikes show up sooner.
            \Illuminate\Support\Facades\Cache::forget("popular-times:{$data['business_id']}:v1");
        } catch (\Throwable $e) {
            // Best-effort — never let tracking surface an error to the user.
        }

        return response()->json(['ok' => true]);
    }
}
