<?php

namespace App\Console\Commands;

use App\Models\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Reassign businesses to the correct zone based on tokens in their address.
 *
 * Why this exists: `banha:reassign-zones` uses lat/lng which is great when
 * coordinates are right. But many imported rows have garbled coordinates and
 * an honest Arabic address — for those, the address tokens are the source
 * of truth.
 *
 *   php artisan banhawy:fix-zone-from-address --dry
 *   php artisan banhawy:fix-zone-from-address
 *   php artisan banhawy:fix-zone-from-address --only-banha
 */
class FixZoneFromAddress extends Command
{
    protected $signature = 'banhawy:fix-zone-from-address
        {--dry : Preview the moves without writing}
        {--only-banha : Only move rows currently assigned to the Banha zone}';

    protected $description = 'Reassign businesses to the correct zone using address tokens (Shubra el-Kheima, Toukh, العبور, …)';

    /**
     * Token → zone slug. First match wins, so order matters — longer/more
     * specific tokens go first to avoid e.g. "قها" matching inside another word.
     */
    private const TOKENS = [
        'شبرا الخيمة'   => 'shubra-elkheima',
        'شبين القناطر'  => 'qalyubia-other',
        'كفر شكر'        => 'kafr-shoukr',
        'الخانكة'        => 'el-khanka',
        'العبور'         => 'el-obour',
        'طوخ'            => 'toukh',
        'قها'            => 'qaha',
        // "القليوب" intentionally omitted — collides with "محافظة القليوبية"
        // which is in every Banha address. Handle Qalyub city manually.
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $onlyBanha = (bool) $this->option('only-banha');

        $zoneIdBySlug = DB::table('zones')->pluck('id', 'slug')->all();
        $banhaId = $zoneIdBySlug['banha'] ?? null;

        $q = Business::query()->whereNotNull('address');
        if ($onlyBanha && $banhaId) $q->where('zone_id', $banhaId);

        $moved = []; $unchanged = 0; $unknown = 0;

        $q->chunkById(500, function ($chunk) use (&$moved, &$unchanged, &$unknown, $zoneIdBySlug, $dry) {
            foreach ($chunk as $b) {
                $hint = null;
                foreach (self::TOKENS as $token => $slug) {
                    if (mb_strpos($b->address, $token) !== false) { $hint = $slug; break; }
                }
                if (! $hint) { $unknown++; continue; }

                $newId = $zoneIdBySlug[$hint] ?? null;
                if (! $newId) { $unknown++; continue; }
                if ((int) $b->zone_id === (int) $newId) { $unchanged++; continue; }

                $moved[] = ['id' => $b->id, 'name' => $b->name, 'from' => $b->zone_id, 'to' => $newId, 'slug' => $hint];

                if (! $dry) {
                    Business::where('id', $b->id)->update(['zone_id' => $newId]);
                }
            }
        });

        $this->newLine();
        $this->info(($dry ? 'DRY · ' : '').'Would move: '.count($moved).' · unchanged: '.$unchanged.' · no-match: '.$unknown);
        foreach (array_slice($moved, 0, 30) as $m) {
            $this->line('  • #'.$m['id'].'  '.$m['name'].'   → '.$m['slug']);
        }
        if (count($moved) > 30) $this->line('  … +'.(count($moved) - 30).' more');

        if ($dry) {
            $this->newLine();
            $this->comment('Run again without --dry to apply.');
        }

        return self::SUCCESS;
    }
}
