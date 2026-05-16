<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    /** Owner: manage menu (categories + items in one screen). */
    public function manage(Business $business)
    {
        $this->authorizeOwner($business);

        $business->load(['menuCategories.items']);

        // Items not attached to any category — so we can render them in their own block
        $looseItems = MenuItem::where('business_id', $business->id)
            ->whereNull('category_id')
            ->orderBy('sort')->orderBy('id')
            ->get();

        return view('menu.manage', compact('business', 'looseItems'));
    }

    public function storeCategory(Business $business, Request $request)
    {
        $this->authorizeOwner($business);
        $data = $request->validate(['name' => ['required', 'string', 'max:80']]);

        MenuCategory::create([
            'business_id' => $business->id,
            'name'        => $data['name'],
            'sort'        => $business->menuCategories()->count(),
        ]);
        $this->markHasMenu($business);
        return back()->with('flash', 'القسم اتضاف.');
    }

    public function destroyCategory(MenuCategory $category)
    {
        $this->authorizeOwner($category->business);
        $category->delete();
        return back()->with('flash', 'القسم اتمسح.');
    }

    public function storeItem(Business $business, Request $request)
    {
        $this->authorizeOwner($business);
        $data = $request->validate([
            'category_id'  => ['nullable', 'exists:menu_categories,id'],
            'name'         => ['required', 'string', 'max:120'],
            'description'  => ['nullable', 'string', 'max:2000'],
            'price'        => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'capacity'     => ['nullable', 'integer', 'min:1', 'max:255'],
            'features'     => ['nullable', 'string'],
            'is_available' => ['nullable', 'boolean'],
            'photo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photos'       => ['nullable', 'array', 'max:10'],
            'photos.*'     => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $cover = null;
        if ($request->hasFile('photo')) {
            $cover = ImageUploader::store($request->file('photo'), 'menu');
        }

        $gallery = [];
        foreach ((array) $request->file('photos', []) as $file) {
            if ($url = ImageUploader::store($file, 'menu')) {
                $gallery[] = $url;
            }
        }

        MenuItem::create([
            'business_id'  => $business->id,
            'category_id'  => $data['category_id'] ?? null,
            'name'         => $data['name'],
            'description'  => $data['description'] ?? null,
            'price'        => $data['price'] ?? null,
            'capacity'     => $data['capacity'] ?? null,
            'photo_url'    => $cover,
            'photos'       => $gallery ?: null,
            'features'     => $this->parseFeatures($data['features'] ?? null),
            'is_available' => (bool) ($data['is_available'] ?? true),
            'sort'         => $business->menuItems()->where('category_id', $data['category_id'] ?? null)->count(),
        ]);
        $this->markHasMenu($business);
        return back()->with('flash', '✓ الصنف اتضاف.');
    }

    public function updateItem(MenuItem $item, Request $request)
    {
        $this->authorizeOwner($item->business);
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:120'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'price'          => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'capacity'       => ['nullable', 'integer', 'min:1', 'max:255'],
            'features'       => ['nullable', 'string'],
            'is_available'   => ['nullable', 'boolean'],
            'photo'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photos'         => ['nullable', 'array', 'max:10'],
            'photos.*'       => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_photos'  => ['nullable', 'array'],
            'remove_photos.*'=> ['string'],
        ]);

        $cover = $item->photo_url;
        if ($request->hasFile('photo')) {
            $cover = ImageUploader::store($request->file('photo'), 'menu', $item->photo_url);
        }

        // Gallery: keep existing minus removed, plus newly uploaded.
        $existing = (array) ($item->photos ?? []);
        $toRemove = (array) ($data['remove_photos'] ?? []);
        $keptGallery = [];
        foreach ($existing as $url) {
            if (in_array($url, $toRemove, true)) {
                ImageUploader::delete($url);
            } else {
                $keptGallery[] = $url;
            }
        }
        foreach ((array) $request->file('photos', []) as $file) {
            if ($url = ImageUploader::store($file, 'menu')) {
                $keptGallery[] = $url;
            }
        }

        $item->update([
            'name'         => $data['name'],
            'description'  => $data['description'] ?? null,
            'price'        => $data['price'] ?? null,
            'capacity'     => $data['capacity'] ?? null,
            'is_available' => (bool) ($data['is_available'] ?? true),
            'photo_url'    => $cover,
            'photos'       => $keptGallery ?: null,
            'features'     => $this->parseFeatures($data['features'] ?? null),
        ]);
        return back()->with('flash', 'تم التعديل.');
    }

    /**
     * Accepts a JSON string from the form (e.g. '[{"icon":"wifi","label":"واي فاي"}]')
     * and returns a cleaned array, or null if empty/invalid.
     */
    private function parseFeatures(?string $raw): ?array
    {
        if (! $raw) return null;
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) return null;

        $out = [];
        foreach ($decoded as $row) {
            if (! is_array($row)) continue;
            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '') continue;
            $icon = preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($row['icon'] ?? '')));
            $out[] = [
                'icon'  => $icon ?: 'tag',
                'label' => mb_substr($label, 0, 40),
            ];
            if (count($out) >= 12) break;
        }
        return $out ?: null;
    }

    public function toggleItem(MenuItem $item)
    {
        $this->authorizeOwner($item->business);
        $item->update(['is_available' => ! $item->is_available]);
        return back();
    }

    /**
     * Owner: update business-level features (used for non-hotel categories
     * where features describe the whole place, not each menu item).
     */
    public function updateFeatures(Business $business, Request $request)
    {
        $this->authorizeOwner($business);
        $request->validate([
            'features' => ['nullable', 'string'],
        ]);
        $business->update([
            'features' => $this->parseFeatures($request->input('features')),
        ]);
        return back()->with('flash', 'تم تحديث المميزات.');
    }

    public function destroyItem(MenuItem $item)
    {
        $this->authorizeOwner($item->business);
        ImageUploader::delete($item->photo_url);
        foreach ((array) ($item->photos ?? []) as $url) {
            ImageUploader::delete($url);
        }
        $item->delete();
        return back()->with('flash', 'تم الحذف.');
    }

    /** Public QR menu page — fast + SEO-friendly. */
    public function publicMenu(Business $business)
    {
        if (! $business->is_active) abort(404);

        $business->load([
            'zone:id,name',
            'menuCategories' => fn ($q) => $q->orderBy('sort')->orderBy('id'),
            'menuCategories.items' => fn ($q) => $q->where('is_available', true)->orderBy('sort')->orderBy('id'),
        ]);

        // Items without category (loose)
        $looseItems = $business->menuItems()
            ->whereNull('category_id')
            ->where('is_available', true)
            ->get();

        // Areas this business delivers to (with their per-area fee).
        // The cart UI uses this for the area picker + auto-detect.
        $deliveryAreas = collect();
        $userDefaultAreaId = optional(Auth::user())->default_area_id;
        if ($business->offersDelivery()) {
            $coveredIds = array_map('intval', array_keys((array) $business->delivery_fees));
            // Hide any stale non-Banha fees the owner may have set before coverage was paused.
            $rows = \App\Models\Area::whereIn('id', $coveredIds)
                ->banha()
                ->orderBy('sort')->orderBy('name')
                ->get();
            $fees = (array) $business->delivery_fees;
            $deliveryAreas = $rows->map(fn ($a) => [
                'id'     => $a->id,
                'name'   => $a->name,
                'parent' => $a->parent,
                'fee'    => (float) ($fees[(string) $a->id] ?? $fees[$a->id] ?? 0),
                'lat'    => $a->lat ? (float) $a->lat : null,
                'lng'    => $a->lng ? (float) $a->lng : null,
            ])->values();
        }

        // "اطلب تاني" flash from MyOrdersController::reorder — list of
        // {id, qty} pairs. Items get matched against the rendered menu's
        // data-item-id attributes so we always use current prices.
        $reorderRequest = session('reorder_request');

        return view('menu.public', compact(
            'business', 'looseItems', 'deliveryAreas', 'userDefaultAreaId',
            'reorderRequest'
        ));
    }

    private function authorizeOwner(Business $business): void
    {
        $u = Auth::user();
        if (! $u) abort(401);
        if (! $u->is_admin && $business->owner_user_id !== $u->id) abort(403);
    }

    private function markHasMenu(Business $business): void
    {
        if (! $business->has_menu) {
            $business->update(['has_menu' => true]);
        }
    }
}
