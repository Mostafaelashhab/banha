<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Zone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Assign each business to its geographically nearest zone using its lat/lng.
 *
 * Older imports tagged every row to a single default zone (Banha). After we
 * seeded the rest of Qalyubia in the Zones table, this corrects that drift.
 *
 *   php artisan banha:reassign-zones --dry           # preview the moves
 *   php artisan banha:reassign-zones                 # apply
 *   php artisan banha:reassign-zones --owned         # include owner-claimed
 *   php artisan banha:reassign-zones --only-default  # only move rows in the default zone
 */
class ReassignZones extends Command
{
    protected $signature = 'banha:reassign-zones
                            {--dry : Preview only, no writes}
                            {--owned : Also reassign rows claimed by an owner (default: skip)}
                            {--only-default : Only reassign rows currently in the default (first) zone}';

    protected $description = 'Reassign businesses to their geographically nearest zone';

    public function handle(): int
    {
        $dry         = (bool) $this->option('dry');
        $includeOwned = (bool) $this->option('owned');
        $onlyDefault = (bool) $this->option('only-default');

        $zones = Zone::whereNotNull('lat')->whereNotNull('lng')->get();
        if ($zones->isEmpty()) {
            $this->error('No zones with lat/lng — seed zone coordinates first.');
            return self::FAILURE;
        }

        $defaultZoneId = Zone::orderBy('sort')->first()?->id;

        $q = Business::whereNotNull('lat')->whereNotNull('lng');
        if (! $includeOwned)  $q->whereNull('owner_user_id');
        if ($onlyDefault)     $q->where('zone_id', $defaultZoneId);

        $total = $q->count();
        if ($total === 0) {
            $this->info('Nothing to reassign with the current filters.');
            return self::SUCCESS;
        }

        $this->info("Scanning {$total} businesses…" . ($dry ? ' (dry-run)' : ''));
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $moves = []; // [fromZoneId => [toZoneId => count]]
        $unchanged = 0;

        $q->chunkById(500, function ($chunk) use ($zones, $dry, &$moves, &$unchanged, $bar) {
            foreach ($chunk as $b) {
                $bar->advance();
                $nearest = $this->nearestZone((float) $b->lat, (float) $b->lng, $zones);
                if (! $nearest) continue;
                if ((int) $b->zone_id === (int) $nearest->id) { $unchanged++; continue; }

                $from = (int) ($b->zone_id ?? 0);
                $to   = (int) $nearest->id;
                $moves[$from][$to] = ($moves[$from][$to] ?? 0) + 1;

                if (! $dry) {
                    // Skip the model's mutators/events — pure column update for speed
                    Business::where('id', $b->id)->update(['zone_id' => $to]);
                }
            }
        });

        $bar->finish();
        $this->newLine(2);

        // Per-zone after-counts (effective)
        $zonesById = $zones->keyBy('id');
        $newCounts = [];
        foreach ($moves as $from => $tos) {
            foreach ($tos as $to => $n) {
                $newCounts[$to] = ($newCounts[$to] ?? 0) + $n;
                $newCounts[$from] = ($newCounts[$from] ?? 0) - $n;
            }
        }
        $rows = [];
        foreach ($zones as $z) {
            $current = Business::where('zone_id', $z->id)->count();
            $effective = $current + ($dry ? ($newCounts[$z->id] ?? 0) : 0);
            $rows[] = [$z->name, $current, $dry ? $effective : $current];
        }
        $this->table(['Zone', $dry ? 'Before' : 'After', $dry ? 'After (preview)' : 'Same'], $rows);
        $this->info("Unchanged: {$unchanged}");

        if (! $dry) {
            // Bust map cache so the new distribution surfaces immediately
            Cache::forget('map-data:v5:all');
            foreach (array_keys(Business::CATEGORIES) as $cat) {
                Cache::forget('map-data:v5:'.$cat);
            }
            $this->info('Map cache cleared.');
        }

        return self::SUCCESS;
    }

    private function nearestZone(float $lat, float $lng, $zones): ?Zone
    {
        $best = null; $bestD = INF;
        foreach ($zones as $z) {
            $d = $this->haversineKm($lat, $lng, (float) $z->lat, (float) $z->lng);
            if ($d < $bestD) { $bestD = $d; $best = $z; }
        }
        return $best;
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371.0;
        $toRad = static fn (float $d): float => $d * M_PI / 180;
        $dLat = $toRad($lat2 - $lat1);
        $dLng = $toRad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos($toRad($lat1)) * cos($toRad($lat2)) * sin($dLng / 2) ** 2;
        return 2 * $R * asin(sqrt($a));
    }
}
