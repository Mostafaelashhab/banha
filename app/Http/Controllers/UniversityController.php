<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Listing;
use Illuminate\Http\Request;

/**
 * "دليل طلاب جامعة بنها"
 *
 * Hub page that bundles food, housing, books, courses, part-time jobs,
 * and transport relevant to Benha University students.
 *
 * Heuristics:
 *  - "Near university" is approximated by zone-name or address LIKE matches.
 *    The campus is in central Banha; a precise geo-radius is a future task.
 */
class UniversityController extends Controller
{
    private const CAMPUS_HINTS = ['جامعة', 'كلية', 'فريد ندا', 'الاستاد', 'وسط البلد'];

    public function index(Request $request)
    {
        $nearbyFood = Business::query()
            ->where('is_active', true)
            ->where('category', 'food')
            ->where(function ($q) {
                foreach (self::CAMPUS_HINTS as $h) {
                    $q->orWhere('address', 'like', "%{$h}%");
                }
            })
            ->with(['zone:id,name', 'photos:id,business_id,url'])
            ->orderByDesc('is_verified')
            ->orderByDesc('rating_avg')
            ->limit(12)
            ->get();

        $bookshops = Business::query()
            ->where('is_active', true)
            ->whereIn('sub_type', ['bookshop', 'printing'])
            ->with(['zone:id,name', 'photos:id,business_id,url'])
            ->orderByDesc('is_verified')
            ->limit(10)
            ->get();

        $courses = Business::query()
            ->where('is_active', true)
            ->whereIn('sub_type', ['tutor', 'edu_center', 'edu_lang'])
            ->with(['zone:id,name', 'photos:id,business_id,url'])
            ->orderByDesc('rating_avg')
            ->limit(10)
            ->get();

        // Real-estate / books / jobs listings from the marketplace
        $housing = Listing::query()
            ->where('status', 'active')
            ->where('category', 'real_estate')
            ->latest()
            ->limit(8)
            ->get();

        $bookListings = Listing::query()
            ->where('status', 'active')
            ->where('category', 'books')
            ->latest()
            ->limit(8)
            ->get();

        $jobs = Listing::query()
            ->where('status', 'active')
            ->where('category', 'jobs')
            ->latest()
            ->limit(8)
            ->get();

        $transport = Business::query()
            ->where('is_active', true)
            ->where('category', 'transport')
            ->with(['zone:id,name'])
            ->orderByDesc('is_verified')
            ->limit(10)
            ->get();

        return view('university.index', compact(
            'nearbyFood', 'bookshops', 'courses', 'housing', 'bookListings', 'jobs', 'transport'
        ));
    }
}
