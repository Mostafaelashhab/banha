<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\JobRequest;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Craftsmen vertical — campaign destination.
 *
 *   GET  /craftsmen              → landing page (trades grid + featured + jobs feed)
 *   GET  /craftsmen/{trade}      → per-trade SEO page (e.g. /craftsmen/plumber)
 *   GET  /craftsmen/signup       → optimized craftsman onboarding
 *   POST /craftsmen/signup       → persist + redirect to phone-verify
 */
class CraftsmenController extends Controller
{
    /** Trades surfaced on the landing page (in this display order). */
    private const FEATURED_TRADES = [
        'plumber', 'electrician', 'ac_tech', 'painter', 'carpenter',
        'appliance_tech', 'gas_tech', 'tile_setter', 'aluminum',
        'finishing', 'satellite_tech', 'moving',
        'pest_control', 'locksmith', 'welder', 'blacksmith',
    ];

    /**
     * Indicative price ranges (EGP) per trade — shown on per-trade pages.
     * Used to *anchor* user expectations and add SEO content. The actual
     * price is decided between user and craftsman; this is "from-to" range.
     */
    private const PRICE_GUIDE = [
        'plumber'        => ['تركيب حنفية' => '50-100',  'إصلاح تسريب' => '100-300',   'فك وتركيب سخان' => '200-400'],
        'electrician'    => ['تركيب لمبة' => '30-60',    'تركيب فيشة'  => '50-100',    'تأسيس شقة'      => '2000-5000'],
        'ac_tech'        => ['غسيل تكييف' => '150-300',  'شحن فريون'   => '200-500',   'تركيب تكييف'    => '300-700'],
        'painter'        => ['متر بلاستيك' => '40-70',   'متر سيلر'    => '70-120',    'دوكو خشب'       => '300-800'],
        'carpenter'      => ['تصليح باب' => '100-300',   'تركيب مطبخ' => '500-2000',   'تفصيل دولاب'    => '1500-5000'],
        'tile_setter'    => ['متر سيراميك' => '60-120',  'متر بورسلين' => '90-180',    'متر رخام'       => '200-500'],
        'appliance_tech' => ['تصليح غسالة' => '150-400', 'تصليح تلاجة' => '200-500',   'تصليح بوتاجاز'  => '100-300'],
        'gas_tech'       => ['وصلة غاز' => '150-300',    'كشف تسريب'  => '100-200'],
        'aluminum'       => ['متر شباك' => '600-1200',   'متر باب'    => '900-1800'],
        'finishing'      => ['شقة كاملة' => 'حسب المتر', 'استشارة'    => 'مجاناً'],
        'satellite_tech' => ['تركيب دش' => '200-500',    'برمجة ريسيفر' => '50-100'],
        'pest_control'   => ['شقة 100م' => '200-400',    'فيلا'        => '400-800'],
        'locksmith'      => ['فتح باب' => '100-300',     'تركيب قفل'  => '150-400'],
        'welder'         => ['متر لحام' => '80-150'],
        'blacksmith'     => ['متر شبك' => '200-400',     'بوابة'      => '1500-5000'],
        'moving'         => ['شقة قريبة' => '500-1500',  'بعيدة'      => '1500-4000'],
    ];

    /**
     * Hand-curated SEO copy for per-trade pages. Short, factual paragraphs that
     * answer real intent like "كيف أختار سباك كويس" / "متى يجي السباك".
     */
    private const TRADE_TIPS = [
        'plumber' => 'اختار السبّاك اللي عنده ضمان مكتوب على الشغل، وبيوضّحلك السعر قبل ما يبدأ. لو الشغلانة طارئة (تسريب أو سدّة)، شوف اللي عليه علامة ⚡ "يقبل طلبات طارئة".',
        'electrician' => 'لو الدائرة بتفصل أكتر من مرة، اطلب كهربائي يكشف لك الـ load بالمتر مش الفك والتركيب. اطلب عرض كتابي قبل تأسيس شقة كاملة.',
        'ac_tech' => 'الغسيل السنوي للتكييف بيوفّر كهربا. تأكد إن الفني عنده ماكينة ضغط مياه ومش "غسيل سطحي" بالقماش. للتركيب اطلب وصلة نحاس مش ألوميتال.',
        'painter' => 'احسب المتر مرتب أحسن من الشقة كاملة. اطلب اسم البويا والشركة والسعر التفصيلي. صبغة "الفل" أغلى من "البلاستيك" بحوالي 40-60%.',
        'tile_setter' => 'متر السيراميك سعر، الرخام سعر، البورسلين سعر تالت. اطلب عيّنة ٢-٣ سيراميك مختلف قبل ما تبتدي. شيك على الـ "كحلة" بعد ٣ أيام.',
    ];

