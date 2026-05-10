<?php

namespace App\Console\Commands;

use App\Models\PushSubscription;
use App\Services\PrayerTimesService;
use App\Services\PushService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Sends prayer-time push notifications to users with `prayer_notify = true`.
 *
 * Designed to be called every minute via cron:
 *
 *   * * * * * cd /path/to/app && php artisan banha:send-prayer-pushes
 *
 * Idempotent — uses a cache key to ensure each prayer fires once per day even
 * if the cron runs multiple times in the same minute.
 */
class SendPrayerPushes extends Command
{
    protected $signature = 'banha:send-prayer-pushes
        {--dry : Print what would fire without actually pushing}
        {--force-prayer= : Force-send a specific prayer key (debug, e.g. asr)}';

    protected $description = 'Push prayer-time notifications to opted-in users (run every minute via cron)';

    private const LABELS = [
        'fajr'    => 'الفجر',
        'dhuhr'   => 'الظهر',
        'asr'     => 'العصر',
        'maghrib' => 'المغرب',
        'isha'    => 'العشاء',
    ];

    public function handle(): int
    {
        $prayer = PrayerTimesService::forBanha();
        if (! $prayer) {
            $this->warn('Prayer times unavailable (network failure?). Skipping.');
            return self::SUCCESS;
        }

        $now      = CarbonImmutable::now('Africa/Cairo');
        $today    = $now->toDateString();
        $nowHm    = $now->format('H:i');
        $forced   = $this->option('force-prayer');
        $isDry    = (bool) $this->option('dry');

        // Collect prayers that match the current minute (or are forced)
        $hits = [];
        foreach (self::LABELS as $key => $label) {
            $time = $prayer['times'][$key] ?? null;
            if (! $time) continue;
            if ($forced ? $forced === $key : $time === $nowHm) {
                $hits[] = ['key' => $key, 'label' => $label, 'time' => $time];
            }
        }

        if (empty($hits)) {
            return self::SUCCESS;
        }

        foreach ($hits as $hit) {
            $dedupeKey = "prayer-push:{$today}:{$hit['key']}";
            if (! $forced && Cache::has($dedupeKey)) {
                $this->info("Skip {$hit['label']}: already fired today.");
                continue;
            }

            $payload = [
                'title' => 'حان الآن وقت ' . $hit['label'],
                'body'  => PrayerTimesService::format12($hit['time']) . ' · بنها',
                'url'   => route('feed'),
                'tag'   => 'prayer-' . $hit['key'] . '-' . $today,
                'silent'=> false,
            ];

            // Subscribers = push subs whose user has opted into prayer_notify
            $subs = PushSubscription::query()
                ->whereHas('user', fn ($q) => $q->where('prayer_notify', true)
                    ->where('is_banned', false))
                ->get();

            $this->line("→ {$hit['label']} ({$hit['time']}) — {$subs->count()} subscribers");

            if ($isDry) {
                continue;
            }

            $result = PushService::sendToSubscriptions($subs, $payload);
            $this->info("   sent={$result['sent']} failed={$result['failed']} pruned={$result['pruned']}");

            // Mark fired (TTL = 25h to span across DST/timezone edges)
            Cache::put($dedupeKey, true, now()->addHours(25));
        }

        return self::SUCCESS;
    }
}
