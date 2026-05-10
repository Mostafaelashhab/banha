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

    /**
     * Keyword → [category, sub_type] classifier. The first match wins, so the
     * ORDER MATTERS (more specific patterns must come before generic ones).
     * Matches against the meta description tags + business name.
     */
    private const CLASSIFY = [
        // ── Services (specific) ─────────────────────────────
        ['kw' => ['كار شاين','غسيل عربيات','غسيل سيارات','car wash','car-wash'],            'cat' => 'services', 'sub' => 'car_wash'],
        ['kw' => ['كوافير','صالون تجميل','تجميل سيدات'],                                    'cat' => 'services', 'sub' => 'salon'],
        ['kw' => ['حلاق','حلاقة'],                                                            'cat' => 'services', 'sub' => 'barber'],
        ['kw' => ['مغسلة','مغسله','laundry','دراي كلين','كاوي'],                              'cat' => 'services', 'sub' => 'laundry'],
        ['kw' => ['خياط','ترزي','tailor'],                                                    'cat' => 'services', 'sub' => 'tailor'],
        ['kw' => ['جيم','نادي رياضي','رياضة','fitness'],                                      'cat' => 'services', 'sub' => 'gym'],
        ['kw' => ['مطبعة','مطبعه','طباعة'],                                                   'cat' => 'services', 'sub' => 'printing'],
        ['kw' => ['تأجير عربيات','rent a car'],                                                'cat' => 'services', 'sub' => 'car_rental'],

        // ── Medical ────────────────────────────────────────
        ['kw' => ['صيدلية','صيدليه','pharmacy'],                                              'cat' => 'medical',  'sub' => 'pharmacy'],
        ['kw' => ['عيادة أسنان','عياده اسنان','طبيب أسنان','dentist'],                        'cat' => 'medical',  'sub' => 'dentist'],
        ['kw' => ['أطفال د','طبيب أطفال','دكتور أطفال','pediatric'],                          'cat' => 'medical',  'sub' => 'pediatrician'],
        ['kw' => ['بيطري','veterinary','عيادة بيطرية'],                                       'cat' => 'medical',  'sub' => 'vet'],
        ['kw' => ['معمل تحاليل','تحاليل طبية','معمل '],                                       'cat' => 'medical',  'sub' => 'lab'],
        ['kw' => ['عيادة','عياده','clinic'],                                                  'cat' => 'medical',  'sub' => 'doctor'],

        // ── Shops ──────────────────────────────────────────
        ['kw' => ['سوبر ماركت','سوبرماركت','هايبر','supermarket'],                            'cat' => 'shops',    'sub' => 'supermarket'],
        ['kw' => ['بقالة','بقاله','بقال'],                                                    'cat' => 'shops',    'sub' => 'grocery'],
        ['kw' => ['جزار','جزاره','لحوم'],                                                     'cat' => 'shops',    'sub' => 'butcher'],
        ['kw' => ['خضار','خضرة','فاكهة'],                                                     'cat' => 'shops',    'sub' => 'fruit_veg'],
        ['kw' => ['ملابس','بوتيك','clothing','أزياء'],                                        'cat' => 'shops',    'sub' => 'clothing'],
        ['kw' => ['مكتبة','مكتبه','قرطاسية','كتب'],                                           'cat' => 'shops',    'sub' => 'bookshop'],
        ['kw' => ['موبايلات','موبيليات','telephone','phone shop'],                            'cat' => 'shops',    'sub' => 'mobile_shop'],
        ['kw' => ['إلكترونيات','الكترونيات','electronics'],                                   'cat' => 'shops',    'sub' => 'electronics'],
        ['kw' => ['عطار','عطارة','عطاره'],                                                    'cat' => 'shops',    'sub' => 'shops_other'],
        ['kw' => ['أثاث','اثاث','furniture'],                                                 'cat' => 'shops',    'sub' => 'furniture'],
        ['kw' => ['ذهب','مجوهرات','محل دهب'],                                                 'cat' => 'shops',    'sub' => 'gold_shop'],
        ['kw' => ['لعب','toys'],                                                              'cat' => 'shops',    'sub' => 'toys'],
        ['kw' => ['محل أطفال','baby shop','bebe'],                                            'cat' => 'shops',    'sub' => 'baby_shop'],

        // ── Food: specific sub-types ───────────────────────
        ['kw' => ['حلواني','حلويات','كنافة','كنافه','بسبوسة','كحك','قطايف','حلاوة','sweets'], 'cat' => 'food',     'sub' => 'sweets'],
        ['kw' => ['مخبز','مخبوزات','فطير مشلتت','عيش','bakery','pâtisserie','patisserie'],    'cat' => 'food',     'sub' => 'bakery'],
        ['kw' => ['كافيه','كافي','قهوة','مقهى','cafe','coffee'],                              'cat' => 'food',     'sub' => 'cafe'],
        ['kw' => ['فطار','فاست فود','فول و طعميه','fast food','sandwich'],                    'cat' => 'food',     'sub' => 'fast_food'],
        ['kw' => ['عصاير','عصير','مشروبات','juice','smoothie'],                               'cat' => 'food',     'sub' => 'juice'],
        // Default food: anything not matched above but mentions restaurant signals
        ['kw' => ['مطعم','مشويات','وجبات','ساندوتشات','برجر','بيتزا','مكرونات','اسماك','أكل'], 'cat' => 'food',     'sub' => 'restaurant'],
    ];

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

            // Classify from meta-description tags + name (city-app puts category-ish keywords
            // in the meta description). Default to food/restaurant since that's the bulk of city-app.
            [$category, $subType, $emoji] = $this->classify(
                ($data['meta_tags'] ?? '').' '.$data['name']
            );

            // Stash Facebook in the `extra` JSON column so we don't lose it
            $extra = [];
            if (! empty($data['facebook']))   $extra['facebook']   = $data['facebook'];
            if (! empty($data['meta_tags']))  $extra['tags']       = trim($data['meta_tags']);

            $payload = [
                'name'          => $data['name'],
                'category'      => $category,
                'sub_type'      => $subType,
                'zone_id'       => $defaultZone->id,
                'lat'           => $data['lat'],
                'lng'           => $data['lng'],
                'address'       => $data['address'],
                'phone'         => $data['phone'],
                'whatsapp'      => $data['whatsapp'],
                'hotline'       => $data['hotline'],
                'hours'         => $data['hours'] ?? null,
                'photo_url'     => $photoUrl,
                'extra'         => $extra ?: null,
                'is_active'     => true,
                'is_verified'   => false,
                'owner_user_id' => null,         // unclaimed; real owner can claim later
                'emoji'         => $emoji,
            ];

            $galleryCount = is_array($data['gallery_urls'] ?? null) ? count($data['gallery_urls']) : 0;
            $this->line(sprintf("  • %s | %s/%s | phone=%s | img=%s | gallery=%d | (%s, %s)",
                $data['name'],
                $category,
                $subType,
                $data['phone'] ?? '—',
                $photoUrl ? '✓' : '—',
                $galleryCount,
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
                    $b = Business::create($payload + ['external_id' => $external]);
                    $stats['created']++;
                }

                // Gallery photos → business_photos table (only when we're allowed to fetch images)
                if (! $skipImages && $b && ! $b->owner_user_id && ! empty($data['gallery_urls'])) {
                    $this->saveGallery($b, $slug, $data['gallery_urls']);
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

        // Cover image — prefer Restaurant/CoverImage (full-size cover), then any AppBranch
        // cover, then the Restaurant/Logo as a last resort.
        $imageUrl = null;
        if (preg_match('#https?://[^"\'\\\\)]+/Uploud/Restaurant/CoverImage/[^"\'\\\\)]+#i', $html, $im)) {
            $imageUrl = $im[0];
        } elseif (preg_match('#https?://[^"\'\\\\)]+/Uploud/AppBranch/[^"\'\\\\)]+#i', $html, $im)) {
            $imageUrl = $im[0];
        } elseif (preg_match('#https?://[^"\'\\\\)]+/Uploud/Restaurant/Logo/[^"\'\\\\)]+#i', $html, $im)) {
            $imageUrl = $im[0];
        }
        if ($imageUrl) $imageUrl = preg_replace('#(?<!:)//+#', '/', $imageUrl);

        // Gallery — RestaurantGallery URLs (additional photos of the place, not the menu)
        $galleryUrls = [];
        if (preg_match_all('#https?://[^"\'\\\\)]+/Uploud/RestaurantGallery/[^"\'\\\\)]+\.(?:jpe?g|png|webp)#i', $html, $gm)) {
            foreach ($gm[0] as $gUrl) {
                $gUrl = preg_replace('#(?<!:)//+#', '/', $gUrl);
                if (! in_array($gUrl, $galleryUrls, true)) $galleryUrls[] = $gUrl;
            }
        }

        // Hours — city-app puts opening text in elements with class "hours-head" or similar.
        // Grab the first short, hour-shaped string after that class.
        $hours = null;
        if (preg_match('#class="hours-head[^"]*"[^>]*>\s*([^<]+?)\s*<#u', $html, $hm)) {
            $candidate = trim(html_entity_decode($hm[1], ENT_QUOTES | ENT_HTML5));
            if (preg_match('#\d{1,2}\s*[صم]#u', $candidate)) {
                $hours = mb_substr($candidate, 0, 100);
            }
        }

        // Facebook page link (skip the share-link variant)
        $facebook = null;
        if (preg_match('#href="(https://www\.facebook\.com/[a-zA-Z0-9._\-]+)/?"#', $html, $fm)) {
            if (! str_contains($fm[1], 'sharer.php')) $facebook = $fm[1];
        }

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

        // Meta description tags — city-app puts category keywords BEFORE the business name
        // (e.g. "ساندوتشات,مشويات,حلويات {اسم} - {slug} فى بنها…"). Only the part before the
        // slug is reliable category info; after it is address/marketing copy.
        $metaTags = '';
        if (preg_match('#<meta\s+name="description"\s+content="([^"]+)"#u', $html, $mm)) {
            $full = html_entity_decode($mm[1], ENT_QUOTES | ENT_HTML5);
            $cut  = mb_stripos($full, $slug);
            $metaTags = $cut !== false ? mb_substr($full, 0, $cut) : $full;
        }

        return [
            '_title'      => $title,
            'name'        => $name,
            'address'     => $address,
            'lat'         => $lat,
            'lng'         => $lng,
            'phone'       => $phone,
            'whatsapp'    => $whatsapp,
            'hotline'     => $hotline,
            'image_url'   => $imageUrl,
            'gallery_urls'=> $galleryUrls,
            'hours'       => $hours,
            'facebook'    => $facebook,
            'meta_tags'   => $metaTags,
        ];
    }

    /**
     * Pick a category + sub_type + emoji from a signals string (meta tags + name).
     *
     * Count hits per rule rather than first-match: city-app's meta description lists
     * MULTIPLE category tags per business (e.g. "ساندوتشات,مشويات,حلويات,وجبات") and
     * also includes address copy. First-match would mis-categorize a steakhouse as
     * "sweets" just because the meta mentioned dessert.
     *
     * Falls back to food/restaurant since city-app is predominantly a food directory.
     */
    private function classify(string $signals): array
    {
        $sig = mb_strtolower($signals);

        // Score each rule by total keyword hits + weight by name match (×3) since
        // the business NAME is the most reliable signal.
        $scores = [];
        foreach (self::CLASSIFY as $idx => $rule) {
            $score = 0;
            foreach ($rule['kw'] as $kw) {
                $kwLower = mb_strtolower($kw);
                // Count occurrences across the whole signals string
                $count = mb_substr_count($sig, $kwLower);
                if ($count > 0) $score += $count;
            }
            if ($score > 0) $scores[$idx] = $score;
        }

        if (! $scores) {
            return ['food', 'restaurant', '🍽️'];
        }

        // Highest score wins; tie-breaker = rule order (more specific rules come first in CLASSIFY)
        arsort($scores);
        $winnerIdx = array_key_first($scores);
        $rule = self::CLASSIFY[$winnerIdx];
        return [$rule['cat'], $rule['sub'], self::emojiFor($rule['sub'])];
    }

    private static function emojiFor(string $sub): string
    {
        return match ($sub) {
            'cafe'          => '☕',
            'sweets'        => '🍰',
            'bakery'        => '🥖',
            'fast_food'     => '🍔',
            'juice'         => '🧃',
            'restaurant'    => '🍽️',
            'food_other'    => '🍴',
            'pharmacy'      => '💊',
            'doctor'        => '🩺',
            'dentist'       => '🦷',
            'pediatrician'  => '👶',
            'vet'           => '🐾',
            'lab'           => '🧪',
            'supermarket'   => '🛒',
            'grocery'       => '🏪',
            'butcher'       => '🥩',
            'fruit_veg'     => '🥬',
            'clothing'      => '👗',
            'bookshop'      => '📚',
            'mobile_shop'   => '📱',
            'electronics'   => '🖥️',
            'furniture'     => '🛋️',
            'gold_shop'     => '💍',
            'toys'          => '🧸',
            'baby_shop'     => '🍼',
            'shops_other'   => '🛍️',
            'car_wash'      => '🚿',
            'salon'         => '💇‍♀️',
            'barber'        => '💈',
            'laundry'       => '🧺',
            'tailor'        => '🪡',
            'gym'           => '🏋️',
            'printing'      => '🖨️',
            'car_rental'    => '🚗',
            default         => '🏷️',
        };
    }

    /**
     * Download gallery images for a business and link them via business_photos.
     * Idempotent: existing photo rows for this business get wiped first so we
     * don't duplicate on re-runs.
     */
    private function saveGallery(Business $business, string $slug, array $urls): void
    {
        // Clear stale photos for this business so we don't duplicate on re-scrape
        \App\Models\BusinessPhoto::where('business_id', $business->id)->delete();

        $sort = 0;
        foreach ($urls as $i => $url) {
            $extMatch = preg_match('/\.(jpe?g|png|webp)(\?|$)/i', $url, $m);
            $ext      = $extMatch ? strtolower($m[1]) : 'jpg';
            if ($ext === 'jpeg') $ext = 'jpg';
            $relPath  = 'businesses/cityapp/'.$slug.'-g'.($i + 1).'.'.$ext;
            $disk     = \Illuminate\Support\Facades\Storage::disk('public');

            if (! $disk->exists($relPath)) {
                try {
                    $resp = \Illuminate\Support\Facades\Http::withHeaders([
                        'User-Agent' => self::UA,
                        'Referer'    => self::BASE,
                    ])->timeout(15)->get($url);
                } catch (\Throwable) {
                    continue;
                }
                if (! $resp->ok()) continue;
                $body = $resp->body();
                if (strlen($body) < 1024) continue;
                $magic = substr($body, 0, 4);
                if (! (str_starts_with($magic, "\xFF\xD8") || str_starts_with($magic, "\x89PNG") || str_starts_with($magic, 'RIFF'))) continue;
                $disk->put($relPath, $body);
            }

            \App\Models\BusinessPhoto::create([
                'business_id' => $business->id,
                'url'         => '/storage/'.$relPath,
                'sort'        => $sort++,
            ]);
        }
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
