<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Fillable(['business_id', 'kind', 'hour', 'dow'])]
class BusinessClickEvent extends Model
{
    public $timestamps = false;
    protected $casts = ['created_at' => 'datetime', 'hour' => 'int', 'dow' => 'int'];

    /**
     * Returns a 24-element histogram for a business, values 0-100 normalized
     * to the peak hour. Cached for an hour (clicks roll in continuously but
     * the relative shape doesn't change minute-to-minute).
     *
     * Empty / insufficient data → null so the UI can hide the section.
     *
     * @return array<int, int>|null  e.g. [0=>5, 1=>0, 2=>0, ..., 13=>100, 14=>87, ...]
     */
    public static function popularTimesFor(int $businessId, int $minTotal = 10): ?array
    {
        return Cache::remember("popular-times:$businessId:v1", 3600, function () use ($businessId, $minTotal) {
            if (! Schema::hasTable('business_click_events')) return null;

            $rows = DB::table('business_click_events')
                ->where('business_id', $businessId)
                ->where('created_at', '>=', now()->subDays(90))
                ->selectRaw('hour, COUNT(*) as c')
                ->groupBy('hour')
                ->pluck('c', 'hour')
                ->all();

            $total = array_sum($rows);
            if ($total < $minTotal) return null;

            $peak = max($rows);
            $hist = [];
            for ($h = 0; $h < 24; $h++) {
                $hist[$h] = $peak > 0 ? (int) round(($rows[$h] ?? 0) / $peak * 100) : 0;
            }
            return $hist;
        });
    }
}
