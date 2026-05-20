<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $paginator = Price::query()
            ->with(['product:id,name', 'zone:id,name', 'user:id,username'])
            ->latest()
            ->paginate(perPage: 30, page: (int) $request->query('page', 1));

        return response()->json([
            'data' => PriceResource::collection(collect($paginator->items())),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }
}
