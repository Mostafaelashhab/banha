<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function mine(Request $request): JsonResponse
    {
        $paginator = Order::query()
            ->where('user_id', $request->user()->id)
            ->with(['business', 'items'])
            ->latest()
            ->paginate(perPage: 20, page: (int) $request->query('page', 1));

        return response()->json([
            'data' => OrderResource::collection(collect($paginator->items())),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->with(['business', 'items'])
            ->findOrFail($id);

        return response()->json(['order' => new OrderResource($order)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_id'      => ['required', 'integer', 'exists:businesses,id'],
            'customer_name'    => ['required', 'string', 'max:80'],
            'customer_phone'   => ['required', 'string', 'max:20'],
            'customer_address' => ['required', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'area_id'          => ['nullable', 'integer', 'exists:areas,id'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.qty'      => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $items = MenuItem::whereIn('id', collect($data['items'])->pluck('menu_item_id'))
            ->where('business_id', $data['business_id'])
            ->where('is_available', true)
            ->get()
            ->keyBy('id');

        if ($items->count() !== count($data['items'])) {
            return response()->json(['message' => 'فيه أصناف مش متوفرة'], 422);
        }

        $subtotal = collect($data['items'])->sum(function ($row) use ($items) {
            $item = $items[$row['menu_item_id']];
            return $item->price * $row['qty'];
        });

        $order = DB::transaction(function () use ($data, $items, $subtotal, $request) {
            $order = Order::create([
                'business_id'      => $data['business_id'],
                'user_id'          => $request->user()->id,
                'customer_name'    => $data['customer_name'],
                'customer_phone'   => $data['customer_phone'],
                'customer_address' => $data['customer_address'],
                'area_id'          => $data['area_id'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'subtotal'         => $subtotal,
                'currency'         => 'EGP',
                'status'           => 'pending',
            ]);

            foreach ($data['items'] as $row) {
                $item = $items[$row['menu_item_id']];
                OrderItem::create([
                    'order_id'     => $order->id,
                    'menu_item_id' => $item->id,
                    'name'         => $item->name,
                    'qty'          => $row['qty'],
                    'unit_price'   => $item->price,
                ]);
            }

            return $order;
        });

        return response()->json([
            'order' => new OrderResource($order->load(['business', 'items'])),
        ], 201);
    }
}
