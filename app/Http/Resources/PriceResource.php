<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Price $p */
        $p = $this->resource;

        return [
            'id'           => $p->id,
            'product'      => $p->product?->name,
            'product_id'   => $p->product_id,
            'price'        => (float) $p->price,
            'shop_name'    => $p->shop_name,
            'photo_url'    => $p->photo_url,
            'notes'        => $p->notes,
            'zone'         => $p->zone?->name,
            'reporter'     => $p->user?->username,
            'created_at'   => $p->created_at,
        ];
    }
}
