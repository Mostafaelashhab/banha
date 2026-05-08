<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Models\Vote;
use App\Services\BadgeService;
use App\Support\AnonSeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    public function show(Post $post, Request $request)
    {
        if ($post->status !== 'active' && (! Auth::check() || Auth::id() !== $post->user_id)) {
            abort(404);
        }

        $post->load(['user:id,username,avatar_seed', 'zone:id,name']);
        $comments = $post->comments()
            ->with('user:id,username,avatar_seed')
            ->where('status', 'active')
            ->latest()
            ->get();

        $userVote = Auth::check()
            ? Vote::where('user_id', Auth::id())->where('post_id', $post->id)->value('value')
            : null;

        return view('posts.show', compact('post', 'comments', 'userVote'));
    }

    public function create()
    {
        return view('posts.create', [
            'categories' => Post::CATEGORIES,
            'zones'      => \App\Models\Zone::orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $key = 'post:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'body' => 'هدّي شوية — مش أكتر من ٥ بوستات في الدقيقة.',
            ]);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'category'     => ['required', 'in:'.implode(',', array_keys(Post::CATEGORIES))],
            'title'        => ['nullable', 'string', 'max:180'],
            'body'         => ['required', 'string', 'min:3', 'max:2000'],
            'zone_id'      => ['nullable', 'exists:zones,id'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $isAnon = (bool) ($data['is_anonymous'] ?? false);

        $post = Post::create([
            'user_id'      => Auth::id(),
            'zone_id'      => $data['zone_id'] ?? Auth::user()->zone_id,
            'is_anonymous' => $isAnon,
            'anon_seed'    => $isAnon ? AnonSeed::generate() : null,
            'category'     => $data['category'],
            'title'        => $data['title'] ?? null,
            'body'         => $data['body'],
            'status'       => 'active',
        ]);

        $post->recomputeHotScore();
        $post->save();

        BadgeService::onPost(Auth::user());

        return redirect()->route('posts.show', $post)->with('flash', 'بوستك اتنشر! 🎉');
    }

    public function vote(Post $post, Request $request)
    {
        $value = (int) $request->input('value');
        if (! in_array($value, [-1, 0, 1], true)) {
            abort(422);
        }

        $userId = Auth::id();
        $now    = now();

        DB::transaction(function () use ($post, $userId, $value, $now) {
            $prev = (int) (DB::table('votes')
                ->where('user_id', $userId)
                ->where('post_id', $post->id)
                ->value('value') ?? 0);

            if ($value === 0) {
                DB::table('votes')
                    ->where('user_id', $userId)
                    ->where('post_id', $post->id)
                    ->delete();
            } else {
                DB::table('votes')->upsert(
                    [[
                        'user_id'    => $userId,
                        'post_id'    => $post->id,
                        'value'      => $value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]],
                    ['user_id', 'post_id'],
                    ['value', 'updated_at']
                );
            }

            $upDelta   = (int) ($value === 1)  - (int) ($prev === 1);
            $downDelta = (int) ($value === -1) - (int) ($prev === -1);

            if ($upDelta)   $post->increment('upvotes',   $upDelta);
            if ($downDelta) $post->increment('downvotes', $downDelta);

            $post->refresh();
            $post->recomputeHotScore();
            $post->save();

            // Reputation: +5 per net upvote, -2 per net downvote (don't reward self)
            $repDelta = ($upDelta * 5) + ($downDelta * -2);
            if ($repDelta !== 0 && $post->user_id !== $userId) {
                $owner = User::find($post->user_id);
                if ($owner) {
                    $owner->increment('reputation', $repDelta);
                    $owner->refresh();
                    BadgeService::onReputationChange($owner);
                    BadgeService::onPost($owner);
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'upvotes'   => $post->upvotes,
                'downvotes' => $post->downvotes,
                'score'     => $post->score(),
                'value'     => $value,
            ]);
        }

        return back();
    }

    public function comment(Post $post, Request $request)
    {
        $key = 'comment:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages([
                'body' => 'كومنتات كتير قوي — استنى دقيقة.',
            ]);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'body'         => ['required', 'string', 'min:1', 'max:1000'],
            'is_anonymous' => ['nullable', 'boolean'],
            'parent_id'    => ['nullable', 'exists:comments,id'],
        ]);

        $isAnon = (bool) ($data['is_anonymous'] ?? false);

        Comment::create([
            'post_id'      => $post->id,
            'user_id'      => Auth::id(),
            'parent_id'    => $data['parent_id'] ?? null,
            'is_anonymous' => $isAnon,
            'anon_seed'    => $isAnon ? AnonSeed::generate() : null,
            'body'         => $data['body'],
        ]);

        $post->increment('comments_count');

        BadgeService::onComment(Auth::user());

        return back()->with('flash', 'كومنتك انضاف.');
    }

    public function report(Post $post, Request $request)
    {
        $data = $request->validate([
            'reason'  => ['required', 'in:spam,abuse,nsfw,fake,other'],
            'details' => ['nullable', 'string', 'max:500'],
        ]);

        Report::firstOrCreate(
            [
                'reporter_id' => Auth::id(),
                'target_type' => 'post',
                'target_id'   => $post->id,
            ],
            [
                'reason'  => $data['reason'],
                'details' => $data['details'] ?? null,
                'status'  => 'open',
            ]
        );

        $post->increment('flag_count');
        if ($post->flag_count >= 5) {
            $post->status = 'flagged';
            $post->save();
        }

        return back()->with('flash', 'تم استلام البلاغ، شكراً.');
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }
        $post->update(['status' => 'removed']);
        return redirect()->route('feed')->with('flash', 'تم حذف البوست.');
    }
}
