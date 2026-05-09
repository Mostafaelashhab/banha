<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'user_id', 'zone_id', 'kind', 'title', 'description', 'location',
    'lat', 'lng', 'starts_at', 'ends_at', 'cover_url',
    'contact_phone', 'attendees_count', 'status',
])]
class Event extends Model
{
    public const KINDS = [
        'wedding'   => ['label' => 'فرح',          'icon' => 'heart',  'tone' => 'coral'],
        'concert'   => ['label' => 'حفلة',         'icon' => 'flame',  'tone' => 'honey'],
        'sports'    => ['label' => 'رياضة',         'icon' => 'flame',  'tone' => 'mint'],
        'community' => ['label' => 'حدث مجتمعي',    'icon' => 'bell',   'tone' => 'coral'],
        'religious' => ['label' => 'ديني',          'icon' => 'check',  'tone' => 'mint'],
        'other'     => ['label' => 'تاني',          'icon' => 'tag',    'tone' => 'blush'],
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
            'lat'       => 'decimal:7',
            'lng'       => 'decimal:7',
        ];
    }

    public function user(): BelongsTo  { return $this->belongsTo(User::class); }
    public function zone(): BelongsTo  { return $this->belongsTo(Zone::class); }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_attendees');
    }

    public function kindMeta(): array
    {
        return self::KINDS[$this->kind] ?? self::KINDS['other'];
    }

    public function isPast(): bool
    {
        $end = $this->ends_at ?? $this->starts_at;
        return $end && $end->isPast();
    }
}
