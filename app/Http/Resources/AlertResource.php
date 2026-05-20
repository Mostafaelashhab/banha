<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Alert $a */
        $a = $this->resource;

        return [
            'id'            => $a->id,
            'type'          => $a->type,
            'description'   => $a->description,
            'lat'           => $a->lat ? (float) $a->lat : null,
            'lng'           => $a->lng ? (float) $a->lng : null,
            'confirmations' => (int) $a->confirmations,
            'is_verified'   => (bool) $a->is_verified,
            'is_resolved'   => (bool) $a->is_resolved,
            'expires_at'    => $a->expires_at,
            'created_at'    => $a->created_at,
            'author'        => $this->whenLoaded('user', fn () => new UserResource($a->user)),
            'zone'          => $a->zone?->name,
        ];
    }
}
