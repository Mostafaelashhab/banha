<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Zone;
use App\Services\BadgeService;
use App\Services\PushService;
use App\Support\EmergencyHotlines;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $type   = $request->query('type');
        $zoneId = $request->query('zone');

        $alerts = Alert::active()
            ->with(['user:id,username', 'zone:id,name'])
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($zoneId, fn ($q) => $q->where('zone_id', $zoneId))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $myConfirms = Auth::check() && $alerts->isNotEmpty()
            ? DB::table('alert_confirmations')
                ->where('user_id', Auth::id())
                ->whereIn('alert_id', $alerts->pluck('id'))
                ->pluck('alert_id')
                ->all()
            : [];

        // Counts per type for filter chips
        $typeCounts = Alert::active()
            ->select('type', DB::raw('count(*) as c'))
            ->groupBy('type')
            ->pluck('c', 'type')
            ->all();

        $hotlines = $type
            ? EmergencyHotlines::forAlertType($type)
            : EmergencyHotlines::all();

        return view('alerts.index', [
            'alerts'     => $alerts,
            'types'      => Alert::TYPES,
            'typeCounts' => $typeCounts,
            'activeType' => $type,
            'zones'      => Zone::orderBy('sort')->get(),
            'activeZone' => $zoneId ? (int) $zoneId : null,
            'myConfirms' => $myConfirms,
            'hotlines'   => $hotlines,
        ]);
    }

    public function create()
    {
        return view('alerts.create', [
            'types' => Alert::TYPES,
            'zones' => Zone::orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $key = 'alert:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'description' => 'هدّي شوية — مش أكتر من ٣ تنبيهات في الدقيقة.',
            ]);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'type'        => ['required', 'in:'.implode(',', array_keys(Alert::TYPES))],
            'description' => ['required', 'string', 'min:5', 'max:280'],
            'zone_id'     => ['required', 'exists:zones,id'],
            'lat'         => ['nullable', 'numeric', 'between:-90,90'],
            'lng'         => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $alert = Alert::create([
            'user_id'     => Auth::id(),
            'zone_id'     => $data['zone_id'],
            'type'        => $data['type'],
            'description' => $data['description'],
            'lat'         => $data['lat'] ?? null,
            'lng'         => $data['lng'] ?? null,
            'expires_at'  => now()->addHours(Alert::DEFAULT_TTL_HOURS),
        ]);

        BadgeService::onAlertSubmit(Auth::user());
        \App\Services\AdminNotificationService::onAlertCreated($alert->fresh()->load('zone'));

        // Push to zone subscribers (excluding the author)
        if ($alert->zone_id) {
            $alert->load('zone:id,name');
            $meta = $alert->typeMeta();
            PushService::sendToZone($alert->zone_id, [
                'title' => $meta['icon'] === 'flame' ? '🔥 ' : ($meta['icon'] === 'bolt' ? '⚡ ' : '⚠️ ').'تنبيه جديد · '.($alert->zone->name ?? 'حيك'),
                'body'  => $meta['label'].': '.\Illuminate\Support\Str::limit($alert->description, 90),
                'url'   => route('alerts.show', $alert),
                'tag'   => 'alert-'.$alert->id,
            ]);
        }

        return redirect()->route('alerts.show', $alert)->with('flash', 'تنبيهك اتنشر — شكراً.');
    }

    public function show(Alert $alert)
    {
        if ($alert->is_resolved && $alert->user_id !== Auth::id()) {
            abort(404);
        }

        $alert->load(['user:id,username,avatar_seed', 'zone:id,name']);

        $myConfirmed = Auth::check() && \Illuminate\Support\Facades\DB::table('alert_confirmations')
            ->where('alert_id', $alert->id)
            ->where('user_id', Auth::id())
            ->exists();

        $hotlines = EmergencyHotlines::forAlertType($alert->type);

        $related = Alert::active()
            ->where('id', '!=', $alert->id)
            ->where('zone_id', $alert->zone_id)
            ->latest()
            ->limit(5)
            ->get();

        return view('alerts.show', compact('alert', 'myConfirmed', 'hotlines', 'related'));
    }

    public function confirm(Alert $alert)
    {
        if ($alert->is_resolved || $alert->expires_at < now()) {
            return back()->with('flash', 'التنبيه ده انتهى.');
        }
        if ($alert->user_id === Auth::id()) {
            return back()->with('flash', 'مش هتأكّد على تنبيهك.');
        }

        $created = DB::table('alert_confirmations')->insertOrIgnore([
            'alert_id'   => $alert->id,
            'user_id'    => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($created) {
            $alert->increment('confirmations');
            $alert->refresh();
            if ($alert->confirmations >= 3 && ! $alert->is_verified) {
                $alert->is_verified = true;
                $alert->expires_at  = now()->addHours(Alert::DEFAULT_TTL_HOURS * 2);
                $alert->save();

                $owner = $alert->user;
                if ($owner) {
                    $owner->increment('reputation', 10);
                    $owner->refresh();
                    BadgeService::onAlertVerified($owner);
                    BadgeService::onReputationChange($owner);
                }

                // Push to zone subscribers (excluding the alert author)
                if ($alert->zone_id) {
                    $meta = $alert->typeMeta();
                    PushService::sendToZone($alert->zone_id, [
                        'title' => '⚠️ تنبيه موثّق · '.($alert->zone->name ?? 'حيك'),
                        'body'  => $meta['label'].': '.\Illuminate\Support\Str::limit($alert->description, 90),
                        'url'   => route('alerts.index', ['type' => $alert->type]),
                        'tag'   => 'alert-'.$alert->id,
                    ]);
                }
            }
        }

        return back()->with('flash', 'شكراً للتأكيد.');
    }

    public function resolve(Alert $alert)
    {
        if ($alert->user_id !== Auth::id()) {
            abort(403);
        }
        $alert->update(['is_resolved' => true]);
        return back()->with('flash', 'تم تأشير التنبيه كـ منتهي.');
    }
}
