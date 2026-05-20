<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\User $user */
        $user = $this->resource;

        return [
            'id'          => $user->id,
            'name'        => $user->username,
            'username'    => $user->username,
            'phone'       => $user->phone,
            'avatar_url'  => $user->avatar_url,
            'is_verified' => (bool) $user->is_verified,
            'city'        => $user->zone?->name,
        ];
    }
}
