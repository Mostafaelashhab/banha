<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Post $p */
        $p = $this->resource;

        return [
            'id'              => $p->id,
            'title'           => $p->title,
            'body'            => $p->body,
            'category'        => $p->category,
            'image_url'       => $p->image_url,
            'is_announcement' => (bool) $p->is_announcement,
            'is_sponsored'    => (bool) $p->is_sponsored,
            'upvotes'         => (int) $p->upvotes,
            'downvotes'       => (int) $p->downvotes,
            'comments_count'  => (int) $p->comments_count,
            'author'          => $p->is_anonymous ? null : [
                'id'       => $p->user?->id,
                'username' => $p->user?->username,
            ],
            'zone'            => $p->zone?->name,
            'created_at'      => $p->created_at,
        ];
    }
}
