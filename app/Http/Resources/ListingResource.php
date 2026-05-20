<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Listing $l */
        $l = $this->resource;

        return [
            'id'                => $l->id,
            'kind'              => $l->kind,
            'category'          => $l->category,
            'title'             => $l->title,
            'description'       => $l->description,
            'price'             => $l->price ? (float) $l->price : null,
            'currency'          => $l->currency,
            'negotiable'        => (bool) $l->negotiable,
            'photo_url'         => $l->photo_url,
            'lat'               => $l->lat ? (float) $l->lat : null,
            'lng'               => $l->lng ? (float) $l->lng : null,
            'contact_phone'     => $l->contact_phone,
            'contact_whatsapp'  => $l->contact_whatsapp,
            'status'            => $l->status,
            'is_featured'       => $l->featured_until && $l->featured_until->isFuture(),
            'views'             => (int) $l->views,
            'expires_at'        => $l->expires_at,
            'created_at'        => $l->created_at,
        ];
    }
}
