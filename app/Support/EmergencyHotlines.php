<?php

namespace App\Support;

class EmergencyHotlines
{
    /**
     * Egyptian national emergency hotlines.
     * Format: ['number' => '...', 'label' => '...', 'icon' => '...', 'tone' => 'mint|blush|coral']
     */
    public const HOTLINES = [
        'police'         => ['number' => '122',   'label' => 'الشرطة',                   'icon' => 'flag',        'tone' => 'blush'],
        'ambulance'      => ['number' => '123',   'label' => 'الإسعاف',                  'icon' => 'stethoscope', 'tone' => 'blush'],
        'civil_defense'  => ['number' => '180',   'label' => 'الحماية المدنية / المطافي', 'icon' => 'flame',       'tone' => 'blush'],
        'electricity'    => ['number' => '121',   'label' => 'طوارئ الكهرباء',           'icon' => 'bolt',        'tone' => 'mint'],
        'water'          => ['number' => '175',   'label' => 'طوارئ المياه',             'icon' => 'tag',         'tone' => 'mint'],
        'gas'            => ['number' => '129',   'label' => 'طوارئ الغاز',              'icon' => 'flame',       'tone' => 'blush'],
        'tourism_police' => ['number' => '126',   'label' => 'شرطة السياحة',             'icon' => 'flag',        'tone' => 'coral'],
        'traffic'        => ['number' => '128',   'label' => 'الإسعاف على الطريق',       'icon' => 'traffic',     'tone' => 'blush'],
        'environment'    => ['number' => '105',   'label' => 'طوارئ البيئة',             'icon' => 'map-pin',     'tone' => 'mint'],
        'consumer'       => ['number' => '19588', 'label' => 'حماية المستهلك',           'icon' => 'tag',         'tone' => 'coral'],
    ];

    /**
     * Map alert types to relevant hotlines, in display order.
     */
    public const ALERT_HOTLINE_MAP = [
        'electricity' => ['electricity'],
        'water'       => ['water'],
        'accident'    => ['ambulance', 'police', 'traffic'],
        'traffic'     => ['traffic', 'police'],
        'checkpoint'  => [],
        'other'       => ['police'],
    ];

    public static function forAlertType(string $type): array
    {
        $keys = self::ALERT_HOTLINE_MAP[$type] ?? [];
        $list = [];
        foreach ($keys as $k) {
            if (isset(self::HOTLINES[$k])) {
                $list[] = self::HOTLINES[$k] + ['key' => $k];
            }
        }
        return $list;
    }

    public static function all(): array
    {
        $list = [];
        foreach (self::HOTLINES as $k => $h) {
            $list[] = $h + ['key' => $k];
        }
        return $list;
    }
}
