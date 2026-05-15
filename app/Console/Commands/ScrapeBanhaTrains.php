<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Pulls Banha train schedules off egytrains.com and saves a normalized
 * JSON file we serve from /banha-trains.
 *
 * Their Next.js page embeds the schedule in __NEXT_DATA__ as:
 *   data: { "<train_number>": {type, startTime, endTime, duration, stops}, … }
 *
 * Each URL is one direction (e.g. /trains/Cairo/Banha gives incoming Cairo→Banha).
 * We scrape both directions for the most-used destinations.
 *
 * Run nightly via cron, or manually whenever schedules look stale:
 *   php artisan trains:scrape:banha
 */
class ScrapeBanhaTrains extends Command
{
    protected $signature   = 'trains:scrape:banha {--throttle=1 : Seconds between requests}';
    protected $description = 'Scrape Banha train schedules from egytrains.com';

    /**
     * Major destinations we care about — kept short so we don't hit
     * egytrains 400+ times. Each gets scraped in both directions.
     *
     * Pair: [English slug for egytrains, Arabic display name]
     */
    private const DESTINATIONS = [
        ['Cairo',         'القاهرة'],
        ['Alexandria',    'الإسكندرية'],
        ['Sidi Gaber',    'سيدي جابر'],
        ['Tanta',         'طنطا'],
        ['Damanhour',     'دمنهور'],
        ['Mansoura',      'المنصورة'],
        ['Damietta',      'دمياط'],
        ['Port Said',     'بورسعيد'],
        ['Suez',          'السويس'],
        ['Ismailia',      'الإسماعيلية'],
        ['Zagazig',       'الزقازيق'],
        ['Giza',          'الجيزة'],
        ['Beni Suef',     'بني سويف'],
        ['Minya',         'المنيا'],
        ['Asyut',         'أسيوط'],
        ['Sohag',         'سوهاج'],
        ['Qena',          'قنا'],
        ['Luxor',         'الأقصر'],
        ['Aswan',         'أسوان'],
        ['Kafr Sheikh',   'كفر الشيخ'],
        ['Kafr Zayat',    'كفر الزيات'],
        ['Mahalla Kubra', 'المحلة الكبرى'],
        ['Quesna',        'قويسنا'],
        ['Berket Saba',   'بركة السبع'],
        ['Qalyoub',       'القليوب'],
        ['Toukh',         'طوخ'],
        ['Marsa Matruh',  'مرسى مطروح'],
    ];

