<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Surfaces suspicious data in the businesses table. Never deletes rows —
 * generates a report you act on manually (or feed into an admin queue).
 *
 *   php artisan banhawy:audit
 *   php artisan banhawy:audit --json=storage/audit.json
 *   php artisan banhawy:audit --csv=storage/audit.csv
 */
class AuditBusinessData extends Command
{
    protected $signature = 'banhawy:audit
        {--json= : Write findings to this JSON path}
        {--csv= : Write findings to this CSV path}
        {--limit=20 : Max rows printed per check}';

    protected $description = 'Audit business data quality — duplicates, missing fields, suspicious values.';

    /**
     * Heuristics for placeholder-looking phone numbers.
     * 01000000000 / 01111111111 / etc. are obvious placeholders.
     */
    private const PLACEHOLDER_PATTERNS = [
        '/^01[0125]0{8}$/',   // 01000000000
        '/^(\d)\1{10}$/',     // 11111111111
        '/^01[0125]1234567\d$/',
        '/^01[0125]0123456\d$/',
    ];

    /**
     * Categories where missing-phone / missing-image / missing-hours are not
     * really bugs. Transport stops, religious landmarks, tourist places and
     * emergency services often don't have any of those.
     */
    private const NO_PHONE_OK = ['transport', 'religious', 'tourist'];
    private const NO_IMAGE_OK = ['transport', 'religious', 'emergency', 'government'];
    private const NO_HOURS_OK = ['transport', 'religious', 'tourist', 'emergency', 'government', 'banks'];

    /**
     * Token → zone-slug hints for the geo-mismatch check. If the address
     * contains the key, the row's zone_id probably should be the slug.
     */
    private const ADDRESS_ZONE_HINTS = [
        // Longer / more specific tokens FIRST so they match before short
        // substrings collide (e.g. "محافظة القليوبية" must NOT be misread
        // as Qalyub city — we leave "القليوب" out entirely for that reason).
        'شبرا الخيمة'   => 'shubra-elkheima',
        'شبين القناطر'  => 'qalyubia-other',  // doesn't have its own zone yet
        'كفر شكر'        => 'kafr-shoukr',
        'الخانكة'        => 'el-khanka',
        'العبور'         => 'el-obour',
        'طوخ'            => 'toukh',
        'قها'            => 'qaha',
    ];

    /** Address values that mean "this business is internet-only — no physical address". */
    private const ONLINE_ADDRESS_HINTS = ['اونلاين', 'أونلاين', 'online', 'توصيل', 'دليفري'];

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $findings = [];

        $this->info('Running Banhawy data-quality audit…');
        $this->newLine();

        // ─── 1. Duplicate phone numbers across unrelated businesses ──
        $dupePhones = DB::table('businesses')
            ->select('phone', DB::raw('COUNT(*) as c'))
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->groupBy('phone')
            ->having('c', '>', 1)
            ->orderByDesc('c')
            ->limit(50)
            ->get();
        $findings['duplicate_phones'] = $dupePhones->map(fn ($r) => [
            'phone' => $r->phone, 'count' => (int) $r->c,
        ])->all();

        // ─── 2. Placeholder-looking phone numbers ────────────────────
        $placeholders = [];
        Business::query()
            ->select('id', 'name', 'phone', 'whatsapp', 'hotline')
            ->whereNotNull('phone')
            ->chunkById(500, function ($rows) use (&$placeholders) {
                foreach ($rows as $b) {
                    $clean = preg_replace('/\D/', '', $b->phone ?: '');
                    foreach (self::PLACEHOLDER_PATTERNS as $p) {
                        if (preg_match($p, $clean)) {
                            $placeholders[] = ['id' => $b->id, 'name' => $b->name, 'phone' => $b->phone];
                            break;
                        }
                    }
                }
            });
        $findings['placeholder_phones'] = $placeholders;

