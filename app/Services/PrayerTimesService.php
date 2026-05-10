<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Daily prayer times from aladhan.com (free, no key, ODbL-friendly).
 *
 * Uses Egyptian General Authority of Survey method (id=5) — the calendar
 * recognized in Egypt. Cached per (lat,lng,day) for 24h since prayer times
 * are deterministic per location per day.
 */
class PrayerTimesService
{
    public const BANHA_LAT = 30.4582;
    public const BANHA_LNG = 31.1797;
    private const METHOD   = 5;     // 5 = Egyptian General Authority of Survey

    /**
     * Returns:
     * [
     *   'date'      => 'Sat 10 May 2026',
     *   'hijri'     => '23 Dhul-Qa'dah 1447',
     *   'times'     => ['fajr' => '03:45', 'sunrise' => '05:12', 'dhuhr' => '12:01', 'asr' => '15:34', 'maghrib' => '18:50', 'isha' => '20:15'],
     *   'next'      => ['key' => 'asr', 'label' => 'العصر', 'time' => '15:34', 'in_minutes' => 87],
     * ]
     * Returns null on fetch failure (e.g. offline) — caller should hide the widget.
     */
    public static function forBanha(): ?array
    {
        return self::forCoords(self::BANHA_LAT, self::BANHA_LNG);
    }

    public static function forCoords(float $lat, float $lng): ?array
    {
        $today = CarbonImmutable::now('Africa/Cairo');
        $cacheKey = sprintf('prayer:%.4f:%.4f:%s', $lat, $lng, $today->toDateString());

        $base = Cache::remember($cacheKey, now()->addHours(24), function () use ($lat, $lng, $today) {
            try {
                $res = Http::timeout(8)
                    ->withHeaders(['User-Agent' => 'Banhawy/1.0 (prayer-times)'])
                    ->get("https://api.aladhan.com/v1/timings/{$today->format('d-m-Y')}", [
                        'latitude'  => $lat,
                        'longitude' => $lng,
                        'method'    => self::METHOD,
                    ]);
            } catch (\Throwable $e) {
                return null;
            }
            if (! $res->ok()) return null;

            $data = $res->json('data') ?? [];
            $t    = $data['timings'] ?? [];
            // Strip any "(EET)" suffix the API adds and keep "HH:MM"
            $clean = fn ($v) => $v ? preg_replace('/[^0-9:]/', '', explode(' ', $v)[0]) : null;

            $times = [
                'fajr'    => $clean($t['Fajr']    ?? null),
                'sunrise' => $clean($t['Sunrise'] ?? null),
                'dhuhr'   => $clean($t['Dhuhr']   ?? null),
                'asr'     => $clean($t['Asr']     ?? null),
                'maghrib' => $clean($t['Maghrib'] ?? null),
                'isha'    => $clean($t['Isha']    ?? null),
            ];
            return [
                'date'        => $today->toDateString(),
                'date_label'  => $today->translatedFormat('D j F Y'),
                'hijri'       => trim(($data['date']['hijri']['day'] ?? '') . ' ' .
                    ($data['date']['hijri']['month']['ar'] ?? '') . ' ' .
                    ($data['date']['hijri']['year'] ?? '')),
                'times'       => $times,
                'pretty'      => array_map(fn ($v) => $v ? self::format12($v) : null, $times),
            ];
        });

        if (! $base) return null;

        $base['next'] = self::computeNext($base['times']);
        return $base;
    }

    /** Convert "HH:MM" (24h) to "h:MM ص/م". e.g. "16:29" → "4:29 م". */
    public static function format12(string $hhmm): string
    {
        if (! preg_match('/^(\d{1,2}):(\d{2})$/', $hhmm, $m)) return $hhmm;
        $h = (int) $m[1];
        $min = $m[2];
        $suffix = $h < 12 ? 'ص' : 'م';
        $h12 = $h % 12 ?: 12;
        return $h12 . ':' . $min . ' ' . $suffix;
    }

    /** Find the next upcoming prayer based on current Cairo time. */
    private static function computeNext(array $times): ?array
    {
        $labels = [
            'fajr'    => 'الفجر',
            'sunrise' => 'الشروق',
            'dhuhr'   => 'الظهر',
            'asr'     => 'العصر',
            'maghrib' => 'المغرب',
            'isha'    => 'العشاء',
        ];
        $now    = CarbonImmutable::now('Africa/Cairo');
        $nowMin = (int) $now->format('G') * 60 + (int) $now->format('i');

        foreach ($labels as $key => $label) {
            $t = $times[$key] ?? null;
            if (! $t || ! preg_match('/^(\d{1,2}):(\d{2})$/', $t, $m)) continue;
            $tMin = ((int) $m[1]) * 60 + (int) $m[2];
            if ($tMin > $nowMin) {
                return [
                    'key'         => $key,
                    'label'       => $label,
                    'time'        => $t,
                    'pretty_time' => self::format12($t),
                    'in_minutes'  => $tMin - $nowMin,
                ];
            }
        }
        // All today's prayers passed — show tomorrow's Fajr label as next
        return $times['fajr'] ? [
            'key'         => 'fajr',
            'label'       => 'فجر بكرة',
            'time'        => $times['fajr'],
            'pretty_time' => self::format12($times['fajr']),
            'in_minutes'  => null,
        ] : null;
    }
}
