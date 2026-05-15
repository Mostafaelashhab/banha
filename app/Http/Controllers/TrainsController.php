<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Public train-schedules pages. Data is scraped from egytrains.com into
 * storage/app/trains/banha-schedules.json by the `trains:scrape:banha`
 * artisan command.
 *
 * If the JSON file isn't there yet (fresh install), the page renders an
 * "instructions" empty state explaining how to populate it.
 */
class TrainsController extends Controller
{
    public function index(Request $request)
    {
        $payload = $this->loadPayload();

        $direction = $request->query('dir', 'outgoing');
        if (! in_array($direction, ['outgoing', 'incoming'], true)) {
            $direction = 'outgoing';
        }

        $destination = $request->query('to');
        $routes      = $payload[$direction] ?? [];

        // Validate destination — if invalid, fall back to first available.
        if ($destination && ! isset($routes[$destination])) {
            $destination = null;
        }
        if (! $destination) {
            $destination = array_key_first($routes);
        }

        $currentTrains = $destination && isset($routes[$destination])
            ? $routes[$destination]['trains'] ?? []
            : [];

        return view('trains.index', [
            'payload'     => $payload,
            'direction'   => $direction,
            'destination' => $destination,
            'routes'      => $routes,
            'trains'      => $currentTrains,
            'scrapedAt'   => $payload['scraped_at'] ?? null,
        ]);
    }

    /** Read the cached JSON; cache for 1h since the file changes only on scrape. */
    private function loadPayload(): array
    {
        return Cache::remember('banha-trains:payload:v1', 3600, function () {
            $path = 'trains/banha-schedules.json';
            if (! Storage::disk('local')->exists($path)) {
                return ['outgoing' => [], 'incoming' => [], 'scraped_at' => null];
            }
            $raw = Storage::disk('local')->get($path);
            $data = json_decode($raw, true);
            return is_array($data) ? $data : ['outgoing' => [], 'incoming' => [], 'scraped_at' => null];
        });
    }
}
