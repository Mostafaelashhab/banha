<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

/**
 * Banha Jobs Board — surfaces listings in the `jobs` category as a dedicated
 * job-finding experience (separate from the general marketplace).
 *
 * Two listing kinds make sense here:
 *  - `sale`  = "I'm offering a job" (employer post)
 *  - `buy`   = "I'm looking for work" (worker post)
 *
 * The same underlying `listings` table powers it — no new model needed.
 */
class JobsController extends Controller
{
    public function index(Request $request)
    {
        $side = $request->query('side', 'hiring'); // hiring | seeking
        if (! in_array($side, ['hiring', 'seeking'], true)) $side = 'hiring';

        $q = Listing::query()
            ->where('status', 'active')
            ->where('category', 'jobs')
            ->with(['user:id,username,avatar_seed,avatar_url', 'zone:id,name'])
            ->latest();

        if ($side === 'hiring')  $q->where('kind', 'sale');
        if ($side === 'seeking') $q->where('kind', 'buy');

        $items = $q->limit(60)->get();

        $counts = [
            'hiring'  => Listing::query()->where('status', 'active')->where('category', 'jobs')->where('kind', 'sale')->count(),
            'seeking' => Listing::query()->where('status', 'active')->where('category', 'jobs')->where('kind', 'buy')->count(),
        ];

        return view('jobs.index', compact('items', 'side', 'counts'));
    }
}
