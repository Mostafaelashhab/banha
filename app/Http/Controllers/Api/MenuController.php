<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Business;
use App\Models\MenuCategory;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $business = Business::query()
            ->where('slug', $slug)->orWhere('id', $slug)
            ->firstOrFail();

        $categories = MenuCategory::query()
            ->where('business_id', $business->id)
            ->with(['items' => function ($q) {
                $q->where('is_available', true)->orderBy('sort');
            }])
            ->orderBy('sort')
            ->get();

        return response()->json([
            'business' => ['id' => $business->id, 'name' => $business->name, 'currency' => $business->menu_currency ?? 'EGP'],
            'menu'     => MenuResource::collection($categories),
        ]);
    }
}
