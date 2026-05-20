<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $page     = max(1, (int) $request->query('page', 1));
        $perPage  = 20;

        $query = Business::query()
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByDesc('promoted_until')
            ->orderByDesc('rating_avg')
            ->orderByDesc('ratings_count');

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $data = collect($paginator->items())->map(fn (Business $b) => [
            'type'     => 'business',
            'business' => new BusinessResource($b),
        ]);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function following(Request $request): JsonResponse
    {
        // Personalized feed — for now mirror the public feed; the web app's
        // FollowController has the real implementation. Mobile can switch
        // when FollowController gets a JSON path.
        return $this->index($request);
    }
}
