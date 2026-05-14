<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * "مفتوح دلوقتي في بنها"
 *
 * Honest version of the open-now filter — only lists businesses where we
 * actually have a working-hours schedule. Anything else gets pushed below
 * the fold with a clear "المواعيد غير مؤكدة" badge so users don't drive
 * out to a closed shop based on a guess.
 */
class OpenNowController extends Controller
{
    /**
     * Categories shown as quick filters at the top.
     * Keys = Business::CATEGORIES keys, values = display labels.
     */
    private const QUICK_FILTERS = [
        'food'      => 'مطاعم وكافيهات',
        'medical'   => 'صيدليات وعيادات',
        'shops'     => 'محلات وسوبر ماركت',
        'emergency' => 'طوارئ',
    ];

    public function index(Request $request)
    {
        $cat = $request->query('cat');
        if ($cat && ! array_key_exists($cat, self::QUICK_FILTERS)) {
            $cat = null;
        }

        $cacheKey = 'open-now:v2:'.($cat ?: 'all');

        [$openConfirmed, $maybeOpen] = Cache::remember($cacheKey, now()->addMinutes(3), function () use ($cat) {
            $base = Business::query()
                ->where('is_active', true)
                ->when($cat, function ($q) use ($cat) {
                    // "medical" filter covers both clinics and pharmacies in the UI
                    // — pharmacies live under category=medical too.
                    return $q->where('category', $cat);
                })
                ->with(['zone:id,name', 'photos:id,business_id,url']);

            // Confirmed-open: has 24h flag or a hours_schedule that says "open right now"
            $confirmedCandidates = (clone $base)
                ->where(function ($q) {
                    $q->where('is_24h', true)->orWhereNotNull('hours_schedule');
                })
                ->orderByDesc('is_verified')
                ->orderByDesc('rating_avg')
                ->limit(80)
                ->get()
                ->filter(fn ($b) => $b->isOpenNow() === true)
                ->take(40)
                ->values();

            // No-schedule rows in same filter — surfaced with a "غير مؤكدة" badge
            $unknown = (clone $base)
                ->whereNull('hours_schedule')
                ->where('is_24h', false)
                ->orderByDesc('is_verified')
                ->orderByDesc('rating_avg')
                ->limit(20)
                ->get();

            return [$confirmedCandidates, $unknown];
        });

        return view('open-now.index', [
            'openConfirmed' => $openConfirmed,
            'maybeOpen'     => $maybeOpen,
            'cat'           => $cat,
            'filters'       => self::QUICK_FILTERS,
        ]);
    }
}
