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
}
