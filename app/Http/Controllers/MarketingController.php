<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

/**
 * Public marketing landing pages — sell business owners on Banhawy.
 * These are sales pages, not the actual claim/menu flows
 * (those live in BusinessClaimController and MenuController).
 */
class MarketingController extends Controller
{
    public function claim(Request $request)
    {
        $stats = [
            'businesses' => Business::query()->where('is_active', true)->count(),
            'verified'   => Business::query()->where('is_verified', true)->count(),
            'zones'      => 9,
        ];

        return view('marketing.claim-landing', compact('stats'));
    }

    public function qrMenu(Request $request)
    {
        $sampleMenu = Business::query()
            ->where('is_active', true)
            ->where('has_menu', true)
            ->where('category', 'food')
            ->inRandomOrder()
            ->first();

        return view('marketing.qr-menu', compact('sampleMenu'));
    }
}
