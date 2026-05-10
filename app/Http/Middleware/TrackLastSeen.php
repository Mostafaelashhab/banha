<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bumps the authenticated user's `last_seen_at` timestamp at most once per minute.
 * Cheap (cache-throttled) and runs on every request.
 */
class TrackLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $key = 'last-seen:'.$userId;
            if (! Cache::has($key)) {
                // Direct UPDATE so we don't trigger model events / updated_at change
                DB::table('users')->where('id', $userId)->update(['last_seen_at' => now()]);
                Cache::put($key, true, 60); // throttle: 60 sec

                // ── Daily login points award ─────────────────────────────
                // Once-per-calendar-date guard via a Cache key, then a real DB-level
                // UNIQUE check inside PointsService blocks any race past the cache.
                $todayKey = 'daily-login-pts:'.$userId.':'.now()->format('Ymd');
                if (! Cache::has($todayKey)) {
                    $user = Auth::user();
                    if ($user) {
                        \App\Services\PointsService::award($user, 'daily_login');
                    }
                    // Cache until end of day + a buffer
                    Cache::put($todayKey, true, now()->endOfDay()->addHour());
                }
            }
        }
        return $next($request);
    }
}
