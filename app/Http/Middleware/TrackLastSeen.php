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
            }
        }
        return $next($request);
    }
}
