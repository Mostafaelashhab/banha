<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Zone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Pull OpenStreetMap nodes around Banha and seed them as businesses.
 *
 * Free, no API key, ODbL license — we attribute "© OpenStreetMap" on the map.
 *
 *   php artisan import:osm                     # 10km radius, all categories
 *   php artisan import:osm --radius=15000      # custom radius (meters)
 *   php artisan import:osm --dry                # show what would happen, no DB writes
 */
class ImportOsm extends Command
{
    protected $signature = 'banha:import-osm
        {--radius=10000 : Radius in meters around Banha center (used only when --area is empty)}
        {--lat=30.4582 : Center latitude (default Banha)}
        {--lng=31.1797 : Center longitude (default Banha)}
        {--area=القليوبية : Arabic name of an OSM admin area (governorate). Empty = use radius mode}
        {--admin-level=4 : OSM admin_level for the area (4=governorate, 6=district)}
        {--dry : Preview only, no DB writes}
        {--limit= : Stop after N entries (debug)}';

    protected $description = 'Seed businesses from OpenStreetMap (Overpass API) — Banha + Qalyubia';

    /**
     * Mapping table: OSM tag → [our category, sub_type].
     * Order matters: more specific tags should be checked first.
     * Returns null when we don't want this kind of node in the directory.
     */
    private function mapTags(array $tags): ?array
    {
        $a    = $tags['amenity']          ?? null;
        $s    = $tags['shop']             ?? null;
        $t    = $tags['tourism']          ?? null;
        $h    = $tags['healthcare']       ?? null;
        $l    = $tags['leisure']          ?? null;
        $r    = $tags['religion']         ?? null;
        $rail = $tags['railway']          ?? null;
        $o    = $tags['office']           ?? null;
        $c    = $tags['craft']            ?? null;
        $hist = $tags['historic']         ?? null;
        $pt   = $tags['public_transport'] ?? null;
        $hw   = $tags['highway']          ?? null;

        // ── Food ─────────────────────────────────────────
        if ($a === 'restaurant')          return ['food', 'restaurant'];
        if ($a === 'cafe')                return ['food', 'cafe'];
        if ($a === 'fast_food')           return ['food', 'fast_food'];
        if ($a === 'bakery' || $s === 'bakery') return ['food', 'bakery'];
        if ($s === 'pastry' || $s === 'confectionery') return ['food', 'sweets'];
        if ($a === 'ice_cream')           return ['food', 'sweets'];
        if ($a === 'juice_bar' || $s === 'beverages') return ['food', 'juice'];
        if ($a === 'food_court')          return ['food', 'food_other'];

        // ── Medical ──────────────────────────────────────
        if ($a === 'pharmacy' || $h === 'pharmacy')   return ['medical', 'pharmacy'];
        if ($a === 'hospital' || $h === 'hospital')   return ['medical', 'medical_other'];
        if ($a === 'clinic'   || $h === 'clinic')     return ['medical', 'medical_other'];
        if ($a === 'doctors'  || $h === 'doctor')     return ['medical', 'doctor'];
        if ($a === 'dentist'  || $h === 'dentist')    return ['medical', 'dentist'];
        if ($a === 'veterinary' || $h === 'veterinary') return ['medical', 'vet'];
        if ($h === 'laboratory') return ['medical', 'lab'];
        if ($h === 'physiotherapist')     return ['medical', 'physio'];

        // ── Banks ────────────────────────────────────────
        if ($a === 'bank')                return ['banks', 'bank_branch'];
        if ($a === 'atm')                 return ['banks', 'bank_atm'];
        if ($a === 'bureau_de_change')    return ['banks', 'bank_exchange'];

        // ── Government ──────────────────────────────────
        if ($a === 'townhall')            return ['government', 'gov_other'];
        if ($a === 'courthouse')          return ['government', 'gov_court'];
        if ($a === 'post_office')         return ['government', 'gov_post'];
        if ($a === 'police')              return ['emergency', 'emr_police'];
        if ($a === 'fire_station')        return ['emergency', 'emr_fire'];

        // ── Religious ───────────────────────────────────
        if ($a === 'place_of_worship') {
            if ($r === 'muslim')          return ['religious', 'rel_mosque'];
            if ($r === 'christian')       return ['religious', 'rel_church'];
            return ['religious', 'rel_mosque']; // sane default in Banha
        }

        // ── Education ───────────────────────────────────
        if ($a === 'kindergarten')        return ['education', 'edu_nursery'];
        if ($a === 'school') {
            $level = $tags['isced:level'] ?? '';
            if (str_contains($level, '1')) return ['education', 'edu_school_prim'];
            if (str_contains($level, '2')) return ['education', 'edu_school_prep'];
            if (str_contains($level, '3')) return ['education', 'edu_school_sec'];
            return ['education', 'edu_school_prim'];
        }
        if ($a === 'university' || $a === 'college') return ['education', 'edu_university'];
        if ($a === 'language_school')     return ['education', 'edu_lang'];
        if ($a === 'training' || $a === 'tutoring') return ['education', 'edu_center'];
        if ($a === 'library')             return ['shops', 'bookshop'];

        // ── Transport ───────────────────────────────────
        if ($a === 'bus_station')         return ['transport', 'trn_bus'];
        if ($rail === 'station' || $rail === 'halt') return ['transport', 'trn_railway'];
        if ($a === 'taxi')                return ['transport', 'trn_taxi'];
        if ($a === 'fuel')                return ['shops', 'gas_station'];
        if ($a === 'car_wash')            return ['services', 'car_wash'];
        if ($a === 'car_rental')          return ['services', 'car_rental'];
        if ($s === 'car_repair' || $s === 'car_parts') return ['craftsmen', 'mechanic_car'];
        if ($s === 'motorcycle_repair')   return ['craftsmen', 'mechanic_bike'];

        // ── Tourist / leisure ───────────────────────────
        if ($t === 'hotel' || $t === 'guest_house') return ['tourist', 'tour_other'];
        if ($t === 'museum' || $t === 'attraction') return ['tourist', 'tour_monument'];
        if ($l === 'park' || $a === 'park') return ['tourist', 'tour_park'];
        if ($l === 'fitness_centre' || $l === 'sports_centre') return ['services', 'gym'];
        if ($a === 'cinema')              return ['tourist', 'tour_cinema'];

        // ── Shops ───────────────────────────────────────
        if ($s === 'supermarket' || $s === 'mall') return ['shops', 'supermarket'];
        if ($s === 'convenience' || $s === 'general' || $a === 'marketplace') return ['shops', 'grocery'];
        if ($s === 'butcher')             return ['shops', 'butcher'];
        if ($s === 'seafood')             return ['shops', 'fish_shop'];
        if ($s === 'greengrocer')         return ['shops', 'fruit_veg'];
        if ($s === 'clothes' || $s === 'shoes') return ['shops', 'clothing'];
        if ($s === 'books')               return ['shops', 'bookshop'];
        if ($s === 'mobile_phone')        return ['shops', 'mobile_shop'];
        if ($s === 'electronics' || $s === 'computer') return ['shops', 'electronics'];
        if ($s === 'hardware' || $s === 'doityourself') return ['shops', 'hardware'];
        if ($s === 'jewelry')             return ['shops', 'gold_shop'];
        if ($s === 'toys')                return ['shops', 'toys'];
        if ($s === 'furniture')           return ['shops', 'furniture'];
        if ($s === 'baby_goods')          return ['shops', 'baby_shop'];

        // ── Services ───────────────────────────────────
        if ($a === 'laundry' || $s === 'laundry') return ['services', 'laundry'];
        if ($s === 'tailor' || $c === 'tailor')   return ['services', 'tailor'];
        if ($s === 'hairdresser')                  return ['services', 'barber'];
        if ($s === 'beauty')                       return ['services', 'salon'];
        if ($s === 'photo' || $s === 'photo_studio') return ['services', 'photographer'];

        // ── Office (lawyers, real estate, accountants, IT) ─────
        if ($o === 'lawyer' || $o === 'accountant' || $o === 'estate_agent'
            || $o === 'insurance' || $o === 'tax_advisor' || $o === 'notary'
            || $o === 'employment_agency' || $o === 'travel_agent'
            || $o === 'it' || $o === 'company' || $o === 'consulting'
            || $o === 'advertising_agency' || $o === 'financial' || $o === 'logistics'
            || $o === 'private') {
            return ['services', 'services_other'];
        }
        if ($o === 'government' || $o === 'governmental') return ['government', 'gov_other'];
        if ($o === 'religion')                            return ['religious', 'rel_mosque'];
        if ($o === 'newspaper' || $o === 'publisher')     return ['shops', 'bookshop'];

        // ── Craft (independent skilled tradesmen mapped here) ──
        if ($c === 'plumber')                              return ['craftsmen', 'plumber'];
        if ($c === 'electrician')                          return ['craftsmen', 'electrician'];
        if ($c === 'carpenter')                            return ['craftsmen', 'carpenter'];
        if ($c === 'painter')                              return ['craftsmen', 'painter'];
        if ($c === 'blacksmith' || $c === 'metal_construction') return ['craftsmen', 'blacksmith'];
        if ($c === 'glaziery')                             return ['craftsmen', 'glazier'];
        if ($c === 'locksmith')                            return ['craftsmen', 'locksmith'];
        if ($c === 'photographer')                         return ['services', 'photographer'];
        if ($c === 'shoemaker')                            return ['services', 'tailor'];
        if ($c === 'bakery')                               return ['food', 'bakery'];
        if ($c === 'caterer')                              return ['food', 'food_other'];
        if ($c === 'gardener')                             return ['craftsmen', 'craftsmen_other'];
        if ($c)                                            return ['craftsmen', 'craftsmen_other'];

        // ── Historic ──────────────────────────────────
        if ($hist === 'monument' || $hist === 'memorial' || $hist === 'archaeological_site'
            || $hist === 'castle' || $hist === 'fort' || $hist === 'ruins'
            || $hist === 'tower') {
            return ['tourist', 'tour_monument'];
        }

        // ── Sports / leisure additions ────────────────
        if ($l === 'stadium' || $l === 'sports_centre' || $l === 'pitch') return ['services', 'gym'];
        if ($l === 'playground' || $l === 'garden')                       return ['tourist', 'tour_park'];

        // ── Public transport / bus stops ───────────────
        if ($pt === 'station')                            return ['transport', 'trn_bus'];
        if ($hw === 'bus_stop')                            return ['transport', 'trn_microbus'];
        if ($rail === 'tram_stop')                         return ['transport', 'trn_other'];

        return null; // unknown / not interesting
    }

