<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Zone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Pull notable places from Wikidata located inside Qalyubia (Q330055).
 *
 * Wikidata is free, ODbL-friendly, no API key. Coverage: landmarks,
 * museums, hospitals, universities, bridges, mosques, churches, parks.
 * Smaller volume than OSM but adds named places OSM often misses.
 *
 *   php artisan banha:import-wikidata
 *   php artisan banha:import-wikidata --qid=Q330055     # custom area Q-id
 *   php artisan banha:import-wikidata --dry              # preview
 */
class ImportWikidata extends Command
{
    protected $signature = 'banha:import-wikidata
        {--qid=Q330055 : Wikidata Q-id of the area (default = Qalyubia)}
        {--dry : Preview only, no DB writes}';

    protected $description = 'Seed businesses from Wikidata (notable places) — Qalyubia governorate';

    /**
     * Map Wikidata "instance of" Q-id → [our category, sub_type].
     * Compiled from common types in Egyptian governorates.
     */
    private const TYPE_MAP = [
        // Museums / monuments
        'Q33506'   => ['tourist', 'tour_monument'],   // museum
        'Q207694'  => ['tourist', 'tour_monument'],   // art museum
        'Q1244442' => ['tourist', 'tour_monument'],   // history museum
        'Q839954'  => ['tourist', 'tour_monument'],   // archaeological site
        'Q570116'  => ['tourist', 'tour_monument'],   // tourist attraction

        // Religious
        'Q32815'    => ['religious', 'rel_mosque'],   // mosque
        'Q16970'    => ['religious', 'rel_church'],   // church
        'Q24398318' => ['religious', 'rel_zawia'],    // sufi shrine

        // Education
        'Q3914'    => ['education', 'edu_school_prim'],  // school
        'Q9842'    => ['education', 'edu_school_prim'],  // primary school
        'Q149566'  => ['education', 'edu_school_prep'],  // secondary school
        'Q159334'  => ['education', 'edu_school_sec'],   // high school
        'Q3918'    => ['education', 'edu_university'],   // university
        'Q189004'  => ['education', 'edu_university'],   // college
        'Q5341295' => ['education', 'edu_lang'],         // language school

        // Health
        'Q16917'  => ['medical', 'medical_other'],   // hospital
        'Q7257872'=> ['medical', 'medical_other'],   // public hospital

        // Transport
        'Q55488'  => ['transport', 'trn_railway'],   // railway station
        'Q124817' => ['transport', 'trn_bus'],       // bus station
        'Q15324'  => ['tourist',   'tour_park'],     // body of water
        'Q12280'  => ['tourist',   'tour_monument'], // bridge

        // Tourist / parks
        'Q22698'   => ['tourist', 'tour_park'],      // park
        'Q3947'    => ['tourist', 'tour_other'],     // house
        'Q23413'   => ['tourist', 'tour_monument'],  // castle
        'Q57831'   => ['tourist', 'tour_monument'],  // ruin
        'Q11483816'=> ['tourist', 'tour_other'],     // notable building

        // Sports
        'Q483110'  => ['services', 'gym'],           // sports venue
        'Q483867'  => ['services', 'gym'],           // sports centre
        'Q483242'  => ['services', 'gym'],           // stadium

        // Government
        'Q1497375' => ['government', 'gov_other'],   // public authority
        'Q15765487'=> ['government', 'gov_other'],   // governorate office

        // Banks / commercial
        'Q22687' => ['banks', 'bank_branch'],        // bank
        'Q7075'  => ['shops', 'bookshop'],           // library
    ];

