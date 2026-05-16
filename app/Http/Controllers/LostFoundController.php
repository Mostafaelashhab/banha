<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

/**
 * Lost & Found board — surfaces the existing Listing rows whose `kind` is
 * `lost` or `found`. Same DB as the marketplace, dedicated UX.
 */
class LostFoundController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'all');   // all | lost | found
        if (! in_array($tab, ['all', 'lost', 'found'], true)) $tab = 'all';

        $q = Listing::query()
            ->where('status', 'active')
            ->whereIn('kind', ['lost', 'found'])
            ->with(['user:id,username,avatar_seed,avatar_url', 'zone:id,name'])
            ->latest();

        if ($tab === 'lost')  $q->where('kind', 'lost');
        if ($tab === 'found') $q->where('kind', 'found');

        $items = $q->limit(80)->get();

        $counts = [
            'all'   => Listing::query()->where('status', 'active')->whereIn('kind', ['lost', 'found'])->count(),
            'lost'  => Listing::query()->where('status', 'active')->where('kind', 'lost')->count(),
            'found' => Listing::query()->where('status', 'active')->where('kind', 'found')->count(),
        ];

        return view('lost-found.index', compact('items', 'tab', 'counts'));
    }
}
