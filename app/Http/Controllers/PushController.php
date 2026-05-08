<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
    public function vapidKey()
    {
        return response()->json([
            'key' => config('services.vapid.public_key', ''),
        ]);
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint'         => ['required', 'url', 'max:500'],
            'keys'             => ['required', 'array'],
            'keys.p256dh'      => ['required', 'string', 'max:200'],
            'keys.auth'        => ['required', 'string', 'max:100'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'user_id'    => Auth::id(),
                'p256dh'     => $data['keys']['p256dh'],
                'auth'       => $data['keys']['auth'],
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'last_used_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $endpoint = $request->input('endpoint');
        if ($endpoint) {
            PushSubscription::where('endpoint', $endpoint)
                ->where('user_id', Auth::id())
                ->delete();
        }
        return response()->json(['ok' => true]);
    }
}
