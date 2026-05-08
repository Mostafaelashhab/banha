<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'zone_id', 'type', 'description', 'lat', 'lng', 'confirmations', 'is_verified', 'is_resolved', 'expires_at'])]
class Alert extends Model
{
    public const TYPES = [
        'traffic'     => ['label' => 'زحمة',   'icon' => 'traffic',     'tone' => 'blush'],
        'electricity' => ['label' => 'كهربا',  'icon' => 'bolt',        'tone' => 'mint'],
        'water'       => ['label' => 'مياه',   'icon' => 'tag',         'tone' => 'mint'],
        'accident'    => ['label' => 'حادثة',  'icon' => 'map-pin',     'tone' => 'blush'],
        'checkpoint'  => ['label' => 'كمين',   'icon' => 'flag',        'tone' => 'coral'],
        'other'       => ['label' => 'تاني',   'icon' => 'bell',        'tone' => 'coral'],
    ];

    public const DEFAULT_TTL_HOURS = 6;

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_resolved' => 'boolean',
            'expires_at'  => 'datetime',
            'lat'         => 'decimal:7',
            'lng'         => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_resolved', false)->where('expires_at', '>', now());
    }

    public function typeMeta(): array
    {
        return self::TYPES[$this->type] ?? self::TYPES['other'];
    }
}
