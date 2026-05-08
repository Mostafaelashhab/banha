<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirectoryController extends Controller
{
    public function index()
    {
        $counts = DB::table('businesses')
            ->where('is_active', true)
            ->select('category', DB::raw('count(*) as c'))
            ->groupBy('category')
            ->pluck('c', 'category')
            ->all();

        $featured = Business::query()
            ->where('is_active', true)
            ->where('is_verified', true)
            ->with('zone:id,name')
            ->latest()
            ->limit(6)
            ->get();

        return view('directory.index', [
            'categories' => Business::CATEGORIES,
            'counts'     => $counts,
            'featured'   => $featured,
            'is24h'      => Business::where('is_active', true)->where('is_24h', true)->limit(8)->get(),
        ]);
    }

    public function category(string $category, Request $request)
    {
        if (! isset(Business::CATEGORIES[$category])) {
            abort(404);
        }

        $subType = $request->query('type');
        $zoneId  = $request->query('zone');
        $q       = trim((string) $request->query('q', ''));

        $query = Business::query()
            ->where('is_active', true)
            ->where('category', $category)
            ->with('zone:id,name');

        if ($subType) $query->where('sub_type', $subType);
        if ($zoneId)  $query->where('zone_id', $zoneId);
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhere('address', 'like', "%{$q}%");
            });
        }

        $businesses = $query->orderByDesc('is_verified')
            ->orderByDesc('rating_avg')
            ->orderByDesc('is_24h')
            ->paginate(20)
            ->withQueryString();

        // Sub-types belonging to this category
        $subTypes = collect(Business::SUB_TYPES)
            ->filter(fn ($s) => $s['category'] === $category)
            ->map(fn ($s, $key) => array_merge($s, ['key' => $key]))
            ->values();

        $subTypeCounts = DB::table('businesses')
            ->where('is_active', true)
            ->where('category', $category)
            ->select('sub_type', DB::raw('count(*) as c'))
            ->groupBy('sub_type')
            ->pluck('c', 'sub_type')
            ->all();

        return view('directory.category', [
            'category'      => $category,
            'meta'          => Business::CATEGORIES[$category],
            'businesses'    => $businesses,
            'subTypes'      => $subTypes,
            'subTypeCounts' => $subTypeCounts,
            'activeSubType' => $subType,
            'activeZone'    => $zoneId ? (int) $zoneId : null,
            'zones'         => Zone::orderBy('sort')->get(),
            'q'             => $q,
        ]);
    }

    public function show(Business $business)
    {
        if (! $business->is_active) abort(404);

        $business->load(['zone', 'owner:id,username']);

        $similar = Business::query()
            ->where('is_active', true)
            ->where('id', '!=', $business->id)
            ->where('sub_type', $business->sub_type)
            ->where('zone_id', $business->zone_id)
            ->limit(4)
            ->get();

        return view('directory.show', compact('business', 'similar'));
    }
}
