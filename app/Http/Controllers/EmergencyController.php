<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

/**
 * "أرقام طوارئ بنها والقليوبية"
 *
 * Pulls businesses in the emergency category + curated craftsmen sub-types
 * who flagged themselves as emergency-call responders.
 */
class EmergencyController extends Controller
{
    /**
     * Hardcoded national emergency hotlines — always shown first, even if the
     * directory has zero rows for them. Reliability matters more than data.
     */
    private const NATIONAL_HOTLINES = [
        ['label' => 'إسعاف',           'phone' => '123', 'emoji' => '🚑', 'tone' => 'blush'],
        ['label' => 'شرطة النجدة',     'phone' => '122', 'emoji' => '🚓', 'tone' => 'coral'],
        ['label' => 'المطافي',          'phone' => '180', 'emoji' => '🚒', 'tone' => 'blush'],
        ['label' => 'الإسعاف الفوري',   'phone' => '137', 'emoji' => '🏥', 'tone' => 'mint'],
        ['label' => 'الكهرباء (أعطال)','phone' => '121', 'emoji' => '⚡',  'tone' => 'honey'],
        ['label' => 'الغاز (طوارئ)',   'phone' => '129', 'emoji' => '🔥', 'tone' => 'blush'],
        ['label' => 'المياه (شكاوى)',  'phone' => '125', 'emoji' => '💧', 'tone' => 'mint'],
        ['label' => 'الحماية المدنية',  'phone' => '180', 'emoji' => '🛡️', 'tone' => 'coral'],
    ];

    public function index(Request $request)
    {
        // Local emergency-tagged businesses (hospitals, pharmacies, civil defense, etc.)
        $emergency = Business::query()
            ->where('is_active', true)
            ->where('category', 'emergency')
            ->with(['zone:id,name'])
            ->orderByDesc('is_verified')
            ->orderByDesc('rating_avg')
            ->limit(60)
            ->get();

        // 24h pharmacies (the "صيدلية نوبتجية" use case)
        $pharmacies24h = Business::query()
            ->where('is_active', true)
            ->where('sub_type', 'pharmacy')
            ->where('is_24h', true)
            ->with(['zone:id,name'])
            ->orderByDesc('is_verified')
            ->limit(20)
            ->get();

        // Emergency craftsmen (plumber/electrician/AC tech who flagged emergency_call)
        $emergencyCraftsmen = Business::query()
            ->where('is_active', true)
            ->where('category', 'craftsmen')
            ->where('extra->emergency_call', true)
            ->with(['zone:id,name'])
            ->orderByDesc('rating_avg')
            ->limit(30)
            ->get()
            ->groupBy('sub_type');

        return view('emergency.index', [
            'hotlines'           => self::NATIONAL_HOTLINES,
            'emergency'          => $emergency,
            'pharmacies24h'      => $pharmacies24h,
            'emergencyCraftsmen' => $emergencyCraftsmen,
        ]);
    }
}
