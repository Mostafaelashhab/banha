<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Master scrape command — runs every legitimate data source we have for Banha
 * and reports the per-category coverage delta. Designed to be cron-friendly
 * (idempotent: existing entries get updated, claimed entries are left alone).
 *
 *   php artisan banha:scrape-all                  # full run
 *   php artisan banha:scrape-all --skip-cityapp   # OSM only
 *   php artisan banha:scrape-all --skip-osm       # city-app only
 */
class BanhaScrapeAll extends Command
{
    protected $signature = 'banha:scrape-all
                            {--skip-cityapp : Skip the city-app source}
                            {--skip-osm     : Skip OpenStreetMap}
                            {--skip-firebase : Skip the public Firebase RTDB import}
                            {--osm-radii=15000,30000 : Comma-list of OSM radii to try (meters)}
                            {--sleep=400    : ms between requests where applicable}';

    protected $description = 'Run every legitimate Banha data source (Firebase + city-app + OSM + …) in one shot';

    public function handle(): int
    {
        $before = $this->snapshotCounts();
        $sleep  = (int) $this->option('sleep');

        // ── 1) Firebase RTDB — base restaurant records + extras (sponsor flags, reviews) ─
        if (! $this->option('skip-firebase')) {
            $this->info('━━ Firebase RTDB (restaurants) ━━');
            try { $this->call('banha:import-firebase'); }
            catch (\Throwable $e) { $this->warn('Firebase restaurants failed: '.$e->getMessage()); }

            $this->info('━━ Firebase RTDB (sponsor + reviews) ━━');
            try { $this->call('banha:import-firebase-extras'); }
            catch (\Throwable $e) { $this->warn('Firebase extras failed: '.$e->getMessage()); }
        }

        // ── 2) city-app — restaurants, cafes, shops, services ──────────────
        if (! $this->option('skip-cityapp')) {
            $this->info('━━ city-app.org ━━');
            $this->call('scrape:cityapp', [
                '--skip-existing' => true,
                '--sleep'         => $sleep,
            ]);
        }

        // ── 3) OpenStreetMap — multi-radius retry to dodge Overpass 504s ──
        if (! $this->option('skip-osm')) {
            $radii = array_filter(array_map('intval', explode(',', (string) $this->option('osm-radii'))));
            foreach ($radii as $r) {
                $this->info("━━ OSM radius {$r}m around Banha ━━");
                try {
                    $this->call('banha:import-osm', [
                        '--area'   => '',      // force radius mode
                        '--radius' => $r,
                        '--lat'    => 30.4582,
                        '--lng'    => 31.1797,
                    ]);
                } catch (\Throwable $e) {
                    $this->warn("OSM radius={$r}m failed: ".$e->getMessage());
                }
                sleep(2);   // be polite to Overpass between runs
            }
        }

        // ── 4) Report ──────────────────────────────────────────────────────
        $after = $this->snapshotCounts();
        $this->newLine();
        $this->info('━━ Coverage delta ━━');
        $rows = [];
        foreach (Business::CATEGORIES as $key => $meta) {
            $b = $before[$key] ?? 0;
            $a = $after[$key]  ?? 0;
            $delta = $a - $b;
            $rows[] = [
                $meta['label'] ?? $key,
                $b,
                $a,
                $delta > 0 ? '+'.$delta : ($delta < 0 ? (string) $delta : '·'),
            ];
        }
        $this->table(['Category', 'Before', 'After', 'Δ'], $rows);

        // Bust map cache so the new entries surface immediately
        Cache::forget('map-data:v5:all');
        foreach (array_keys(Business::CATEGORIES) as $cat) {
            Cache::forget('map-data:v5:'.$cat);
        }

        return self::SUCCESS;
    }

    /** Per-category active count. */
    private function snapshotCounts(): array
    {
        return DB::table('businesses')
            ->where('is_active', true)
            ->selectRaw('category, count(*) as c')
            ->groupBy('category')
            ->pluck('c', 'category')
            ->all();
    }
}