    public function handle(): int
    {
        $throttle = (int) $this->option('throttle');
        $outgoing = []; // Banha → X
        $incoming = []; // X → Banha
        $allNumbers = []; // collected for the per-train details pass
        $hit = 0; $miss = 0;

        $this->info('Phase 1/2: route schedules');
        foreach (self::DESTINATIONS as [$en, $ar]) {
            // Banha → destination
            $rows = $this->fetchRoute('Banha', $en);
            if ($rows !== null) {
                $outgoing[$en] = ['ar' => $ar, 'trains' => $rows];
                foreach ($rows as $r) $allNumbers[$r['number']] = true;
                $hit++;
                $this->line("  ✓ Banha → {$en}: ".count($rows).' قطار');
            } else {
                $miss++;
                $this->line("  ✗ Banha → {$en}: failed");
            }
            sleep($throttle);

            // destination → Banha
            $rows = $this->fetchRoute($en, 'Banha');
            if ($rows !== null) {
                $incoming[$en] = ['ar' => $ar, 'trains' => $rows];
                foreach ($rows as $r) $allNumbers[$r['number']] = true;
                $hit++;
                $this->line("  ✓ {$en} → Banha: ".count($rows).' قطار');
            } else {
                $miss++;
                $this->line("  ✗ {$en} → Banha: failed");
            }
            sleep($throttle);
        }

        // Phase 2: per-train stops. Each /train/{n} returns the full city list
        // with departure (d) and arrival (a) times at each stop. Keyed by
        // train number so we fetch each one only once.
        $this->info("\nPhase 2/2: per-train stops ({" . count($allNumbers) . "} unique trains)");
        $details = [];
        foreach (array_keys($allNumbers) as $i => $number) {
            $stops = $this->fetchTrainStops($number);
            if ($stops !== null) {
                $details[$number] = $stops;
                if ($i % 10 === 0) {
                    $this->line("  ✓ {$i}/" . count($allNumbers) . " · train #{$number} · " . count($stops['stops']) . ' محطة');
                }
            }
            // Smaller throttle for the second pass — many requests.
            usleep(max(200_000, $throttle * 500_000));
        }

        $payload = [
            'scraped_at' => now()->toAtomString(),
            'source'     => 'https://egytrains.com',
            'outgoing'   => $outgoing,
            'incoming'   => $incoming,
            'details'    => $details,
        ];

        Storage::disk('local')->put(
            'trains/banha-schedules.json',
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        $this->info("\nDone. {$hit} routes + " . count($details) . ' train detail pages scraped.');
        $this->info('Saved to storage/app/private/trains/banha-schedules.json');

        return self::SUCCESS;
    }

    /**
     * Fetch the per-train stops list. Returns:
     *   ['working' => string, 'stops' => [['name' => string, 'd' => 'HH:MM', 'a' => 'HH:MM']]]
     * or null on failure.
     */
    private function fetchTrainStops(string $number): ?array
    {
        $url = 'https://egytrains.com/train/' . rawurlencode($number);
        try {
            $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (Banhawy schedule importer)'])
                ->timeout(15)->get($url);
            if (! $resp->successful()) return null;
        } catch (\Throwable $e) {
            return null;
        }

        if (! preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $resp->body(), $m)) {
            return null;
        }
        $data = json_decode($m[1], true);
        if (! is_array($data)) return null;

        $d = $data['props']['pageProps']['data'] ?? null;
        if (! is_array($d) || empty($d['cities']) || ! is_array($d['cities'])) return null;

        $stops = [];
        foreach ($d['cities'] as $c) {
            if (empty($c['name'])) continue;
            $stops[] = [
                'name' => (string) $c['name'],
                'd'    => $c['d'] ?? null,  // departure time (last stop has none)
                'a'    => $c['a'] ?? null,  // arrival time (first stop has none)
            ];
        }
        return [
            'working' => (string) ($d['working'] ?? ''),
            'stops'   => $stops,
        ];
    }

    /**
     * Fetch one direction. Returns an array of trains sorted by startTime,
     * or null on failure.
     *
     * @return array<array{number:string,type:string,start:string,end:string,duration:string,stops:int}>|null
     */
    private function fetchRoute(string $from, string $to): ?array
    {
        $url = 'https://egytrains.com/trains/' . rawurlencode($from) . '/' . rawurlencode($to);
        try {
            $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (Banhawy schedule importer)'])
                ->timeout(15)->get($url);
            if (! $resp->successful()) return null;
        } catch (\Throwable $e) {
            return null;
        }

        $html = $resp->body();
        if (! preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $m)) {
            return null;
        }
        $data = json_decode($m[1], true);
        if (! is_array($data)) return null;

        $payload = $data['props']['pageProps']['data'] ?? null;
        if (! is_array($payload)) return null;

        $rows = [];
        foreach ($payload as $trainNumber => $train) {
            if (! is_array($train) || empty($train['startTime'])) continue;
            $rows[] = [
                'number'   => (string) $trainNumber,
                'type'     => $train['type']      ?? '',
                'start'    => $train['startTime'] ?? '',
                'end'      => $train['endTime']   ?? '',
                'duration' => $train['duration']  ?? '',
                'stops'    => (int) ($train['stops'] ?? 0),
            ];
        }
        // Sort by departure time
        usort($rows, fn ($a, $b) => strcmp($a['start'], $b['start']));
        return $rows;
    }
}
