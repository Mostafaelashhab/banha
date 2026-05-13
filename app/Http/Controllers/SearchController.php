<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Listing;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '' || mb_strlen($q) < 2) {
            return view('search.index', [
                'q' => $q,
                'posts' => collect(),
                'businesses' => collect(),
                'listings' => collect(),
                'users' => collect(),
            ]);
        }

        $like = "%{$q}%";

        $posts = Post::query()
            ->where('status', 'active')
            ->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)->orWhere('body', 'like', $like);
            })
            ->with(['user:id,username,avatar_seed,avatar_url,verification_tier,is_admin,last_seen_at', 'zone:id,name'])
            ->latest()
            ->limit(15)
            ->get();

        $businesses = Business::query()
            ->where('is_active', true)
            ->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                  ->orWhere('description', 'like', $like)
                  ->orWhere('address', 'like', $like)
                  ->orWhere('custom_sub_type', 'like', $like);
            })
            ->orderByDesc('is_verified')
            ->orderByDesc('rating_avg')
            ->with('zone:id,name')
            ->limit(15)
            ->get();

        $listings = Listing::query()
            ->where('status', 'active')
            ->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)->orWhere('description', 'like', $like);
            })
            ->with('zone:id,name')
            ->latest()
            ->limit(10)
            ->get();

        $users = User::query()
            ->where('is_banned', false)
            ->where('username', 'like', $like)
            ->limit(8)
            ->get();

        return view('search.index', compact('q', 'posts', 'businesses', 'listings', 'users'));
    }

    /**
     * Lightweight JSON for the home-page live suggest dropdown.
     * Trims fields to what the UI shows (id, name, url, photo, meta).
     */
    public function suggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['businesses' => [], 'listings' => [], 'q' => $q]);
        }
        $like = '%'.$q.'%';

        $businesses = Business::query()
            ->where('is_active', true)
            ->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                  ->orWhere('custom_sub_type', 'like', $like)
                  ->orWhere('address', 'like', $like);
            })
            ->orderByDesc('is_verified')
            ->orderByDesc('rating_avg')
            ->with('zone:id,name')
            ->limit(6)
            ->get(['id', 'name', 'category', 'sub_type', 'zone_id', 'photo_url', 'is_verified', 'rating_avg'])
            ->map(fn ($b) => [
                'id'        => $b->id,
                'name'      => $b->name,
                'url'       => route('directory.show', $b->id),
                'photo'     => $b->photo_url,
                'category'  => $b->categoryMeta()['label'] ?? null,
                'cat_color' => $b->categoryMeta()['color'] ?? '#2D5BFF',
                'zone'      => $b->zone?->name,
                'verified'  => (bool) $b->is_verified,
                'rating'    => $b->rating_avg ? round((float) $b->rating_avg, 1) : null,
            ]);

        $listings = Listing::query()
            ->where('status', 'active')
            ->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)->orWhere('description', 'like', $like);
            })
            ->with('zone:id,name')
            ->latest()
            ->limit(4)
            ->get(['id', 'title', 'price', 'currency', 'photo_url', 'zone_id', 'kind'])
            ->map(fn ($l) => [
                'id'    => $l->id,
                'title' => $l->title,
                'url'   => route('marketplace.show', $l->id),
                'photo' => $l->photo_url,
                'price' => $l->priceLabel(),
                'zone'  => $l->zone?->name,
                'kind'  => $l->kindMeta()['label'] ?? null,
            ]);

        return response()->json([
            'q'          => $q,
            'businesses' => $businesses,
            'listings'   => $listings,
        ]);
    }
}
