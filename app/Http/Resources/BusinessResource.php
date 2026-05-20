<?php

namespace App\Http\Resources;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Business $b */
        $b = $this->resource;

        $tier = null;
        if ($b->is_verified) {
            $tier = ($b->is_verified_paid && $b->verified_paid_until && $b->verified_paid_until->isFuture())
                ? 'gold'
                : 'silver';
        }

        return [
            'id'             => $b->id,
            'slug'           => $b->slug,
            'name'           => $b->name,
            'subtitle'       => $b->description ? mb_substr($b->description, 0, 120) : null,
            'category'       => $b->category,
            'cover_url'      => $b->photo_url,
            'logo_url'       => null,
            'lat'            => $b->lat ? (float) $b->lat : null,
            'lng'            => $b->lng ? (float) $b->lng : null,
            'address'        => $b->address,
            'phone'          => $b->phone,
            'whatsapp'       => $b->whatsapp,
            'is_open'        => method_exists($b, 'isOpenNow') ? $b->isOpenNow() : null,
            'rating'         => $b->rating_avg ? (float) $b->rating_avg : null,
            'reviews_count'  => (int) ($b->ratings_count ?? 0),
            'is_verified'    => (bool) $b->is_verified,
            'tier'           => $tier,
            'is_sponsored'   => $b->promoted_until && $b->promoted_until->isFuture(),
            'distance_m'     => isset($b->distance_m) ? (float) $b->distance_m : null,
        ];
    }
}
