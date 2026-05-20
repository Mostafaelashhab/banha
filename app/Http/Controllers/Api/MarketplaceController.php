<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kind'     => ['nullable', 'string', 'max:40'],
            'category' => ['nullable', 'string', 'max:40'],
            'q'        => ['nullable', 'string', 'max:120'],
            'page'     => ['nullable', 'integer', 'min:1'],
        ]);

        $paginator = Listing::query()
            ->where('status', 'active')
            ->when($data['kind'] ?? null, fn ($q, $v) => $q->where('kind', $v))
            ->when($data['category'] ?? null, fn ($q, $v) => $q->where('category', $v))
            ->when($data['q'] ?? null, fn ($q, $v) => $q->where(function ($w) use ($v) {
                $w->where('title', 'like', "%$v%")->orWhere('description', 'like', "%$v%");
            }))
            ->orderByRaw('CASE WHEN featured_until > NOW() THEN 0 ELSE 1 END')
            ->orderByDesc('created_at')
            ->paginate(perPage: 20, page: (int) ($data['page'] ?? 1));

        return response()->json([
            'data' => ListingResource::collection(collect($paginator->items())),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $listing = Listing::query()->where('status', 'active')->findOrFail($id);
        $listing->increment('views');

        return response()->json(['listing' => new ListingResource($listing)]);
    }
}
