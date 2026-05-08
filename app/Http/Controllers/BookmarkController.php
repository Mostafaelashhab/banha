<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Business;
use App\Models\Listing;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function toggle(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:post,business,listing'],
            'id'   => ['required', 'integer'],
        ]);

        $userId = Auth::id();
        $existing = Bookmark::where('user_id', $userId)
            ->where('target_type', $data['type'])
            ->where('target_id', $data['id'])
            ->first();

        if ($existing) {
            $existing->delete();
            $saved = false;
        } else {
            Bookmark::create([
                'user_id'     => $userId,
                'target_type' => $data['type'],
                'target_id'   => $data['id'],
                'created_at'  => now(),
            ]);
            $saved = true;
        }

        if ($request->wantsJson()) {
            return response()->json(['saved' => $saved]);
        }
        return back()->with('flash', $saved ? '✓ تم الحفظ' : 'اتشال من المحفوظات');
    }

    public function index()
    {
        $userId = Auth::id();
        $bookmarks = Bookmark::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('target_type');

        $posts = Post::with(['user:id,username,avatar_seed,avatar_url,verification_tier', 'zone:id,name'])
            ->whereIn('id', $bookmarks->get('post', collect())->pluck('target_id'))
            ->where('status', 'active')
            ->get();

        $businesses = Business::with('zone:id,name')
            ->whereIn('id', $bookmarks->get('business', collect())->pluck('target_id'))
            ->where('is_active', true)
            ->get();

        $listings = Listing::with('zone:id,name')
            ->whereIn('id', $bookmarks->get('listing', collect())->pluck('target_id'))
            ->where('status', 'active')
            ->get();

        return view('bookmarks.index', compact('posts', 'businesses', 'listings'));
    }
}
