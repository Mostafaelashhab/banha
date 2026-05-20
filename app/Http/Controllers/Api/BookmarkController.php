<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Bookmark;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $bookmarks = Bookmark::query()
            ->where('user_id', $userId)
            ->where('target_type', 'business')
            ->latest()
            ->limit(100)
            ->pluck('target_id');

        $businesses = Business::whereIn('id', $bookmarks)->get();

        return response()->json([
            'data' => BusinessResource::collection($businesses),
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_id' => ['required', 'integer', 'exists:businesses,id'],
        ]);

        $userId = $request->user()->id;
        $existing = Bookmark::where('user_id', $userId)
            ->where('target_type', 'business')
            ->where('target_id', $data['business_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['bookmarked' => false]);
        }

        Bookmark::create([
            'user_id'     => $userId,
            'target_type' => 'business',
            'target_id'   => $data['business_id'],
        ]);

        return response()->json(['bookmarked' => true], 201);
    }
}
