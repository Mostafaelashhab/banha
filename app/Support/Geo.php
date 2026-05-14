<?php

namespace App\Support;

use App\Models\Business;
use App\Models\Zone;

/**
 * Location-phrase helpers — keeps SEO copy honest.
 *
 * "في بنها" is wrong for a clinic in Toukh or a restaurant in Shubra el-Kheima.
 * Use these to render the actual city, falling back to "في القليوبية" only when
 * we genuinely have no city signal.
 */
class Geo
{
    /** Default fallback when no zone is set. */
    public const DEFAULT_GOVERNORATE = 'القليوبية';

    /** Marketing-ish wide fallback used in homepage copy. */
    public const DEFAULT_AREA_PHRASE = 'بنها والقليوبية';

    /** Display name for a zone (e.g. "بنها", "طوخ"). */
    public static function cityName(?Zone $zone): string
    {
        return $zone?->name ?: 'بنها';
    }

    /** "في {city}" or "في بنها" as a safe fallback. */
    public static function inCity(?Zone $zone): string
    {
        return 'في '.self::cityName($zone);
    }

    /** "في بنها والقليوبية" — broad fallback for homepage / nav copy. */
    public static function inDefaultArea(): string
    {
        return 'في '.self::DEFAULT_AREA_PHRASE;
    }

    /** Governorate label — usually "القليوبية". */
    public static function governorate(?Zone $zone): string
    {
        return $zone?->governorate ?: self::DEFAULT_GOVERNORATE;
    }

    /**
     * Full SEO location phrase for a business.
     * Examples:
     *   "في بنها"            (zone = بنها)
     *   "في شبرا الخيمة"     (zone = شبرا الخيمة)
     *   "في بنها والقليوبية" (no zone)
     */
    public static function businessLocationPhrase(Business $business): string
    {
        $zone = $business->relationLoaded('zone') ? $business->zone : $business->zone;
        if ($zone) return self::inCity($zone);
        return self::inDefaultArea();
    }

    /**
     * Bare city display name for a business — no preposition.
     * Used in card chips and breadcrumbs.
     */
    public static function businessCityLabel(Business $business): string
    {
        return self::cityName($business->relationLoaded('zone') ? $business->zone : $business->zone);
    }
}
