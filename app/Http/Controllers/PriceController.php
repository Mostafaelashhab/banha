<?php

namespace App\Http\Controllers;

use App\Models\Price;
use App\Models\Product;
use App\Models\Zone;
use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PriceController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');
        $zoneId   = (int) ($request->query('zone') ?: Auth::user()->zone_id);

        $products = Product::where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('sort')
            ->get();

        // Today's avg per product (for current zone if set, else global)
        $todayAvg = DB::table('prices')
            ->select('product_id', DB::raw('AVG(price) as avg_p'), DB::raw('COUNT(*) as c'))
            ->where('created_at', '>=', now()->subHours(24))
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Yesterday's avg for delta
        $yesterdayAvg = DB::table('prices')
            ->select('product_id', DB::raw('AVG(price) as avg_p'))
            ->whereBetween('created_at', [now()->subHours(48), now()->subHours(24)])
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->groupBy('product_id')
            ->pluck('avg_p', 'product_id');

        return view('prices.index', [
            'products'     => $products,
            'todayAvg'     => $todayAvg,
            'yesterdayAvg' => $yesterdayAvg,
            'zones'        => Zone::orderBy('sort')->get(),
            'activeZone'   => $zoneId,
            'category'     => $category,
            'categories'   => Product::CATEGORIES,
        ]);
    }

    public function show(Product $product, Request $request)
    {
        $zoneId = (int) ($request->query('zone') ?: Auth::user()->zone_id);

        $recent = Price::with(['user:id,username', 'zone:id,name'])
            ->where('product_id', $product->id)
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->latest()
            ->limit(20)
            ->get();

        // 7-day daily avg trend
        $trend = DB::table('prices')
            ->select(
                DB::raw('DATE(created_at) as d'),
                DB::raw('AVG(price) as avg_p'),
                DB::raw('COUNT(*) as c')
            )
            ->where('product_id', $product->id)
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $avgToday = $recent->where('created_at', '>=', now()->subHours(24))->avg('price');

        $stats = [
            'avg_today' => $avgToday ?: $recent->avg('price'),
            'min'       => $recent->min('price'),
            'max'       => $recent->max('price'),
            'reports'   => Price::where('product_id', $product->id)
                                ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
                                ->count(),
        ];

        return view('prices.show', [
            'product'    => $product,
            'recent'     => $recent,
            'trend'      => $trend,
            'stats'      => $stats,
            'zones'      => Zone::orderBy('sort')->get(),
            'activeZone' => $zoneId,
        ]);
    }

    public function create(Request $request)
    {
        return view('prices.create', [
            'products' => Product::where('is_active', true)->orderBy('sort')->get(),
            'zones'    => Zone::orderBy('sort')->get(),
            'preselect'=> $request->query('product'),
        ]);
    }

    public function store(Request $request)
    {
        $key = 'price:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages([
                'price' => 'بعد شوية — كتر الإدخال.',
            ]);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'zone_id'    => ['required', 'exists:zones,id'],
            'price'      => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'shop_name'  => ['nullable', 'string', 'max:100'],
            'notes'      => ['nullable', 'string', 'max:200'],
        ]);

        Price::create([
            'product_id' => $data['product_id'],
            'zone_id'    => $data['zone_id'],
            'user_id'    => Auth::id(),
            'price'      => $data['price'],
            'shop_name'  => $data['shop_name'] ?? null,
            'notes'      => $data['notes'] ?? null,
        ]);

        BadgeService::onPriceSubmit(Auth::user());

        return redirect()->route('prices.show', $data['product_id'])
            ->with('flash', 'شكراً، السعر اتسجّل ودخل في المتوسط.');
    }
}
