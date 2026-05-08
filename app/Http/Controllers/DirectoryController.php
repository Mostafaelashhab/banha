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
        if (! $business->is_active && (! Auth::check() || Auth::id() !== $business->owner_user_id)) {
            abort(404);
        }

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
            'name'          => $data['name'],
            'category'      => $sm['category'],
            'sub_type'      => $data['sub_type'],
            'zone_id'       => $data['zone_id'],
            'owner_user_id' => Auth::id(),
            'description'   => $data['description'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'whatsapp'      => $data['whatsapp'] ?? null,
            'address'       => $data['address'] ?? null,
            'hours'         => $data['hours'] ?? null,
            'is_24h'        => (bool) ($data['is_24h'] ?? false),
            'is_verified'   => false,
            'is_active'     => true,
            'emoji'         => $sm['emoji'],
            'photo_url'     => $photoUrl,
        ]);

        \App\Services\AdminNotificationService::onBusinessCreated($business->fresh()->load('owner'));

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
            'name'        => $data['name'],
            'category'    => $sm['category'],
            'sub_type'    => $data['sub_type'],
            'zone_id'     => $data['zone_id'],
            'description' => $data['description'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'whatsapp'    => $data['whatsapp'] ?? null,
            'address'     => $data['address'] ?? null,
            'hours'       => $data['hours'] ?? null,
            'is_24h'      => (bool) ($data['is_24h'] ?? false),
            'emoji'       => $sm['emoji'],
            'photo_url'   => $newPhoto ?: $business->photo_url,
        ]);

        return redirect()->route('directory.show', $business)
            ->with('flash', '✓ تم تحديث النشاط.');
    }

    public function destroy(Business $business)
    {
        $this->authorizeOwner($business);
        $business->update(['is_active' => false]);
        return redirect()->route('directory.mine')->with('flash', 'تم حذف النشاط.');
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
        return $request->validate([
            'name'        => ['required', 'string', 'min:3', 'max:120'],
            'sub_type'    => ['required', 'in:'.implode(',', array_keys(Business::SUB_TYPES))],
            'zone_id'     => ['required', 'exists:zones,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'phone'       => ['nullable', 'regex:/^01[0125][0-9]{8}$/'],
            'whatsapp'    => ['nullable', 'regex:/^01[0125][0-9]{8}$/'],
            'address'     => ['nullable', 'string', 'max:200'],
            'hours'       => ['nullable', 'string', 'max:100'],
            'is_24h'      => ['nullable', 'boolean'],
            'photo'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'phone.regex'    => 'لازم رقم موبايل مصري صحيح.',
            'whatsapp.regex' => 'لازم رقم واتساب مصري صحيح.',
            'photo.image'    => 'الملف لازم يكون صورة.',
            'photo.mimes'    => 'لازم JPG / PNG / WEBP.',
            'photo.max'      => 'حجم الصورة لازم أقل من ٣ ميجا.',
        ]);
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