    public function index(Request $request)
    {
        // ── Per-trade counts (active + serving this user's apparent zone if any)
        $counts = Business::query()
            ->where('category', 'craftsmen')
            ->where('is_active', true)
            ->selectRaw('sub_type, COUNT(*) as n')
            ->groupBy('sub_type')
            ->pluck('n', 'sub_type')
            ->all();

        // Total active + paid-verified
        $totalCraftsmen = array_sum($counts);
        $verifiedCount  = Business::where('category', 'craftsmen')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_verified', true)->orWhere('is_verified_paid', true);
            })
            ->count();

        // Top trades to render with counts (use FEATURED_TRADES order)
        $trades = collect(self::FEATURED_TRADES)->map(function ($key) use ($counts) {
            $meta = Business::SUB_TYPES[$key] ?? null;
            if (! $meta) return null;
            return [
                'key'   => $key,
                'label' => $meta['label'],
                'emoji' => $meta['emoji'] ?? '🔧',
                'icon'  => $meta['icon']  ?? 'wrench',
                'count' => (int) ($counts[$key] ?? 0),
            ];
        })->filter()->values();

        // Featured craftsmen — verified-paid > verified > rated, capped 6
        $featured = Business::query()
            ->where('category', 'craftsmen')
            ->where('is_active', true)
            ->orderByDesc('is_verified_paid')
            ->orderByDesc('is_verified')
            ->orderByDesc('rating_avg')
            ->orderByDesc('ratings_count')
            ->limit(6)
            ->get();

        // 24h emergency-accepting craftsmen
        $emergency = Business::query()
            ->where('category', 'craftsmen')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('accepts_emergency', true)->orWhere('is_24h', true);
            })
            ->orderByDesc('is_verified_paid')
            ->limit(4)
            ->get();

        // Live open jobs (campaign loves this — proves the marketplace works)
        $openJobs = JobRequest::open()
            ->latest()
            ->limit(5)
            ->get();

        return view('craftsmen.index', compact(
            'trades', 'totalCraftsmen', 'verifiedCount',
            'featured', 'emergency', 'openJobs'
        ));
    }

    public function trade(string $trade, Request $request)
    {
        $meta = Business::SUB_TYPES[$trade] ?? null;
        abort_unless($meta && ($meta['category'] ?? null) === 'craftsmen', 404);

        $zoneId = (int) $request->query('zone');
        $verifiedOnly = $request->boolean('verified');

        $q = Business::query()
            ->where('category', 'craftsmen')
            ->where('sub_type', $trade)
            ->where('is_active', true);

        if ($verifiedOnly) {
            $q->where(function ($w) {
                $w->where('is_verified', true)->orWhere('is_verified_paid', true);
            });
        }
        if ($zoneId) {
            // Either home zone OR JSON service_zones contains it
            $q->where(function ($w) use ($zoneId) {
                $w->where('zone_id', $zoneId)
                  ->orWhereRaw('JSON_CONTAINS(service_zones, ?)', ['"' . $zoneId . '"'])
                  ->orWhereRaw('JSON_CONTAINS(service_zones, ?)', [(string) $zoneId]);
            });
        }

        $businesses = $q->orderByDesc('is_verified_paid')
            ->orderByDesc('is_verified')
            ->orderByDesc('rating_avg')
            ->orderByDesc('ratings_count')
            ->limit(60)
            ->get();

        $zones      = Zone::where('is_active', true)->orderBy('sort')->get();
        $priceGuide = self::PRICE_GUIDE[$trade] ?? [];
        $tip        = self::TRADE_TIPS[$trade] ?? null;

        // Open jobs in this trade — for the supply side scrolling through
        $openJobs = JobRequest::open()->forTrade($trade)
            ->when($zoneId, fn ($x) => $x->forZone($zoneId))
            ->latest()->limit(6)->get();

        return view('craftsmen.trade', compact(
            'trade', 'meta', 'businesses', 'zones', 'zoneId',
            'verifiedOnly', 'priceGuide', 'tip', 'openJobs'
        ));
    }

    public function signup()
    {
        return view('craftsmen.signup', [
            'trades' => collect(Business::SUB_TYPES)
                ->filter(fn ($m) => ($m['category'] ?? null) === 'craftsmen')
                ->map(fn ($m, $key) => ['key' => $key, 'label' => $m['label'], 'emoji' => $m['emoji'] ?? '🔧'])
                ->values()
                ->all(),
            'zones'  => Zone::where('is_active', true)->orderBy('sort')->get(),
            'user'   => Auth::user(),
        ]);
    }

    public function storeSignup(Request $request)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'min:3', 'max:120'],
            'sub_type'         => ['required', 'string'],
            'zone_id'          => ['required', 'exists:zones,id'],
            'service_zones'    => ['nullable', 'array'],
            'service_zones.*'  => ['integer', 'exists:zones,id'],
            'phone'            => ['required', 'regex:/^01[0125][0-9]{8}$/'],
            'whatsapp'         => ['nullable', 'regex:/^01[0125][0-9]{8}$/'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'accepts_emergency'=> ['nullable', 'boolean'],
            'min_callout_fee'  => ['nullable', 'integer', 'min:0', 'max:5000'],
            'description'      => ['nullable', 'string', 'max:500'],
        ]);

        $meta = Business::SUB_TYPES[$data['sub_type']] ?? null;
        if (! $meta || ($meta['category'] ?? null) !== 'craftsmen') {
            throw ValidationException::withMessages(['sub_type' => 'تخصص غير صالح.']);
        }

        // Default whatsapp to phone (most craftsmen use one number)
        $data['whatsapp'] = $data['whatsapp'] ?: $data['phone'];

        $business = Business::create([
            'name'             => $data['name'],
            'category'         => 'craftsmen',
            'sub_type'         => $data['sub_type'],
            'zone_id'          => $data['zone_id'],
            'service_zones'    => $data['service_zones'] ?? [(int) $data['zone_id']],
            'phone'            => $data['phone'],
            'whatsapp'         => $data['whatsapp'],
            'years_experience' => $data['years_experience'] ?? null,
            'accepts_emergency'=> (bool) ($data['accepts_emergency'] ?? false),
            'min_callout_fee'  => $data['min_callout_fee'] ?? null,
            'description'      => $data['description'] ?? null,
            'is_active'        => true,
            'owner_user_id'    => Auth::id(),
            'emoji'            => $meta['emoji'] ?? '🔧',
        ]);

        return redirect()->route('directory.show', $business)
            ->with('flash', '✓ تم تسجيلك. الناس بقت تشوف نشاطك دلوقتي.');
    }
}
