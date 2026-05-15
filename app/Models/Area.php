<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'parent', 'lat', 'lng', 'sort'])]
class Area extends Model
{
    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('parent')->orderBy('sort')->orderBy('name');
    }

    /**
     * Find the nearest area to a given lat/lng using a cheap Haversine.
     * Returns null when no area has coordinates set.
     */
    public static function nearest(float $lat, float $lng, ?string $parent = null): ?self
    {
        $q = self::query()
            ->whereNotNull('lat')
            ->whereNotNull('lng');

        if ($parent) $q->where('parent', $parent);

        // Cheap bounding box filter (~ ±0.5° = ~55km) so we don't compute
        // Haversine for the whole table.
        $q->whereBetween('lat', [$lat - 0.5, $lat + 0.5])
          ->whereBetween('lng', [$lng - 0.5, $lng + 0.5]);

        $candidates = $q->limit(200)->get();
        if ($candidates->isEmpty()) return null;

        $best = null; $bestDist = INF;
        foreach ($candidates as $a) {
            $d = self::haversineKm($lat, $lng, (float) $a->lat, (float) $a->lng);
            if ($d < $bestDist) { $bestDist = $d; $best = $a; }
        }
        // Don't snap to something obviously wrong — > 30km from any known
        // area means the user is somewhere we don't cover.
        return $bestDist <= 30 ? $best : null;
    }

    private static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371.0; // earth radius km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return 2 * $r * asin(sqrt($a));
    }
}
