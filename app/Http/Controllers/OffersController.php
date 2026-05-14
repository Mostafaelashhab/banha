<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Listing;
use Illuminate\Http\Request;

/**
 * "عروض بنها النهارده" — surfaces promoted businesses + featured marketplace
 * listings as a single shoppable feed, plus a category quick-filter.
 */
class OffersController extends Controller
{
    public function index(Request $request)
    {
        $cat = $request->query('cat');

        $businesses = Business::query()
            ->where('is_active', true)
            ->where('promoted_until', '>', now())
            ->when($cat, fn ($q) => $q->where('category', $cat))
            ->with(['zone:id,name', 'photos:id,business_id,url'])
            ->orderByDesc('promoted_until')
            ->limit(40)
            ->get();

        $listings = Listing::query()
            ->where('status', 'active')
            ->where('featured_until', '>', now())
            ->with(['zone:id,name'])
            ->orderByDesc('featured_until')
            ->limit(40)
            ->get();

        // Categories shown as a sticky filter row.
        $cats = collect([
            'food'      => 'مطاعم',
            'shops'     => 'محلات',
            'services'  => 'خدمات',
            'medical'   => 'صحة',
            'education' => 'كورسات وتعليم',
        ]);

        return view('offers.index', compact('businesses', 'listings', 'cats', 'cat'));
    }
}
