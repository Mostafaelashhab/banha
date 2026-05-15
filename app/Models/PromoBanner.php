<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'business_id',
    'image_url', 'title', 'tag', 'description', 'cta_text', 'href',
    'bg_from', 'bg_to', 'sort_order', 'is_active', 'starts_at', 'ends_at',
])]
class PromoBanner extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** Image-only mode = uploaded image + no legacy title/desc overlay. */
    public function isImageOnly(): bool
    {
        return $this->image_url && empty($this->title) && empty($this->description);
    }

    /** Resolves the click destination — business link wins over a manual href. */
    public function destinationUrl(): string
    {
        if ($this->business_id) {
            // Use the eager-loaded relation when available, otherwise look it up.
            $b = $this->relationLoaded('business') ? $this->business : Business::find($this->business_id);
            if ($b) {
                return $b->slug ? url('/biz/' . $b->slug) : url('/directory/business/' . $b->id);
            }
        }
        return $this->href ?: url('/');
    }

    public function scopeLive(Builder $q): Builder
    {
        $now = now();
        return $q->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
            });
    }
}
