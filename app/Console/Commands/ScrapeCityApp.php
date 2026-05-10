<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Zone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Scrape public business listings from city-app.org's public sitemap and
 * upsert them into our `businesses` table as unowned entries that real
 * owners can later claim. Idempotent via `external_id = cityapp:{slug}`.
 *
 * Only pulls FACTS (name / phone / address / coords) — not photos,
 * descriptions, or menus, since those are copyrightable.
 */
class ScrapeCityApp extends Command
{
    protected $signature = 'scrape:cityapp
                            {--city=بنها : Title-fragment to filter by (Banha by default)}
                            {--limit=0 : Hard cap; 0 = no limit}
                            {--dry-run : Preview without writing to DB}
                            {--sleep=300 : Milliseconds between requests}
                            {--skip-existing : Skip slugs already imported (good for cron chunks)}
                            {--no-images : Skip image downloads (faster, smaller storage)}';

    protected $description = 'Scrape public business data from city-app.org and upsert into our directory.';

    private const SITEMAP   = 'https://city-app.org/sitemap.xml';
    private const BASE      = 'https://city-app.org/';
    // city-app's house numbers — they appear as a fallback when real branch data isn't filled in
    private const SKIP_NUMS = ['01288856886', '01205770368'];
    // Fallback coords city-app uses when a branch has no real location set
    private const SKIP_COORDS = [
        ['30.466919608421', '31.189944140292'],
        ['30.466919608420', '31.189944140292'], // float precision tolerance
    ];
    private const UA        = 'Mozilla/5.0 (banhacity-importer; contact via banhawy.app)';

    // Banha bounding box — drop coords outside this so we don't import wrong-city pins
    private const BBOX = ['lat_min' => 30.30, 'lat_max' => 30.65, 'lng_min' => 31.00, 'lng_max' => 31.40];

    public function handle(): int
    {
        $cityFragment = (string) $this->option('city');
        $limit        = (int) $this->option('limit');
        $dry          = (bool) $this->option('dry-run');
        $sleepMs      = max(0, (int) $this->option('sleep'));
        $skipExisting = (bool) $this->option('skip-existing');
        $skipImages   = (bool) $this->option('no-images');

        $defaultZone = Zone::where('slug', 'banha-center')->first()
                      ?? Zone::orderBy('sort')->first();
        if (! $defaultZone) {
            $this->error('No zones in DB. Run ZoneSeeder first.');
            return self::FAILURE;
        }

        $this->info("Fetching sitemap…");
        $slugs = $this->fetchSlugs();
        $this->info(count($slugs).' candidate slugs.');

        $stats = ['scanned' => 0, 'skipped_city' => 0, 'skipped_no_data' => 0, 'skipped_existing' => 0, 'created' => 0, 'updated' => 0];

        // Preload the set of already-imported slugs once when --skip-existing is on
        $existing = [];
        if ($skipExisting) {
            $existing = Business::where('external_id', 'like', 'cityapp:%')
                ->pluck('external_id')
                ->map(fn ($e) => substr($e, strlen('cityapp:')))
                ->flip()
                ->all();
        }

        foreach ($slugs as $slug) {
            if ($limit > 0 && $stats['scanned'] >= $limit) break;
            $stats['scanned']++;

            // Cron-friendly: short-circuit before doing the HTTP fetch
            if ($skipExisting && isset($existing[$slug])) {
                $stats['skipped_existing']++;
                continue;
            }

            $data = $this->scrapeBusiness($slug);
            if (! $data) { $stats['skipped_no_data']++; continue; }

            // City filter — title contains the city-fragment (e.g. "بنها")
            if ($cityFragment !== '' && mb_stripos($data['_title'] ?? '', $cityFragment) === false) {
                $stats['skipped_city']++;
                if ($sleepMs) usleep($sleepMs * 1000);
                continue;
            }

            // Download cover image to local storage (so we don't hot-link)
            $photoUrl = null;
            if (! $skipImages && ! $dry && ! empty($data['image_url'])) {
                $photoUrl = $this->downloadImage($slug, $data['image_url']);
            }

            $payload = [
                'name'          => $data['name'],
                'category'      => 'food',
                'sub_type'      => 'restaurant',
                'zone_id'       => $defaultZone->id,
                'lat'           => $data['lat'],
                'lng'           => $data['lng'],
                'address'       => $data['address'],
                'phone'         => $data['phone'],
                'whatsapp'      => $data['whatsapp'],
                'hotline'       => $data['hotline'],
                'photo_url'     => $photoUrl,
                'is_active'     => true,
                'is_verified'   => false,
                'owner_user_id' => null,         // unclaimed; real owner can claim later
                'emoji'         => '🍽️',
            ];

            $this->line(sprintf("  • %s | phone=%s | hotline=%s | img=%s | (%s, %s)",
                $data['name'],
                $data['phone'] ?? '—',
                $data['hotline'] ?? '—',
                $photoUrl ? '✓' : '—',
                $data['lat'] ?? '?',
                $data['lng'] ?? '?',
            ));

            if (! $dry) {
                $external = 'cityapp:'.$slug;
                $b = Business::firstWhere('external_id', $external);
                if ($b) {
                    // Don't clobber data the owner has filled in themselves after claiming
                    if ($b->owner_user_id) {
                        // skip silently; respect claimed listings
                    } else {
                        $b->update(array_filter($payload, fn ($v) => $v !== null && $v !== ''));
                        $stats['updated']++;
                    }
                } else {
                    Business::create($payload + ['external_id' => $external]);
                    $stats['created']++;
                }
            }

            if ($sleepMs) usleep($sleepMs * 1000);
        }

        // Bust map cache so new pins show
        \Illuminate\Support\Facades\Cache::forget('map-data:v5:all');
        \Illuminate\Support\Facades\Cache::forget('map-data:v5:food');

        $this->info('');
        $this->info(sprintf('Done. scanned=%d  created=%d  updated=%d  skipped(city)=%d  skipped(no_data)=%d',
            $stats['scanned'], $stats['created'], $stats['updated'],
            $stats['skipped_city'], $stats['skipped_no_data']
        ));

        return self::SUCCESS;
    }

