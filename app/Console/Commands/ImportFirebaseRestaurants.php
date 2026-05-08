<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Zone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportFirebaseRestaurants extends Command
{
    protected $signature = 'banha:import-firebase
                            {--url=https://banha-restaurants.firebaseio.com/restaurant.json : RTDB endpoint}
                            {--dry-run : Preview without inserting}
                            {--purge : Delete existing imported entries first}';

    protected $description = 'Import restaurants from the public Firebase RTDB into businesses table';

    /**
     * Map Firebase category strings to (category, sub_type) tuples in our model.
     */
    private const CATEGORY_MAP = [
        'كافيه'                  => ['food', 'cafe'],
        'مطعم و كافيه'          => ['food', 'cafe'],
        'مطعم وكافيه'           => ['food', 'cafe'],
        'بيتزا و فطائر'          => ['food', 'restaurant'],
        'بيتزا وفطائر'           => ['food', 'restaurant'],
        'كشري'                   => ['food', 'restaurant'],
        'مشويات'                 => ['food', 'restaurant'],
        'أكل اسيوي'              => ['food', 'restaurant'],
        'اكل اسيوي'              => ['food', 'restaurant'],
        'أكل بيتي اون لاين'      => ['food', 'restaurant'],
        'مأكولات بحرية'          => ['food', 'restaurant'],
        'ساندويتشات'             => ['food', 'fast_food'],
        'فول و طعميه'           => ['food', 'fast_food'],
        'فول وطعميه'             => ['food', 'fast_food'],
        'كريب'                   => ['food', 'fast_food'],
        'وجبات'                  => ['food', 'fast_food'],
        'عصائر و حلويات'         => ['food', 'juice'],
        'عصائر وحلويات'          => ['food', 'juice'],
        'مخابز و معجنات'         => ['food', 'bakery'],
        'مخابز ومعجنات'          => ['food', 'bakery'],
        'جزاره'                  => ['shops', 'butcher'],
        'سوبر ماركت'             => ['shops', 'supermarket'],
        'منطقة لعب اطفال'        => ['services', 'tutor'], // closest fallback
    ];

    public function handle(): int
    {
        $url = $this->option('url');
        $this->info("→ Fetching: {$url}");

        $resp = Http::timeout(60)->get($url);
        if (! $resp->successful()) {
            $this->error('Failed to fetch: HTTP '.$resp->status());
            return self::FAILURE;
        }

        $data = $resp->json() ?? [];
        if (empty($data)) {
            $this->error('Empty response.');
            return self::FAILURE;
        }

        $banhaZone = Zone::where('slug', 'banha')->first();
        if (! $banhaZone) {
            $this->error('Banha zone not found in DB. Seed zones first.');
            return self::FAILURE;
        }

        if ($this->option('purge')) {
            $count = Business::where('emoji', '🔥📦')->delete();
            $this->warn("Purged {$count} previously-imported entries.");
        }

        $stats = ['total' => 0, 'imported' => 0, 'skipped_spam' => 0, 'skipped_deleted' => 0, 'updated' => 0, 'errors' => 0];

        foreach ($data as $key => $entry) {
            $stats['total']++;

            if (! $this->isValid($key, $entry)) {
                $stats['skipped_spam']++;
                continue;
            }

            if (($entry['isDelete'] ?? null) === 'yes') {
                $stats['skipped_deleted']++;
                continue;
            }

            $row = $this->buildRow($key, $entry, $banhaZone->id);
            if (! $row) { $stats['errors']++; continue; }

            if ($this->option('dry-run')) {
                $this->line("  • {$row['name']} → {$row['sub_type']} ({$row['phone']})");
                $stats['imported']++;
                continue;
            }

            try {
                $existing = Business::where('name', $row['name'])->first();
                if ($existing) {
                    $existing->update($row);
                    $stats['updated']++;
                } else {
                    Business::create($row);
                    $stats['imported']++;
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
                $this->error("  ✗ {$row['name']}: ".$e->getMessage());
            }
        }

        $this->newLine();
        $this->table(['Metric', 'Count'], [
            ['Total fetched',      $stats['total']],
            ['Imported new',       $stats['imported']],
            ['Updated existing',   $stats['updated']],
            ['Skipped (spam/test)',$stats['skipped_spam']],
            ['Skipped (deleted)',  $stats['skipped_deleted']],
            ['Errors',             $stats['errors']],
        ]);

        return self::SUCCESS;
    }

    private function isValid(string $key, $entry): bool
    {
        if (! is_array($entry)) return false;

        // Firebase RTDB has been polluted by security scanners. Reject obvious noise.
        $blacklist = [
            'insecure', 'creep', 'nuclei', 'javascript:', '<script', '${', 'BytesKnight',
            'Bytes_Knight', 'context', 'graphql', 'option', 'poc', 'security_test', 'test', 'v1', 'v2', 'v3', 'v4',
            'sponsor', 'category', 'name',
        ];
        foreach ($blacklist as $bad) {
            if (stripos($key, $bad) !== false) return false;
        }

        // Reject entries that have { id: 'insecure-firebase-database' } and nothing else
        if (count($entry) === 1 && ($entry['id'] ?? null) === 'insecure-firebase-database') return false;

        // Must have a name OR be using the key as name + a category/branch/phone
        $hasContent = isset($entry['name']) || isset($entry['category']) || isset($entry['branch']) || isset($entry['phoneAdmin']);
        return $hasContent;
    }

    private function buildRow(string $key, array $entry, int $defaultZoneId): ?array
    {
        $name = $this->cleanText($entry['name'] ?? $key);
        if ($name === '' || mb_strlen($name) < 2) return null;

        // Pick first category & map
        $category = 'food';
        $subType  = 'restaurant';
        if (! empty($entry['category']) && is_array($entry['category'])) {
            foreach (array_keys($entry['category']) as $catKey) {
                $catKey = $this->cleanText($catKey);
                if (isset(self::CATEGORY_MAP[$catKey])) {
                    [$category, $subType] = self::CATEGORY_MAP[$catKey];
                    break;
                }
            }
        }

        // First branch: address + lat/lng + phones
        $address = null; $lat = null; $lng = null; $phone = null;
        if (! empty($entry['branch']) && is_array($entry['branch'])) {
            $firstBranchKey = array_key_first($entry['branch']);
            $branch         = $entry['branch'][$firstBranchKey];
            if (is_array($branch)) {
                $address = trim($firstBranchKey).' — '.($branch['details'] ?? '');
                $address = trim($address, " —");
                if (! empty($branch['map']['lat']) && is_numeric($branch['map']['lat'])) $lat = (float) $branch['map']['lat'];
                if (! empty($branch['map']['lng']) && is_numeric($branch['map']['lng'])) $lng = (float) $branch['map']['lng'];
                if (! empty($branch['phone']) && is_array($branch['phone'])) {
                    $phone = (string) array_key_first($branch['phone']);
                }
            }
        }
        $phone = $phone ?: ($entry['phoneAdmin'] ?? null);
        if ($phone) $phone = $this->cleanPhone($phone);

        // Hours
        $hours = null;
        if (! empty($entry['date']['from']) && ! empty($entry['date']['to'])) {
            $hours = "من {$entry['date']['from']} لـ {$entry['date']['to']}";
        }

        // Rating: compute from rate1..rate5 (Firebase 'total' field is unreliable)
        $rateAvg   = 0;
        $rateCount = 0;
        if (! empty($entry['rate']) && is_array($entry['rate'])) {
            $sum = 0; $count = 0;
            for ($i = 1; $i <= 5; $i++) {
                $n = (int) ($entry['rate']["rate{$i}"] ?? 0);
                $sum   += $i * $n;
                $count += $n;
            }
            $rateCount = $count;
            // Clamp avg to 0-9.9 (decimal(2,1))
            $rateAvg = $count > 0 ? min(9.9, round($sum / $count, 1)) : 0;
        }
        // Plus comments count
        $commentsCount = ! empty($entry['comment']) && is_array($entry['comment']) ? count($entry['comment']) : 0;
        $rateCount = max($rateCount, $commentsCount);

        $logo = ! empty($entry['logo']) && is_string($entry['logo']) && filter_var($entry['logo'], FILTER_VALIDATE_URL) ? $entry['logo'] : null;

        return [
            'name'          => mb_substr($name, 0, 120),
            'category'      => $category,
            'sub_type'      => $subType,
            'zone_id'       => $defaultZoneId,
            'owner_user_id' => null,
            'description'   => null,
            'phone'         => $phone,
            'whatsapp'      => null,
            'address'       => $address ? mb_substr($address, 0, 200) : null,
            'lat'           => $lat,
            'lng'           => $lng,
            'hours'         => $hours,
            'is_24h'        => false,
            'is_verified'   => false,
            'is_active'     => true,
            'rating_avg'    => $rateAvg,
            'ratings_count' => $rateCount,
            'emoji'         => '🔥📦',  // marker for imported entries (so we can purge if needed)
            'photo_url'     => $logo,
        ];
    }

    private function cleanText(string $s): string
    {
        $s = trim($s);
        // Strip control chars + suspicious script/template tokens
        $s = preg_replace('/[\x00-\x1F\x7F]/', '', $s);
        $s = preg_replace('/<[^>]+>/', '', $s);
        return $s;
    }

    private function cleanPhone(string $s): string
    {
        $s = preg_replace('/\D/', '', $s);
        return mb_substr($s, 0, 20);
    }
}
