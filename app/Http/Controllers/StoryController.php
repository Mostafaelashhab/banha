<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class StoryController extends Controller
{
    public const TTL_HOURS    = 24;
    public const MAX_PER_USER = 5;  // hard cap per user per 24h

    public function index()
    {
        // Group stories by user (most recent per user first)
        $stories = Story::alive()
            ->with('user:id,username,avatar_seed,avatar_url')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('user_id');

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
        $key = 'story:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['image' => 'هدّي شوية — مش أكتر من ٥ ستوريز في ساعة.']);
        }

        // Hard cap: max 5 alive stories per user
        $alive = Story::where('user_id', Auth::id())->where('expires_at', '>', now())->count();
        if ($alive >= self::MAX_PER_USER) {
            throw ValidationException::withMessages(['image' => 'وصلت للحد الأقصى ('.self::MAX_PER_USER.' ستوريز). استنى لما واحدة تخلص.']);
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

        RateLimiter::hit($key, 3600);

        return redirect()->route('stories.index')->with('flash', '✓ ستوريك انتشر');
    }

    public function destroy(Story $story)
    {
        if ($story->user_id !== Auth::id() && ! Auth::user()->is_admin) abort(403);
        ImageUploader::delete($story->image_url);
        $story->delete();
        return back()->with('flash', 'الستوري اتمسحت');
    }

    public function create()
    {
        return view('stories.create');
    }
}
