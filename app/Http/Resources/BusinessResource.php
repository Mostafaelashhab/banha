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

        $isOpen = method_exists($b, 'isOpenNow') ? $b->isOpenNow() : null;

        return [
            'id'             => $b->id,
            'slug'           => $b->slug,
            'name'           => $b->name,
            'subtitle'       => $b->description ? mb_substr($b->description, 0, 120) : null,
            'description'    => $b->description,
            'category'       => $b->category,
            'category_label' => $b->categoryMeta()['label'] ?? null,
            'sub_type'       => $b->sub_type,
            'cover_url'      => $b->photo_url,
            'logo_url'       => null,
            'lat'            => $b->lat ? (float) $b->lat : null,
            'lng'            => $b->lng ? (float) $b->lng : null,
            'address'        => $b->address,
            'phone'          => $b->phone,
            'whatsapp'       => $b->whatsapp,
            'hotline'        => $b->hotline,
            'is_open'        => $isOpen,
            'is_24h'         => (bool) $b->is_24h,
            'hours_text'     => $b->hours,
            'hours_schedule' => $b->hours_schedule,
            'rating'         => $b->rating_avg ? (float) $b->rating_avg : null,
            'reviews_count'  => (int) ($b->ratings_count ?? 0),
            'is_verified'    => (bool) $b->is_verified,
            'tier'           => $tier,
            'is_sponsored'   => $b->promoted_until && $b->promoted_until->isFuture(),
            'distance_m'     => isset($b->distance_m) ? (float) $b->distance_m : null,
            'has_menu'       => (bool) $b->has_menu,
            'menu_currency'  => $b->menu_currency,
            'booking_enabled'=> (bool) $b->booking_enabled,
            'features'       => $b->features ?: [],
            'photos'         => $this->whenLoaded('photos', fn () => $b->photos->take(6)->map(fn ($p) => [
                'id'  => $p->id,
                'url' => $p->url,
            ])->values()),
            'photos_count'   => $this->whenLoaded('photos', fn () => $b->photos->count()),
            'reviews'        => $this->whenLoaded('reviews', fn () => $b->reviews->take(3)->map(fn ($r) => [
                'id'           => $r->id,
                'rating'       => (float) $r->rating,
                'body'         => $r->body,
                'author_name'  => $r->author_name ?? $r->user?->username,
                'reviewed_at'  => $r->reviewed_at ?? $r->created_at,
            ])->values()),
        ];
    }
}
