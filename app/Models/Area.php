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
     * Only Banha-proper areas. The seeder also ships other Qalyubia
     * markaz centers (Tukh, Qalyub, Qaha, …) but they're hidden across
     * the app until coverage expands.
     */
    public function scopeBanha(Builder $q): Builder
    {
        return $q->where('parent', 'بنها');
    }

    /** Max distance (km) to allow auto-snap. Beyond this we return null
     *  with the actual distance so the caller can decide. */
    public const NEAREST_SNAP_KM = 5.0;

    /**
     * Find the nearest area to a given lat/lng using a cheap Haversine.
     *
     * `area` is null when:
     *  - no area in the parent has coords set
     *  - the closest match is farther than NEAREST_SNAP_KM (likely the user
     *    isn't in Banha at all). `distance_km` still reflects what we found
     *    so the UI can say "أقرب منطقة بعيدة بـ N كم".
     *
     * @return array{area: ?self, distance_km: ?float}
     */
    public static function nearest(float $lat, float $lng, ?string $parent = null): array
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
        if ($candidates->isEmpty()) {
            return ['area' => null, 'distance_km' => null];
        }

        $best = null; $bestDist = INF;
        foreach ($candidates as $a) {
            $d = self::haversineKm($lat, $lng, (float) $a->lat, (float) $a->lng);
            if ($d < $bestDist) { $bestDist = $d; $best = $a; }
        }

        return [
            'area'        => $bestDist <= self::NEAREST_SNAP_KM ? $best : null,
            'distance_km' => round($bestDist, 1),
        ];
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
