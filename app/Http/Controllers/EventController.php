<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Zone;
use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $kind = $request->query('kind');

        $upcoming = Event::query()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('starts_at', '>=', now())
                  ->orWhere('ends_at', '>=', now());
            })
            ->when($kind, fn ($q) => $q->where('kind', $kind))
            ->with(['user:id,username,avatar_seed,avatar_url', 'zone:id,name'])
            ->orderBy('starts_at')
            ->paginate(20)
            ->withQueryString();

        return view('events.index', [
            'events'     => $upcoming,
            'kinds'      => Event::KINDS,
            'activeKind' => $kind,
        ]);
    }

    public function show(Event $event)
    {
        if ($event->status !== 'active' && Auth::id() !== $event->user_id) abort(404);
        $event->load(['user:id,username,avatar_seed,avatar_url', 'zone:id,name']);
        $isAttending = Auth::check() && DB::table('event_attendees')
            ->where('event_id', $event->id)->where('user_id', Auth::id())->exists();
        return view('events.show', compact('event', 'isAttending'));
    }

    public function create()
    {
        return view('events.create', [
            'kinds' => Event::KINDS,
            'zones' => Zone::orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $key = 'event:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages(['title' => 'مش هتقدر تضيف أكتر من ٣ أحداث في الساعة.']);
        }
        RateLimiter::hit($key, 3600);

        $data = $request->validate([
            'kind'          => ['required', 'in:'.implode(',', array_keys(Event::KINDS))],
            'title'         => ['required', 'string', 'min:3', 'max:150'],
            'description'   => ['nullable', 'string', 'max:2000'],
            'location'      => ['nullable', 'string', 'max:200'],
            'starts_at'     => ['required', 'date', 'after_or_equal:now'],
            'ends_at'       => ['nullable', 'date', 'after_or_equal:starts_at'],
            'zone_id'       => ['nullable', 'exists:zones,id'],
            'contact_phone' => ['nullable', 'regex:/^01[0125][0-9]{8}$/'],
            'cover'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $cover = null;
        if ($request->hasFile('cover')) {
            $cover = ImageUploader::store($request->file('cover'), 'events');
        }

        $event = Event::create([
            'user_id'       => Auth::id(),
            'zone_id'       => $data['zone_id'] ?? Auth::user()->zone_id,
            'kind'          => $data['kind'],
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'location'      => $data['location'] ?? null,
            'starts_at'     => $data['starts_at'],
            'ends_at'       => $data['ends_at'] ?? null,
            'cover_url'     => $cover,
            'contact_phone' => $data['contact_phone'] ?? null,
            'status'        => 'active',
        ]);

        return redirect()->route('events.show', $event)->with('flash', 'الحدث اتنشر! 🎉');
    }

    public function attend(Event $event)
    {
        DB::table('event_attendees')->upsert(
            [['event_id' => $event->id, 'user_id' => Auth::id(), 'created_at' => now()]],
            ['event_id', 'user_id'], ['created_at']
        );
        $event->update(['attendees_count' => DB::table('event_attendees')->where('event_id', $event->id)->count()]);
        return back()->with('flash', '✓ هتحضر');
    }

    public function unattend(Event $event)
    {
        DB::table('event_attendees')
            ->where('event_id', $event->id)->where('user_id', Auth::id())->delete();
        $event->update(['attendees_count' => DB::table('event_attendees')->where('event_id', $event->id)->count()]);
        return back();
    }

    public function destroy(Event $event)
    {
        if ($event->user_id !== Auth::id() && ! Auth::user()->is_admin) abort(403);
        ImageUploader::delete($event->cover_url);
        $event->update(['status' => 'cancelled']);
        return redirect()->route('events.index')->with('flash', 'تم إلغاء الحدث.');
    }
}
