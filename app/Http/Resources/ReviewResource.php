<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\BusinessReview $r */
        $r = $this->resource;

        return [
            'id'           => $r->id,
            'rating'       => (float) $r->rating,
            'body'         => $r->body,
            'author_name'  => $r->author_name ?? $r->user?->username,
            'source'       => $r->source,
            'reviewed_at'  => $r->reviewed_at ?? $r->created_at,
            'created_at'   => $r->created_at,
        ];
    }
}
