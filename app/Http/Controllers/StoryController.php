<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoryController extends Controller
{
    public const TTL_HOURS    = 24;
    public const MAX_PER_USER = 10;  // hard cap per user per 24h

    public function index()
    {
        // Show every active story as its own tile (most recent first).
        // My own stories come first so I can manage them easily.
        $myId = Auth::id();
        $stories = Story::alive()
            ->with('user:id,username,avatar_seed,avatar_url')
            ->orderByRaw('user_id = ? DESC', [$myId ?: 0])
            ->orderByDesc('created_at')
            ->get();

        return view('stories.index', compact('stories'));
    }

    public function show(Story $story)
    {
        if ($story->expires_at->isPast()) abort(404);
        $story->load('user:id,username,avatar_seed,avatar_url');

        if (Auth::check() && Auth::id() !== $story->user_id) {
            $exists = DB::table('story_views')
                ->where('story_id', $story->id)
                ->where('user_id', Auth::id())
                ->exists();
            if (! $exists) {
                DB::table('story_views')->insert([
                    'story_id'  => $story->id,
                    'user_id'   => Auth::id(),
                    'viewed_at' => now(),
                ]);
                $story->increment('views_count');
            }
        }

        return view('stories.show', compact('story'));
    }

    public function store(Request $request)
    {
        // Hard cap: max alive stories per user (no rate limiter — alive count is enough)
        $alive = Story::where('user_id', Auth::id())->where('expires_at', '>', now())->count();
        if ($alive >= self::MAX_PER_USER) {
            throw ValidationException::withMessages([
                'image' => 'وصلت للحد الأقصى ('.self::MAX_PER_USER.' ستوريز نشطة). استنى لما واحدة تخلص (٢٤ ساعة) أو احذف واحدة.',
            ]);
        }

        $request->validate([
            'image'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:200'],
        ]);

        $url = ImageUploader::store($request->file('image'), 'stories');
        if (! $url) return back()->withErrors(['image' => 'فشل رفع الصورة.']);

        Story::create([
            'user_id'    => Auth::id(),
            'image_url'  => $url,
            'caption'    => $request->input('caption'),
            'expires_at' => now()->addHours(self::TTL_HOURS),
            'created_at' => now(),
        ]);

        return redirect()->route('stories.index')->with('flash', '✓ ستوريك انتشر');
    }

    /** Owner-only: list of users who viewed this story. */
    public function viewers(Story $story)
    {
        if ($story->user_id !== Auth::id() && ! Auth::user()->is_admin) abort(403);

        $viewers = DB::table('story_views')
            ->join('users', 'users.id', '=', 'story_views.user_id')
            ->where('story_views.story_id', $story->id)
            ->orderByDesc('story_views.viewed_at')
            ->select('users.id', 'users.username', 'users.avatar_seed', 'users.avatar_url', 'users.verification_tier', 'story_views.viewed_at')
            ->limit(200)
            ->get();

        return view('stories.viewers', compact('story', 'viewers'));
    }

    public function destroy(Story $story)
    {
        if ($story->user_id !== Auth::id() && ! Auth::user()->is_admin) abort(403);
        ImageUploader::delete($story->image_url);
        $story->delete();
        return redirect()->route('stories.index')->with('flash', 'الستوري اتمسحت');
    }

    public function create()
    {
        $alive = Story::where('user_id', Auth::id())->where('expires_at', '>', now())->count();
        return view('stories.create', [
            'alive' => $alive,
            'max'   => self::MAX_PER_USER,
        ]);
    }
}