        // ─── 3. Missing core fields ──────────────────────────────────
        // Skip categories where a missing phone is normal (microbus stops,
        // mosques, parks — these aren't bugs).
        $findings['missing_phone'] = Business::query()
            ->where('is_active', true)
            ->whereNotIn('category', self::NO_PHONE_OK)
            ->where(function ($q) { $q->whereNull('phone')->orWhere('phone', ''); })
            ->where(function ($q) { $q->whereNull('whatsapp')->orWhere('whatsapp', ''); })
            ->where(function ($q) { $q->whereNull('hotline')->orWhere('hotline', ''); })
            ->select('id', 'name', 'category')
            ->limit($limit * 5)
            ->get()->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'category' => $b->category])->all();

        $findings['missing_zone'] = Business::query()
            ->where('is_active', true)
            ->whereNull('zone_id')
            ->select('id', 'name')
            ->limit($limit * 5)
            ->get()->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])->all();

        $findings['missing_category'] = Business::query()
            ->where('is_active', true)
            ->where(function ($q) { $q->whereNull('category')->orWhere('category', ''); })
            ->select('id', 'name')
            ->limit($limit * 5)
            ->get()->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])->all();

        // Skip the categories where having no image is OK (microbus stops, gov offices, etc.)
        $findings['missing_image'] = Business::query()
            ->where('is_active', true)
            ->whereNotIn('category', self::NO_IMAGE_OK)
            ->where(function ($q) { $q->whereNull('photo_url')->orWhere('photo_url', ''); })
            ->whereDoesntHave('photos')
            ->select('id', 'name', 'category')
            ->limit($limit * 5)
            ->get()->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'category' => $b->category])->all();

        // Missing coords — but only count it as a bug when the business is NOT
        // an online-only / delivery operation that has no physical address.
        $findings['missing_coords'] = Business::query()
            ->where('is_active', true)
            ->where(function ($q) { $q->whereNull('lat')->orWhereNull('lng'); })
            ->where(function ($q) {
                foreach (self::ONLINE_ADDRESS_HINTS as $h) {
                    $q->where('address', 'not like', "%{$h}%");
                }
                $q->orWhereNull('address')->orWhere('address', '');
            })
            ->select('id', 'name', 'address')
            ->limit($limit * 5)
            ->get()->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'address' => $b->address])->all();

        // ─── 4. Open-but-no-schedule (would render "open now" by accident) ──
        // Skip categories where having no schedule is fine (transport stops etc.)
        $findings['no_schedule_but_active'] = Business::query()
            ->where('is_active', true)
            ->whereNotIn('category', self::NO_HOURS_OK)
            ->where('is_24h', false)
            ->whereNull('hours_schedule')
            ->whereNull('hours')
            ->select('id', 'name', 'category')
            ->limit($limit * 5)
            ->get()->map(fn ($b) => ['id' => $b->id, 'name' => $b->name, 'category' => $b->category])->all();

        // ─── 5. Geo mismatch: zoned as Banha but address mentions other city ──
        // Now includes a `suggested_zone` hint so an admin can bulk-correct.
        $mismatch = [];
        $banhaZoneId = DB::table('zones')->where('slug', 'banha')->value('id');
        if ($banhaZoneId) {
            $zoneIdBySlug = DB::table('zones')->pluck('id', 'slug')->all();
            $q = Business::query()
                ->where('is_active', true)
                ->where('zone_id', $banhaZoneId)
                ->whereNotNull('address')
                ->where(function ($w) {
                    foreach (array_keys(self::ADDRESS_ZONE_HINTS) as $t) {
                        $w->orWhere('address', 'like', "%{$t}%");
                    }
                })
                ->select('id', 'name', 'address')
                ->limit($limit * 5)
                ->get();
            foreach ($q as $b) {
                $hintSlug = null;
                foreach (self::ADDRESS_ZONE_HINTS as $token => $slug) {
                    if (mb_strpos($b->address, $token) !== false) { $hintSlug = $slug; break; }
                }
                $mismatch[] = [
                    'id' => $b->id,
                    'name' => $b->name,
                    'suggested_zone' => $hintSlug,
                    'address' => mb_strlen($b->address) > 80 ? mb_substr($b->address, 0, 80).'…' : $b->address,
                ];
            }
        }
        $findings['banha_label_outside_city'] = $mismatch;

        // ─── Render to console ───────────────────────────────────────
        $this->renderSummary($findings, $limit);

        // ─── Optional file exports ───────────────────────────────────
        if ($json = $this->option('json')) {
            file_put_contents($json, json_encode($findings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->info("JSON report → {$json}");
        }
        if ($csv = $this->option('csv')) {
            $this->writeCsv($csv, $findings);
            $this->info("CSV report → {$csv}");
        }

        return self::SUCCESS;
    }

    private function renderSummary(array $findings, int $limit): void
    {
        $headers = [
            'duplicate_phones'           => '📞 Duplicate phones (one number, many businesses)',
            'placeholder_phones'         => '⚠️  Placeholder-looking phones',
            'missing_phone'              => '☎️  No phone + no whatsapp + no hotline',
            'missing_zone'               => '📍 Missing zone',
            'missing_category'           => '🏷  Missing category',
            'missing_image'              => '🖼  Missing image / photos',
            'missing_coords'             => '🗺  Missing coordinates',
            'no_schedule_but_active'     => '🕐 Active but no working-hours data',
            'banha_label_outside_city'   => '🚧 Zoned as Banha but address mentions another city',
        ];
        foreach ($headers as $key => $title) {
            $rows = $findings[$key] ?? [];
            $this->line('');
            $this->line("<options=bold>{$title}</> — ".count($rows));
            if (empty($rows)) {
                $this->line('  <fg=gray>(none found)</>');
                continue;
            }
            foreach (array_slice($rows, 0, $limit) as $r) {
                $this->line('  • '.collect($r)->map(fn ($v, $k) => "{$k}={$v}")->implode(' · '));
            }
            if (count($rows) > $limit) {
                $this->line('  <fg=gray>… +'.(count($rows) - $limit).' more</>');
            }
        }
    }

    private function writeCsv(string $path, array $findings): void
    {
        $fh = fopen($path, 'w');
        fputcsv($fh, ['check', 'data']);
        foreach ($findings as $check => $rows) {
            foreach ($rows as $r) {
                fputcsv($fh, [$check, json_encode($r, JSON_UNESCAPED_UNICODE)]);
            }
        }
        fclose($fh);
    }
}
