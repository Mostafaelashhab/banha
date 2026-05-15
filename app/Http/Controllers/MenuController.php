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
            'description'  => ['nullable', 'string', 'max:500'],
            'price'        => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'is_available' => ['nullable', 'boolean'],
            'photo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = ImageUploader::store($request->file('photo'), 'menu');
        }

        MenuItem::create([
            'business_id'  => $business->id,
            'category_id'  => $data['category_id'] ?? null,
            'name'         => $data['name'],
            'description'  => $data['description'] ?? null,
            'price'        => $data['price'] ?? null,
            'photo_url'    => $photo,
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
            'name'         => ['required', 'string', 'max:120'],
            'description'  => ['nullable', 'string', 'max:500'],
            'price'        => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'is_available' => ['nullable', 'boolean'],
            'photo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $newPhoto = $item->photo_url;
        if ($request->hasFile('photo')) {
            $newPhoto = ImageUploader::store($request->file('photo'), 'menu', $item->photo_url);
        }

        $item->update([
            'name'         => $data['name'],
            'description'  => $data['description'] ?? null,
            'price'        => $data['price'] ?? null,
            'is_available' => (bool) ($data['is_available'] ?? true),
            'photo_url'    => $newPhoto,
        ]);
        return back()->with('flash', 'تم التعديل.');
    }

    public function toggleItem(MenuItem $item)
    {
        $this->authorizeOwner($item->business);
        $item->update(['is_available' => ! $item->is_available]);
        return back();
    }

    public function destroyItem(MenuItem $item)
    {
        $this->authorizeOwner($item->business);
        ImageUploader::delete($item->photo_url);
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
            $rows = \App\Models\Area::whereIn('id', $coveredIds)
                ->orderBy('parent')->orderBy('sort')->orderBy('name')
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

        return view('menu.public', compact(
            'business', 'looseItems', 'deliveryAreas', 'userDefaultAreaId'
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