    public function handle(): int
    {
        $qid = trim((string) $this->option('qid'));
        $dry = (bool) $this->option('dry');

        $this->info("Querying Wikidata SPARQL for places in {$qid}...");

        // SPARQL: items located (P131*) in the governorate, with coords (P625) and type (P31).
        // We pull labels in Arabic with English fallback.
        $sparql = <<<SPARQL
SELECT ?item ?itemLabel ?itemAr ?coord ?type ?typeLabel WHERE {
  ?item wdt:P131* wd:{$qid} .
  ?item wdt:P625 ?coord .
  OPTIONAL { ?item wdt:P31 ?type . }
  OPTIONAL { ?item rdfs:label ?itemAr . FILTER(LANG(?itemAr) = "ar") }
  SERVICE wikibase:label { bd:serviceParam wikibase:language "ar,en". }
}
LIMIT 5000
SPARQL;

        // Wikidata requires a descriptive User-Agent and is heavily rate-limited.
        // Retry with exponential backoff on 429/502/503 (their cluster is often busy).
        $res = null;
        $attempts = 0;
        $maxAttempts = 5;
        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $res = Http::timeout(180)
                    ->connectTimeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Banhawy/1.0 (https://banhawy.app; ops@banhawy.app) Laravel-import',
                        'Accept'     => 'application/sparql-results+json',
                    ])
                    ->get('https://query.wikidata.org/sparql', ['query' => $sparql]);
            } catch (\Throwable $e) {
                $this->warn("Attempt {$attempts}: ".$e->getMessage());
                $res = null;
            }

            if ($res && $res->ok()) break;

            $status = $res?->status() ?? 0;
            // 429 (rate-limited), 502/503/504 (server overload) → backoff and retry
            if ($attempts < $maxAttempts && in_array($status, [0, 429, 502, 503, 504], true)) {
                $delay = min(60, 2 ** $attempts); // 2, 4, 8, 16, 32 seconds
                $this->warn("HTTP {$status}, retrying in {$delay}s (attempt {$attempts}/{$maxAttempts})...");
                sleep($delay);
                continue;
            }
            break;
        }

        if (! $res || ! $res->ok()) {
            $this->error('Wikidata failed after '.$attempts.' attempts: HTTP '.($res?->status() ?? 'no-response'));
            return self::FAILURE;
        }

        $rows = $res->json('results.bindings') ?? [];
        $this->info('Got '.count($rows).' Wikidata rows. Mapping…');

        // Group by item — same item may have multiple types (multiple P31 statements)
        $byItem = [];
        foreach ($rows as $r) {
            $uri = $r['item']['value'] ?? null;
            if (! $uri) continue;
            $byItem[$uri] ??= [
                'name_ar'  => null,
                'name_any' => $r['itemLabel']['value'] ?? null,
                'coord'    => $r['coord']['value'] ?? null,
                'types'    => [],
            ];
            if (! empty($r['itemAr']['value'])) $byItem[$uri]['name_ar'] = $r['itemAr']['value'];
            if (! empty($r['type']['value']))   $byItem[$uri]['types'][] = basename($r['type']['value']);
        }

        $defaultZone = Zone::orderBy('sort')->first();
        if (! $defaultZone) {
            $this->error('No zones in DB — create at least one zone first.');
            return self::FAILURE;
        }

        $stats = ['imported' => 0, 'updated' => 0, 'skipped_no_map' => 0, 'skipped_no_name' => 0, 'skipped_existing' => 0, 'skipped_no_coord' => 0];
        $bar = $this->output->createProgressBar(count($byItem));
        $bar->start();

        foreach ($byItem as $uri => $data) {
            $bar->advance();

            $name = $data['name_ar'] ?: $data['name_any'];
            if (! $name || mb_strlen(trim($name)) < 2) { $stats['skipped_no_name']++; continue; }

            // Wikidata coords look like "Point(31.18 30.45)" — lng first, then lat.
            if (! preg_match('/Point\(([\-\d\.]+)\s+([\-\d\.]+)\)/', (string) $data['coord'], $m)) {
                $stats['skipped_no_coord']++; continue;
            }
            [$lng, $lat] = [(float) $m[1], (float) $m[2]];

            // Pick the first type that maps to our directory
            $mapped = null;
            foreach ($data['types'] as $t) {
                if (isset(self::TYPE_MAP[$t])) { $mapped = self::TYPE_MAP[$t]; break; }
            }
            if (! $mapped) { $stats['skipped_no_map']++; continue; }

            [$category, $subType] = $mapped;
            $sm = Business::SUB_TYPES[$subType] ?? null;
            $wdId = 'wd:'.basename($uri);

            $payload = [
                'name'          => mb_substr(trim($name), 0, 120),
                'category'      => $category,
                'sub_type'      => $subType,
                'zone_id'       => $defaultZone->id,
                'owner_user_id' => null,
                'lat'           => $lat,
                'lng'           => $lng,
                'is_verified'   => false,
                'is_active'     => true,
                'emoji'         => $sm['emoji'] ?? null,
            ];

            if ($dry) { $stats['imported']++; continue; }

            $existing = Business::where('external_id', $wdId)->first();
            if ($existing) {
                if ($existing->owner_user_id === null) {
                    $existing->update($payload);
                    $stats['updated']++;
                } else {
                    $stats['skipped_existing']++;
                }
                continue;
            }

            Business::create(array_merge($payload, ['external_id' => $wdId]));
            $stats['imported']++;
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['metric', 'count'], [
            ['imported (new)',                 $stats['imported']],
            ['updated',                        $stats['updated']],
            ['skipped: no name',               $stats['skipped_no_name']],
            ['skipped: no coords',             $stats['skipped_no_coord']],
            ['skipped: type not mapped',       $stats['skipped_no_map']],
            ['skipped: owned by user',         $stats['skipped_existing']],
        ]);

        if (! $dry) {
            \Illuminate\Support\Facades\Cache::forget('map-data:v3:all');
            foreach (array_keys(Business::CATEGORIES) as $cat) {
                \Illuminate\Support\Facades\Cache::forget('map-data:v3:'.$cat);
            }
        }

        return self::SUCCESS;
    }
}
