<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Business;
use App\Models\BusinessReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->orWhere('id', $slug)->firstOrFail();

        $paginator = BusinessReview::query()
            ->where('business_id', $business->id)
            ->orderByDesc('reviewed_at')
            ->orderByDesc('created_at')
            ->paginate(perPage: 20, page: (int) $request->query('page', 1));

        return response()->json([
            'data' => ReviewResource::collection(collect($paginator->items())),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'rating_avg'   => $business->rating_avg ? (float) $business->rating_avg : null,
                'ratings_count' => (int) $business->ratings_count,
            ],
        ]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->orWhere('id', $slug)->firstOrFail();

        $data = $request->validate([
            'rating' => ['required', 'numeric', 'between:1,5'],
            'body'   => ['nullable', 'string', 'max:1000'],
        ]);

        $review = BusinessReview::create([
            'business_id' => $business->id,
            'user_id'     => $request->user()->id,
            'author_name' => $request->user()->username,
            'rating'      => $data['rating'],
            'body'        => $data['body'] ?? null,
            'source'      => 'mobile',
            'reviewed_at' => now(),
        ]);

        return response()->json(['review' => new ReviewResource($review)], 201);
    }

    public function photos(string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->orWhere('id', $slug)->firstOrFail();

        $photos = $business->photos()
            ->orderByDesc('created_at')
            ->get(['id', 'url', 'created_at']);

        return response()->json(['photos' => $photos]);
    }
}
