<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\MenuCategory $c */
        $c = $this->resource;

        return [
            'id'    => $c->id,
            'name'  => $c->name,
            'sort'  => (int) $c->sort,
            'items' => $this->whenLoaded('items', fn () => $c->items->map(fn ($i) => [
                'id'           => $i->id,
                'name'         => $i->name,
                'description'  => $i->description,
                'price'        => (float) $i->price,
                'photo_url'    => $i->photo_url,
                'is_available' => (bool) $i->is_available,
            ])),
        ];
    }
}
