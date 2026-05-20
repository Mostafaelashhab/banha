<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Order $o */
        $o = $this->resource;

        return [
            'id'              => $o->id,
            'business_id'     => $o->business_id,
            'business'        => $this->whenLoaded('business', fn () => new BusinessResource($o->business)),
            'customer_name'   => $o->customer_name,
            'customer_phone'  => $o->customer_phone,
            'customer_address'=> $o->customer_address,
            'delivery_fee'    => $o->delivery_fee ? (float) $o->delivery_fee : 0.0,
            'subtotal'        => (float) $o->subtotal,
            'currency'        => $o->currency,
            'notes'           => $o->notes,
            'status'          => $o->status,
            'items'           => $this->whenLoaded('items', fn () => $o->items->map(fn ($i) => [
                'id'         => $i->id,
                'name'       => $i->name,
                'qty'        => (int) $i->qty,
                'unit_price' => (float) $i->unit_price,
                'line_total' => (float) ($i->qty * $i->unit_price),
            ])),
            'created_at'      => $o->created_at,
        ];
    }
}
