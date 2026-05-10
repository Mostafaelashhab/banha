<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Zone;
use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $kind     = $request->query('kind', 'sale');
        $category = $request->query('category');
        $zoneId   = $request->query('zone');
        $q        = trim((string) $request->query('q', ''));

        if (! isset(Listing::KINDS[$kind])) $kind = 'sale';

        $query = Listing::query()
            ->with(['user:id,username,avatar_seed,avatar_url,verification_tier,is_admin,last_seen_at', 'zone:id,name'])
            ->where('status', 'active')
            ->where('kind', $kind);

        if ($category && isset(Listing::CATEGORIES[$category])) $query->where('category', $category);
        if ($zoneId)  $query->where('zone_id', $zoneId);
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $listings = $query
            ->orderByRaw('CASE WHEN featured_until > NOW() THEN 0 ELSE 1 END')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        if ($request->boolean('partial') || $request->ajax()) {
            return view('marketplace._page', compact('listings'));
        }

        // Per-kind active count, used to drive the segmented control labels
        $kindCounts = Listing::query()
            ->where('status', 'active')
            ->selectRaw('kind, count(*) as c')
            ->groupBy('kind')
            ->pluck('c', 'kind')
            ->all();

        return view('marketplace.index', [
            'listings'      => $listings,
            'kinds'         => Listing::KINDS,
            'kindCounts'    => $kindCounts,
            'categories'    => Listing::CATEGORIES,
            'activeKind'    => $kind,
            'activeCategory'=> $category,
            'activeZone'    => $zoneId ? (int) $zoneId : null,
            'zones'         => Zone::orderBy('sort')->get(),
            'q'             => $q,
        ]);
    }

    public function show(Listing $listing)
    {
        if ($listing->status !== 'active' && Auth::id() !== $listing->user_id) abort(404);

        $listing->load(['user:id,username,avatar_seed,avatar_url,verification_tier,is_admin,last_seen_at', 'zone:id,name']);
        $listing->increment('views');

        return view('marketplace.show', compact('listing'));
    }

    public function create()
    {
        return view('marketplace.create', [
            'kinds'      => Listing::KINDS,
            'categories' => Listing::CATEGORIES,
            'zones'      => Zone::orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $key = 'listing:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'title' => 'هدّي شوية — مش أكتر من ٥ إعلانات في الدقيقة.',
            ]);
        }
        RateLimiter::hit($key, 60);

        $data = $this->validateListing($request);

        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = ImageUploader::store($request->file('photo'), 'listings');
        }

        $listing = Listing::create([
            'user_id'          => Auth::id(),
            'zone_id'          => $data['zone_id'] ?? Auth::user()->zone_id,
            'kind'             => $data['kind'],
            'category'         => $data['category'],
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'price'            => in_array($data['kind'], ['sale','buy'], true) ? ($data['price'] ?? null) : null,
            'currency'         => 'EGP',
            'negotiable'       => (bool) ($data['negotiable'] ?? true),
            'photo_url'        => $photo,
            'contact_phone'    => $data['contact_phone'] ?? null,
            'contact_whatsapp' => $data['contact_whatsapp'] ?? null,
            'status'           => 'active',
            'expires_at'       => now()->addDays(60),
        ]);

        return redirect()->route('marketplace.show', $listing)
            ->with('flash', 'إعلانك اتنشر! 🎉');
    }

    public function destroy(Listing $listing)
    {
        if ($listing->user_id !== Auth::id() && ! Auth::user()->is_admin) abort(403);
        ImageUploader::delete($listing->photo_url);
        $listing->update(['status' => 'removed']);
        return redirect()->route('marketplace.index')->with('flash', 'تم حذف الإعلان.');
    }

    public function markSold(Listing $listing)
    {
        if ($listing->user_id !== Auth::id()) abort(403);
        $listing->update(['status' => 'sold']);
        return back()->with('flash', '✓ متأشّر "اتباع".');
    }

    private function validateListing(Request $request): array
    {
        return $request->validate([
            'kind'             => ['required', 'in:'.implode(',', array_keys(Listing::KINDS))],
            'category'         => ['required', 'in:'.implode(',', array_keys(Listing::CATEGORIES))],
            'title'            => ['required', 'string', 'min:3', 'max:120'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'price'            => ['nullable', 'integer', 'min:0', 'max:99999999'],
            'negotiable'       => ['nullable', 'boolean'],
            'zone_id'          => ['nullable', 'exists:zones,id'],
            'contact_phone'    => ['nullable', 'regex:/^01[0125][0-9]{8}$/'],
            'contact_whatsapp' => ['nullable', 'regex:/^01[0125][0-9]{8}$/'],
            'photo'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }
}
