<?php

namespace App\Support;

class AnonSeed
{
    public const ADJECTIVES = [
        'مغمور','مجهول','صريح','جريء','هادي','حِكيم','عابر','حَلَمان','شاطر','أصيل','مُحترِم','هَنبهة',
    ];

    public const NOUNS = [
        'بنهاوي','قلويبي','فولـ','كشري','مكرونة','شاي_تقيل','قهوة_سادة','حمام_زاجل',
        'محطة','جامعة','زراعي','نيل','قطار','تمر_حِنّاء','مولد',
    ];

    public static function generate(): string
    {
        $a = self::ADJECTIVES[array_rand(self::ADJECTIVES)];
        $n = self::NOUNS[array_rand(self::NOUNS)];
        $num = random_int(10, 9999);
        return "{$n}_{$a}_{$num}";
    }

    public static function avatarColor(string $seed): string
    {
        $palette = ['#FF7A4D', '#FFB85C', '#1FA857', '#E64646', '#7C3AED', '#0EA5E9', '#F59E0B', '#10B981'];
        $idx = abs(crc32($seed)) % count($palette);
        return $palette[$idx];
    }

    public static function initial(string $seed): string
    {
        $clean = ltrim($seed, '_');
        $first = mb_substr($clean, 0, 1, 'UTF-8');
        return $first ?: 'ب';
    }
}