    public function handle(): int
    {
        $radius     = (int) $this->option('radius');
        $lat        = (float) $this->option('lat');
        $lng        = (float) $this->option('lng');
        $area       = trim((string) $this->option('area'));
        $adminLevel = (int) $this->option('admin-level');
        $dry        = (bool) $this->option('dry');
        $limit      = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        if ($area !== '') {
            $this->info("Querying Overpass API for area '{$area}' (admin_level={$adminLevel})...");
            $areaQ = addslashes($area);
            // Resolve the area by Arabic OR generic name (OSM has both name and name:ar).
            // We pull `node` AND `way` (some shops/buildings are mapped as polygons).
            // Wider tag net: amenity, shop, tourism, healthcare, leisure, office, craft,
            // historic, sport, railway/highway/public_transport stations.
            $query = <<<OQL
[out:json][timeout:240];
(
  area["admin_level"="{$adminLevel}"]["name:ar"="{$areaQ}"];
  area["admin_level"="{$adminLevel}"]["name"="{$areaQ}"];
)->.A;
(
  node["amenity"](area.A);
  way["amenity"](area.A);
  node["shop"](area.A);
  way["shop"](area.A);
  node["tourism"](area.A);
  way["tourism"](area.A);
  node["healthcare"](area.A);
  way["healthcare"](area.A);
  node["leisure"~"park|fitness_centre|sports_centre|stadium|pitch|playground|garden"](area.A);
  way["leisure"~"park|fitness_centre|sports_centre|stadium|pitch|playground|garden"](area.A);
  node["office"](area.A);
  way["office"](area.A);
  node["craft"](area.A);
  way["craft"](area.A);
  node["historic"](area.A);
  way["historic"](area.A);
  node["railway"~"station|halt|tram_stop"](area.A);
  node["public_transport"~"station|stop_position"](area.A);
  node["highway"="bus_stop"](area.A);
);
out tags center;
OQL;
        } else {
            $this->info("Querying Overpass API around [$lat, $lng] (radius {$radius}m)...");
            $query = <<<OQL
[out:json][timeout:120];
(
  node["amenity"](around:$radius,$lat,$lng);
  way["amenity"](around:$radius,$lat,$lng);
  node["shop"](around:$radius,$lat,$lng);
  way["shop"](around:$radius,$lat,$lng);
  node["tourism"](around:$radius,$lat,$lng);
  way["tourism"](around:$radius,$lat,$lng);
  node["healthcare"](around:$radius,$lat,$lng);
  way["healthcare"](around:$radius,$lat,$lng);
  node["leisure"~"park|fitness_centre|sports_centre|stadium|pitch|playground|garden"](around:$radius,$lat,$lng);
  way["leisure"~"park|fitness_centre|sports_centre|stadium|pitch|playground|garden"](around:$radius,$lat,$lng);
  node["office"](around:$radius,$lat,$lng);
  node["craft"](around:$radius,$lat,$lng);
  node["historic"](around:$radius,$lat,$lng);
  node["railway"~"station|halt|tram_stop"](around:$radius,$lat,$lng);
  node["public_transport"~"station|stop_position"](around:$radius,$lat,$lng);
  node["highway"="bus_stop"](around:$radius,$lat,$lng);
);
out tags center;
OQL;
        }

        try {
            $res = Http::timeout(240)
                ->connectTimeout(30)
                ->withHeaders(['User-Agent' => 'Banhawy/1.0 (osm-import)'])
                ->asForm()
                ->post('https://overpass-api.de/api/interpreter', ['data' => $query]);
        } catch (\Throwable $e) {
            $this->error('Overpass request failed: '.$e->getMessage());
            return self::FAILURE;
        }

        if (! $res->ok()) {
            $this->error('Overpass returned HTTP '.$res->status());
            return self::FAILURE;
        }

        $elements = $res->json('elements') ?? [];
        $this->info('Got '.count($elements).' OSM elements. Mapping…');

        $defaultZone = Zone::orderBy('sort')->first();
        if (! $defaultZone) {
            $this->error('No zones in DB — create at least one zone first.');
            return self::FAILURE;
        }

        $stats = ['imported' => 0, 'updated' => 0, 'skipped_no_map' => 0, 'skipped_no_name' => 0, 'skipped_existing' => 0];
        $bar = $this->output->createProgressBar(count($elements));
        $bar->start();

        foreach ($elements as $el) {
            $bar->advance();
            if ($limit !== null && ($stats['imported'] + $stats['updated']) >= $limit) break;

            $tags = $el['tags'] ?? [];
            $mapped = $this->mapTags($tags);
            if (! $mapped) { $stats['skipped_no_map']++; continue; }

            $name = $tags['name:ar'] ?? $tags['name'] ?? null;
            if (! $name || mb_strlen(trim($name)) < 2) {
                $stats['skipped_no_name']++; continue;
            }

            $osmId = 'osm:'.($el['type'] ?? 'node').':'.$el['id'];
            [$category, $subType] = $mapped;

            $coords = isset($el['lat'], $el['lon'])
                ? [$el['lat'], $el['lon']]
                : (isset($el['center']) ? [$el['center']['lat'], $el['center']['lon']] : null);
            if (! $coords) { $stats['skipped_no_map']++; continue; }

            // Address from tags
            $addrParts = array_filter([
                $tags['addr:street']   ?? null,
                $tags['addr:district'] ?? null,
                $tags['addr:city']     ?? null,
            ]);
            $address = $addrParts ? mb_substr(implode(' · ', $addrParts), 0, 200) : null;

            $phone = $tags['phone'] ?? $tags['contact:phone'] ?? null;
            $phone = $phone ? preg_replace('/\D/', '', $phone) : null;
            $phone = ($phone && preg_match('/^01[0125]\d{8}$/', substr($phone, -11)))
                ? substr($phone, -11)
                : null;

            $hours    = $tags['opening_hours'] ?? null;
            $hours    = $hours ? mb_substr($hours, 0, 100) : null;
            $is24h    = $hours === '24/7';

            $sm = Business::SUB_TYPES[$subType] ?? null;

            $payload = [
                'name'          => mb_substr(trim($name), 0, 120),
                'category'      => $category,
                'sub_type'      => $subType,
                'zone_id'       => $defaultZone->id,
                'owner_user_id' => null,
                'address'       => $address,
                'phone'         => $phone,
                'lat'           => $coords[0],
                'lng'           => $coords[1],
                'hours'         => $hours,
                'is_24h'        => $is24h,
                'is_verified'   => false,
                'is_active'     => true,
                'emoji'         => $sm['emoji'] ?? null,
            ];

            if ($dry) {
                $stats['imported']++;
                continue;
            }

            $existing = Business::where('external_id', $osmId)->first();
            if ($existing) {
                // Only update if owner hasn't claimed it (to avoid overwriting their edits)
                if ($existing->owner_user_id === null) {
                    $existing->update($payload);
                    $stats['updated']++;
                } else {
                    $stats['skipped_existing']++;
                }
                continue;
            }

            Business::create(array_merge($payload, ['external_id' => $osmId]));
            $stats['imported']++;
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['metric', 'count'], [
            ['imported (new)',   $stats['imported']],
            ['updated',          $stats['updated']],
            ['skipped: no name', $stats['skipped_no_name']],
            ['skipped: unknown type / no coords', $stats['skipped_no_map']],
            ['skipped: owned by user', $stats['skipped_existing']],
        ]);

        // Bust map cache so /map shows the new entries immediately
        if (! $dry) {
            \Illuminate\Support\Facades\Cache::forget('map-data:v4:all');
            foreach (array_keys(Business::CATEGORIES) as $cat) {
                \Illuminate\Support\Facades\Cache::forget('map-data:v4:'.$cat);
            }
        }

        return self::SUCCESS;
    }
}
