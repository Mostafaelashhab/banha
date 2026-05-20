<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Booking $b */
        $b = $this->resource;

        return [
            'id'               => $b->id,
            'business_id'      => $b->business_id,
            'business'         => $this->whenLoaded('business', fn () => new BusinessResource($b->business)),
            'name'             => $b->name,
            'phone'            => $b->phone,
            'starts_at'        => $b->starts_at,
            'duration_minutes' => (int) $b->duration_minutes,
            'status'           => $b->status,
            'notes'            => $b->notes,
            'created_at'       => $b->created_at,
        ];
    }
}
