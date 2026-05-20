<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Bookmark;
use App\Models\Business;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('zone');

        $stats = [
            'saves'    => (int) Bookmark::where('user_id', $user->id)->count(),
            'orders'   => (int) Order::where('user_id', $user->id)->count(),
            'listings' => (int) Business::where('owner_user_id', $user->id)->count(),
        ];

        return response()->json([
            'user'  => new UserResource($user),
            'stats' => $stats,
        ]);
    }

    public function show(string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        return response()->json([
            'user' => new UserResource($user->load('zone')),
        ]);
    }
}
