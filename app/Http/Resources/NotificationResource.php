<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Notification $n */
        $n = $this->resource;

        return [
            'id'         => $n->id,
            'icon'       => $n->icon ?? 'bell',
            'title'      => $n->title ?? '',
            'body'       => $n->body ?? null,
            'link'       => $n->link ?? null,
            'read_at'    => $n->read_at,
            'created_at' => $n->created_at,
        ];
    }
}
