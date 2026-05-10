<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\Comment;
use App\Models\Listing;
use App\Models\Post;
use App\Models\User;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show(Request $request, ?string $username = null)
    {
        $user = $username
            ? User::where('username', $username)->firstOrFail()
            : Auth::user();

        $isMe = Auth::check() && Auth::id() === $user->id;
        $tab  = $request->query('tab', 'posts');

        $user->load(['zone', 'badges']);

        $stats = [
            'posts'      => Post::where('user_id', $user->id)->where('status', 'active')->count(),
            'comments'   => Comment::where('user_id', $user->id)->where('status', 'active')->count(),
            'listings'   => Listing::where('user_id', $user->id)->where('status', 'active')->count(),
            'reputation' => (int) $user->reputation,
            'days'       => $user->daysActive(),
        ];

        $posts = Post::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) use ($isMe) {
                if (! $isMe) $q->where('is_anonymous', false);
            })
            ->latest()
            ->limit(20)
            ->get();

        $listings = $tab === 'listings'
            ? Listing::with('zone:id,name')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->latest()
                ->paginate(20)
                ->withQueryString()
            : null;

        $earnedSlugs   = $user->badges->pluck('slug')->all();
        $allBadges     = Badge::orderBy('sort')->get();
        $earnedBadges  = $allBadges->whereIn('slug', $earnedSlugs)->values();
        $lockedBadges  = $allBadges->whereNotIn('slug', $earnedSlugs)->where('is_secret', false)->values();

        $silverProgress = $isMe ? VerificationService::silverProgress($user) : null;

        return view('profile', [
            'user'           => $user,
            'isMe'           => $isMe,
            'tab'            => $tab,
            'stats'          => $stats,
            'posts'          => $posts,
            'listings'       => $listings,
            'earnedBadges'   => $earnedBadges,
            'lockedBadges'   => $lockedBadges,
            'silverProgress' => $silverProgress,
        ]);
    }
}
