<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    public function toggle(User $user, Request $request)
    {
        if ($user->id === Auth::id()) abort(422, 'cannot follow self');

        $exists = DB::table('user_follows')
            ->where('follower_id', Auth::id())
            ->where('followed_id', $user->id)
            ->exists();

        if ($exists) {
            DB::table('user_follows')
                ->where('follower_id', Auth::id())
                ->where('followed_id', $user->id)
                ->delete();
            $following = false;
        } else {
            DB::table('user_follows')->insert([
                'follower_id' => Auth::id(),
                'followed_id' => $user->id,
                'created_at'  => now(),
            ]);
            $following = true;

            // Notify followed user
            \App\Services\PushService::sendToUser($user->id, [
                'title' => '👤 '.Auth::user()->username.' بقا بيتابعك',
                'body'  => 'افتح بروفايلك وشوفه.',
                'url'   => route('profile.show', Auth::user()->username),
                'tag'   => 'follow-'.Auth::id(),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['following' => $following]);
        }
        return back();
    }

    public function followingFeed()
    {
        $followedIds = DB::table('user_follows')
            ->where('follower_id', Auth::id())
            ->pluck('followed_id');

        if ($followedIds->isEmpty()) {
            return view('feed-following', [
                'posts'     => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'userVotes' => [],
                'empty'     => true,
            ]);
        }

        $posts = Post::active()
            ->whereIn('user_id', $followedIds)
            ->where('is_anonymous', false)
            ->with(['user:id,username,avatar_seed,avatar_url,verification_tier,is_admin', 'zone:id,name', 'poll'])
            ->latest()
            ->paginate(20);

        $userVotes = Vote::where('user_id', Auth::id())
            ->whereIn('post_id', $posts->pluck('id'))
            ->pluck('value', 'post_id')
            ->all();

        return view('feed-following', compact('posts', 'userVotes'));
    }
}
