<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Event $e */
        $e = $this->resource;

        return [
            'id'              => $e->id,
            'kind'            => $e->kind,
            'title'           => $e->title,
            'description'     => $e->description,
            'location'        => $e->location,
            'lat'             => $e->lat ? (float) $e->lat : null,
            'lng'             => $e->lng ? (float) $e->lng : null,
            'starts_at'       => $e->starts_at,
            'ends_at'         => $e->ends_at,
            'cover_url'       => $e->cover_url,
            'contact_phone'   => $e->contact_phone,
            'attendees_count' => (int) $e->attendees_count,
            'status'          => $e->status,
        ];
    }
}
