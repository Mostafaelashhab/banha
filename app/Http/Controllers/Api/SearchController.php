<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q'        => ['required', 'string', 'min:1', 'max:120'],
            'category' => ['nullable', 'string', 'max:40'],
        ]);

        $q        = $data['q'];
        $category = $data['category'] ?? null;

        $businesses = Business::query()
            ->where('is_active', true)
            ->when($category, fn ($qb) => $qb->where('category', $category))
            ->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                  ->orWhere('description', 'like', "%$q%")
                  ->orWhere('address', 'like', "%$q%");
            })
            ->orderByDesc('rating_avg')
            ->limit(30)
            ->get();

        return response()->json([
            'businesses' => BusinessResource::collection($businesses),
            'posts'      => [],
        ]);
    }
}