    /** GET the sitemap and return business slugs (filters out static pages). */
    private function fetchSlugs(): array
    {
        $xml = Http::withHeaders(['User-Agent' => self::UA])->timeout(20)->get(self::SITEMAP)->body();
        if (! $xml) return [];

        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $m);
        $slugs = [];
        foreach ($m[1] as $url) {
            $path = ltrim(parse_url($url, PHP_URL_PATH) ?? '', '/');
            if ($path === '' || str_contains($path, '/')) continue; // skip nested + root
            // Skip known static pages
            if (in_array(strtolower($path), ['download','signup','login','about','contact','register','sitemap.xml'], true)) continue;
            $slugs[] = $path;
        }
        return array_values(array_unique($slugs));
    }

    /** Fetch + parse a single business page. Returns null when nothing usable found. */
    private function scrapeBusiness(string $slug): ?array
    {
        try {
            $resp = Http::withHeaders(['User-Agent' => self::UA])->timeout(15)->get(self::BASE.$slug);
        } catch (\Throwable) {
            return null;
        }
        if (! $resp->ok()) return null;
        $html = $resp->body();

        // Title format: "{NAME-AR} - {slug} فى {city} منيو - عنوان - ارقام"
        $title = '';
        if (preg_match('#<title>([^<]+)</title>#u', $html, $m)) $title = trim($m[1]);
        $name = $title;
        if ($name && preg_match('#^(.+?)\s*-\s*'.preg_quote($slug, '#').'#u', $title, $m)) {
            $name = trim($m[1]);
        }
        if ($name === '' || mb_strlen($name) < 2) return null;

        // Address — first non-empty data-address attribute (the first one is sometimes a marketing line)
        $address = null;
        preg_match_all('#data-address="([^"]+)"#u', $html, $aMatches);
        foreach ($aMatches[1] ?? [] as $candidate) {
            $candidate = trim(html_entity_decode($candidate, ENT_QUOTES | ENT_HTML5));
            // Skip obvious marketing copy
            if (mb_stripos($candidate, 'كاش باك') !== false) continue;
            if (mb_strlen($candidate) < 6) continue;
            $address = $candidate;
            break;
        }

        // Coords — first lat/lng inside the Banha bounding box, skipping city-app's fallback point
        $lat = $lng = null;
        if (preg_match_all('#daddr=([0-9.\-]+),\s*([0-9.\-]+)#', $html, $cMatches)) {
            foreach ($cMatches[1] as $i => $latStr) {
                $lngStr = $cMatches[2][$i] ?? '';

                // Drop city-app's fallback "no real branch set" coords
                $isFallback = false;
                foreach (self::SKIP_COORDS as [$sl, $sg]) {
                    if (str_starts_with($latStr, substr($sl, 0, 10)) && str_starts_with($lngStr, substr($sg, 0, 10))) {
                        $isFallback = true;
                        break;
                    }
                }
                if ($isFallback) continue;

                $tryLat = (float) $latStr;
                $tryLng = (float) $lngStr;
                if ($tryLat >= self::BBOX['lat_min'] && $tryLat <= self::BBOX['lat_max']
                 && $tryLng >= self::BBOX['lng_min'] && $tryLng <= self::BBOX['lng_max']) {
                    $lat = $tryLat;
                    $lng = $tryLng;
                    break;
                }
            }
        }

        // Image — prefer Restaurant/CoverImage (the full-size cover), then any AppBranch cover,
        // then the Restaurant/Logo as a last resort. We download it later to our public disk.
        $imageUrl = null;
        if (preg_match('#https?://[^"\'\\\\)]+/Uploud/Restaurant/CoverImage/[^"\'\\\\)]+#i', $html, $im)) {
            $imageUrl = $im[0];
        } elseif (preg_match('#https?://[^"\'\\\\)]+/Uploud/AppBranch/[^"\'\\\\)]+#i', $html, $im)) {
            $imageUrl = $im[0];
        } elseif (preg_match('#https?://[^"\'\\\\)]+/Uploud/Restaurant/Logo/[^"\'\\\\)]+#i', $html, $im)) {
            $imageUrl = $im[0];
        }
        if ($imageUrl) $imageUrl = preg_replace('#(?<!:)//+#', '/', $imageUrl); // collapse stray double-slashes

        // Phones — all data-phones values, comma-split, dedupe, drop city-app's own number
        $phones = [];
        preg_match_all('#data-phones="([^"]+)"#u', $html, $pMatches);
        foreach ($pMatches[1] ?? [] as $list) {
            foreach (explode(',', $list) as $raw) {
                $raw = trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5));
                if ($raw === '' || in_array($raw, self::SKIP_NUMS, true)) continue;
                $phones[] = $raw;
            }
        }
        $phones = array_values(array_unique($phones));

        // Classify: 11-digit `01x...` → mobile/whatsapp, others → hotline/landline
        $phone = $whatsapp = $hotline = null;
        foreach ($phones as $p) {
            $isMobile = (bool) preg_match('/^01[0125]\d{8}$/', $p);
            if ($isMobile && $phone === null) {
                $phone = $p;
            } elseif ($isMobile && $whatsapp === null) {
                $whatsapp = $p;
            } elseif (! $isMobile && $hotline === null) {
                $hotline = $p;
            }
        }

        // Need at least a name + (coords or phone) to be useful
        if ($lat === null && $phone === null && $hotline === null) return null;

        return [
            '_title'    => $title,
            'name'      => $name,
            'address'   => $address,
            'lat'       => $lat,
            'lng'       => $lng,
            'phone'     => $phone,
            'whatsapp'  => $whatsapp,
            'hotline'   => $hotline,
            'image_url' => $imageUrl,
        ];
    }

    /**
     * Download a remote image into storage/app/public/businesses/cityapp/{slug}.{ext}
     * and return the public path. Skip & return existing path if already downloaded.
     */
    private function downloadImage(string $slug, string $url): ?string
    {
        $extMatch = preg_match('/\.(jpe?g|png|webp)(\?|$)/i', $url, $m);
        $ext      = $extMatch ? strtolower($m[1]) : 'jpg';
        if ($ext === 'jpeg') $ext = 'jpg';

        $relPath  = 'businesses/cityapp/'.$slug.'.'.$ext;
        $disk     = \Illuminate\Support\Facades\Storage::disk('public');

        if ($disk->exists($relPath)) {
            return '/storage/'.$relPath;
        }

        try {
            $resp = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => self::UA,
                'Referer'    => self::BASE,
            ])->timeout(20)->get($url);
        } catch (\Throwable) {
            return null;
        }
        if (! $resp->ok()) return null;

        $body = $resp->body();
        // Quick sanity check — at least a few KB and a known image header
        if (strlen($body) < 1024) return null;
        $magic = substr($body, 0, 4);
        $isImage = str_starts_with($magic, "\xFF\xD8")      // JPEG
                || str_starts_with($magic, "\x89PNG")        // PNG
                || str_starts_with($magic, 'RIFF');          // WEBP container
        if (! $isImage) return null;

        $disk->put($relPath, $body);
        return '/storage/'.$relPath;
    }
}
