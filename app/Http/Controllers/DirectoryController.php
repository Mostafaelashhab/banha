<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DirectoryController extends Controller
{
    /**
     * Businesses sorted by distance from user-provided lat/lng (Haversine).
     * Cheap: just SQL math, no PHP loops, no extra storage.
     */
    public function nearby(Request $request)
    {
        $lat = (float) $request->query('lat', 0);
        $lng = (float) $request->query('lng', 0);
        $category = $request->query('category');

        if ($lat === 0.0 || $lng === 0.0) {
            return view('directory.nearby', [
                'businesses' => collect(),
                'lat'        => null,
                'lng'        => null,
                'category'   => $category,
                'categories' => Business::CATEGORIES,
            ]);
        }

        // Haversine in km — bounding box first for index efficiency, then exact distance
        $deg = 0.18;  // ~20km bounding box (1° lat ≈ 111km)
        $minLat = $lat - $deg; $maxLat = $lat + $deg;
        $minLng = $lng - $deg; $maxLng = $lng + $deg;

        $query = Business::query()
            ->where('is_active', true)
            ->whereNotNull('lat')->whereNotNull('lng')
            ->whereBetween('lat', [$minLat, $maxLat])
            ->whereBetween('lng', [$minLng, $maxLng])
            ->selectRaw('*, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(lat)) *
                    cos(radians(lng) - radians(?)) +
                    sin(radians(?)) * sin(radians(lat))
                )
            ) AS distance_km', [$lat, $lng, $lat])
            ->with('zone:id,name')
            ->orderBy('distance_km')
            ->limit(40);

        if ($category && isset(Business::CATEGORIES[$category])) {
            $query->where('category', $category);
        }

        return view('directory.nearby', [
            'businesses' => $query->get(),
            'lat'        => $lat,
            'lng'        => $lng,
            'category'   => $category,
            'categories' => Business::CATEGORIES,
        ]);
    }

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

        // Promoted (paid) bumps to the top, then verified, then by rating
        $businesses = $query
            ->orderByRaw('CASE WHEN promoted_until > NOW() THEN 0 ELSE 1 END')
            ->orderByDesc('is_verified')
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

        if ($request->boolean('partial') || $request->ajax()) {
            return view('directory.partials.category-page', compact('businesses'));
        }

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
        if (! $business->is_active && (! Auth::check() || Auth::id() !== $business->owner_user_id)) {
            abort(404);
        }

        $business->load(['zone', 'owner:id,username', 'photos']);

        // Track view (don't count owner's own views)
        if (! Auth::check() || Auth::id() !== $business->owner_user_id) {
            $business->increment('views_count');
        }

        $similar = Business::query()
            ->where('is_active', true)
            ->where('id', '!=', $business->id)
            ->where('sub_type', $business->sub_type)
            ->where('zone_id', $business->zone_id)
            ->limit(4)
            ->get();

        $reviews = $business->reviews()
            ->whereNotNull('body')
            ->where('body', '!=', '')
            ->orderByDesc('reviewed_at')
            ->limit(20)
            ->get();

        $myReview = Auth::check()
            ? $business->reviews()->where('user_id', Auth::id())->where('source', 'user')->first()
            : null;

        return view('directory.show', compact('business', 'similar', 'reviews', 'myReview'));
    }

    // ─── Owner CRUD ─────────────────────────────────────────────

    public function myListings()
    {
        $businesses = Business::query()
            ->where('owner_user_id', Auth::id())
            ->latest()
            ->get();

        return view('directory.my-listings', compact('businesses'));
    }

    public function create()
    {
        return view('directory.create', [
            'categories' => Business::CATEGORIES,
            'subTypes'   => Business::SUB_TYPES,
            'zones'      => Zone::orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $key = 'biz:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'name' => 'لا تستطيع إضافة أنشطة كتير في نفس الوقت. حاول لاحقاً.',
            ]);
        }
        RateLimiter::hit($key, 600);

        $data = $this->validateBusiness($request);

        $sm = Business::SUB_TYPES[$data['sub_type']];

        $photoUrl = $this->handlePhotoUpload($request);

        $business = Business::create([
            'name'            => $data['name'],
            'category'        => $sm['category'],
            'sub_type'        => $data['sub_type'],
            'custom_sub_type' => $data['custom_sub_type'] ?? null,
            'zone_id'         => $data['zone_id'],
            'owner_user_id'   => Auth::id(),
            'description'   => $data['description'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'whatsapp'      => $data['whatsapp'] ?? null,
            'address'       => $data['address'] ?? null,
            'hours'         => $data['hours'] ?? null,
            'is_24h'        => (bool) ($data['is_24h'] ?? false),
            'lat'           => $data['lat'] ?? null,
            'lng'           => $data['lng'] ?? null,
            'is_verified'   => false,
            'is_active'     => true,
            'emoji'         => $sm['emoji'],
            'photo_url'     => $photoUrl,
        ]);

        \App\Services\AdminNotificationService::onBusinessCreated($business->fresh()->load('owner'));

        if ($business->lat && $business->lng) {
            \Illuminate\Support\Facades\Cache::forget('map-data:v3:all');
            \Illuminate\Support\Facades\Cache::forget('map-data:v3:'.$business->category);
        }

        return redirect()->route('directory.show', $business)
            ->with('flash', '✓ نشاطك انضاف للدليل! هتراجعه فريق بنهاوي قريباً للتوثيق.');
    }

    public function edit(Business $business)
    {
        $this->authorizeOwner($business);
        return view('directory.edit', [
            'business'   => $business,
            'subTypes'   => Business::SUB_TYPES,
            'zones'      => Zone::orderBy('sort')->get(),
        ]);
    }

    public function update(Request $request, Business $business)
    {
        $this->authorizeOwner($business);

        $data = $this->validateBusiness($request);
        $sm   = Business::SUB_TYPES[$data['sub_type']];

        $newPhoto = $this->handlePhotoUpload($request, $business->photo_url);

        $business->update([
            'name'            => $data['name'],
            'category'        => $sm['category'],
            'sub_type'        => $data['sub_type'],
            'custom_sub_type' => $data['custom_sub_type'] ?? null,
            'zone_id'         => $data['zone_id'],
            'description' => $data['description'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'whatsapp'    => $data['whatsapp'] ?? null,
            'address'     => $data['address'] ?? null,
            'hours'       => $data['hours'] ?? null,
            'is_24h'      => (bool) ($data['is_24h'] ?? false),
            'lat'         => array_key_exists('lat', $data) ? $data['lat'] : $business->lat,
            'lng'         => array_key_exists('lng', $data) ? $data['lng'] : $business->lng,
            'emoji'       => $sm['emoji'],
            'photo_url'   => $newPhoto ?: $business->photo_url,
        ]);

        // Bust map cache so location changes show up immediately on /map
        \Illuminate\Support\Facades\Cache::forget('map-data:v3:all');
        \Illuminate\Support\Facades\Cache::forget('map-data:v3:'.$business->category);

        return redirect()->route('directory.show', $business)
            ->with('flash', '✓ تم تحديث النشاط.');
    }

    public function destroy(Business $business)
    {
        $this->authorizeOwner($business);
        $business->update(['is_active' => false]);
        return redirect()->route('directory.mine')->with('flash', 'تم حذف النشاط.');
    }

    /** Map view of all businesses (public, fast). */
    public function map()
    {
        return view('directory.map', [
            'categories' => Business::CATEGORIES,
        ]);
    }

    /** Lightweight JSON of businesses + events with lat/lng (used by /map). Cached. */
    public function mapData(Request $request)
    {
        $cat = $request->query('category');

        // Businesses (with `is_promoted` bubbled up so JS can style them differently)
        $businesses = \Illuminate\Support\Facades\Cache::remember('map-data:v3:'.($cat ?: 'all'), 300, function () use ($cat) {
            $q = Business::query()
                ->where('is_active', true)
                ->whereNotNull('lat')->whereNotNull('lng')
                ->select('id', 'name', 'category', 'lat', 'lng', 'is_verified', 'has_menu', 'rating_avg', 'phone', 'promoted_until');
            if ($cat) $q->where('category', $cat);
            return $q->limit(500)->get()->map(fn ($b) => [
                'id'          => (int) $b->id,
                'name'        => (string) $b->name,
                'category'    => (string) $b->category,
                'lat'         => (float) $b->lat,
                'lng'         => (float) $b->lng,
                'is_verified' => (bool) $b->is_verified,
                'has_menu'    => (bool) $b->has_menu,
                'is_promoted' => (bool) ($b->promoted_until && $b->promoted_until->isFuture()),
                'rating_avg'  => (float) $b->rating_avg,
                'phone'       => $b->phone,
            ])->values()->all();
        });

        // Live events (not category-filtered — they're a separate layer)
        $events = \Illuminate\Support\Facades\Cache::remember('map-events:v1', 120, function () {
            return \App\Models\Event::query()
                ->where('status', 'active')
                ->whereNotNull('lat')->whereNotNull('lng')
                ->where(function ($q) {
                    $q->where('starts_at', '>=', now()->subHours(6))
                      ->orWhere('ends_at', '>=', now());
                })
                ->select('id', 'title', 'kind', 'lat', 'lng', 'starts_at', 'location')
                ->limit(200)
                ->get()
                ->map(fn ($e) => [
                    'id'        => (int) $e->id,
                    'title'     => (string) $e->title,
                    'kind'      => (string) $e->kind,
                    'lat'       => (float) $e->lat,
                    'lng'       => (float) $e->lng,
                    'starts_at' => $e->starts_at?->toIso8601String(),
                    'location'  => $e->location,
                ])
                ->values()->all();
        });

        return response()->json([
            'businesses' => array_values((array) $businesses),
            'events'     => array_values((array) $events),
            'categories' => Business::CATEGORIES,
        ]);
    }

    /** Track a contact click (phone or whatsapp). Returns 204 — used by JS beacon. */
    public function trackClick(Business $business, Request $request)
    {
        $kind = $request->query('kind');
        if ($kind === 'phone')    $business->increment('phone_clicks');
        if ($kind === 'whatsapp') $business->increment('whatsapp_clicks');
        return response()->noContent();
    }

    /** Owner analytics dashboard for a single business. */
    public function stats(Business $business)
    {
        $this->authorizeOwner($business);
        $business->loadCount('reviews');
        return view('directory.stats', compact('business'));
    }

    private function authorizeOwner(Business $business): void
    {
        if (! Auth::check()) abort(403);
        $u = Auth::user();
        if ($u->is_admin) return;
        if ($business->owner_user_id !== $u->id) abort(403);
    }

    private function validateBusiness(Request $request): array
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'min:3', 'max:120'],
            'sub_type'        => ['required', 'in:'.implode(',', array_keys(Business::SUB_TYPES))],
            'custom_sub_type' => ['nullable', 'string', 'max:80'],
            'zone_id'         => ['required', 'exists:zones,id'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'phone'           => ['nullable', 'regex:/^01[0125][0-9]{8}$/'],
            'whatsapp'        => ['nullable', 'regex:/^01[0125][0-9]{8}$/'],
            'address'         => ['nullable', 'string', 'max:200'],
            'hours'           => ['nullable', 'string', 'max:100'],
            'is_24h'          => ['nullable', 'boolean'],
            'lat'             => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng'             => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'photo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'phone.regex'    => 'لازم رقم موبايل مصري صحيح.',
            'whatsapp.regex' => 'لازم رقم واتساب مصري صحيح.',
            'photo.image'    => 'الملف لازم يكون صورة.',
            'photo.mimes'    => 'لازم JPG / PNG / WEBP.',
            'photo.max'      => 'حجم الصورة لازم أقل من ٣ ميجا.',
            'lat.required_with' => 'حدّد المكان كامل من على الخريطة.',
            'lng.required_with' => 'حدّد المكان كامل من على الخريطة.',
        ]);

        // Only keep custom_sub_type when an "_other" type was picked
        if (! str_ends_with($data['sub_type'], '_other')) {
            $data['custom_sub_type'] = null;
        } elseif (empty(trim($data['custom_sub_type'] ?? ''))) {
            throw ValidationException::withMessages([
                'custom_sub_type' => 'اكتب نوع نشاطك بالظبط.',
            ]);
        }

        return $data;
    }

    private function handlePhotoUpload(Request $request, ?string $oldUrl = null): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        // Delete old
        if ($oldUrl && str_starts_with($oldUrl, '/storage/')) {
            $relative = ltrim(str_replace('/storage/', '', $oldUrl), '/');
            Storage::disk('public')->delete($relative);
        }

        $path = $request->file('photo')->store('businesses', 'public');
        return '/storage/'.$path;
    }
}
