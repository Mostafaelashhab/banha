<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function mine(Request $request): JsonResponse
    {
        $paginator = Booking::query()
            ->where('user_id', $request->user()->id)
            ->with(['business'])
            ->orderByDesc('starts_at')
            ->paginate(perPage: 20, page: (int) $request->query('page', 1));

        return response()->json([
            'data' => BookingResource::collection(collect($paginator->items())),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_id'      => ['required', 'integer', 'exists:businesses,id'],
            'name'             => ['required', 'string', 'max:80'],
            'phone'            => ['required', 'string', 'max:20'],
            'starts_at'        => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $booking = Booking::create([
            'business_id'      => $data['business_id'],
            'user_id'          => $request->user()->id,
            'name'             => $data['name'],
            'phone'            => $data['phone'],
            'starts_at'        => $data['starts_at'],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'status'           => 'pending',
            'notes'            => $data['notes'] ?? null,
        ]);

        return response()->json([
            'booking' => new BookingResource($booking->load('business')),
        ], 201);
    }
}
