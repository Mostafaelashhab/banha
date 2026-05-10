<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Business;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Price;
use App\Models\PushSubscription;
use App\Models\Report;
use App\Models\User;
use App\Models\Zone;
use App\Services\PushService;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $today      = now()->startOfDay();
        $yesterday  = now()->subDay()->startOfDay();
        $weekAgo    = now()->subDays(7);

        // Helper: count today vs yesterday and compute % delta
        $delta = function (string $model, string $col = 'created_at') use ($today, $yesterday) {
            $t = $model::where($col, '>=', $today)->count();
            $y = $model::whereBetween($col, [$yesterday, $today])->count();
            $pct = $y > 0 ? round((($t - $y) / $y) * 100) : ($t > 0 ? 100 : 0);
            return ['today' => $t, 'yesterday' => $y, 'pct' => $pct];
        };

        $usersDelta  = $delta(User::class);
        $postsDelta  = $delta(Post::class);
        $alertsDelta = $delta(Alert::class);
        $pricesDelta = $delta(Price::class);

        $stats = [
            'users'           => User::count(),
            'users_today'     => $usersDelta['today'],
            'users_pct'       => $usersDelta['pct'],
            'users_banned'    => User::where('is_banned', true)->count(),
            'users_verified'  => User::whereIn('verification_tier', ['silver', 'gold'])->count(),
            'posts'           => Post::count(),
            'posts_today'     => $postsDelta['today'],
            'posts_pct'       => $postsDelta['pct'],
            'posts_flagged'   => Post::where('status', 'flagged')->count(),
            'comments'        => Comment::count(),
            'alerts_active'   => Alert::active()->count(),
            'alerts_today'    => $alertsDelta['today'],
            'alerts_pct'      => $alertsDelta['pct'],
            'alerts_verified' => Alert::where('is_verified', true)->count(),
            'businesses'      => Business::where('is_active', true)->count(),
            'biz_pending'     => Business::where('is_active', true)->where('is_verified', false)->whereNotNull('owner_user_id')->count(),
            'reports_open'    => Report::where('status', 'open')->count(),
            'prices'          => Price::count(),
            'prices_today'    => $pricesDelta['today'],
            'prices_pct'      => $pricesDelta['pct'],
            'prices_week'     => Price::where('created_at', '>=', $weekAgo)->count(),
            'subs_total'      => PushSubscription::count(),
        ];

        // Helper: last 7 days filled with 0s for missing days
        $sevenDaySeries = function (string $model) {
            $rows = $model::query()
                ->select(DB::raw('DATE(created_at) as d'), DB::raw('count(*) as c'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('d')->orderBy('d')->pluck('c', 'd')->all();

            $series = [];
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i)->format('Y-m-d');
                $series[] = ['d' => $day, 'c' => (int) ($rows[$day] ?? 0)];
            }
            return $series;
        };

        $charts = [
            'users'  => $sevenDaySeries(User::class),
            'posts'  => $sevenDaySeries(Post::class),
            'alerts' => $sevenDaySeries(Alert::class),
            'prices' => $sevenDaySeries(Price::class),
        ];

        // Top zones with relative bars
        $topZones = Zone::query()
            ->withCount(['posts' => function ($q) { $q->where('status', 'active'); }])
            ->orderByDesc('posts_count')
            ->limit(6)
            ->get();
        $maxZonePosts = max($topZones->max('posts_count') ?: 0, 1);

        // Mixed activity timeline (last 12 events)
        $events = collect();

        foreach (User::latest()->limit(8)->get() as $u) {
            $events->push([
                'type'  => 'signup',
                'at'    => $u->created_at,
                'icon'  => 'user', 'tone' => 'coral',
                'title' => $u->username.' سجّل حساب',
                'sub'   => $u->phone.' · '.($u->zone?->name ?? '—'),
                'url'   => route('profile.show', $u->username),
            ]);
        }
        foreach (Post::latest()->limit(8)->get() as $p) {
            $events->push([
                'type'  => 'post',
                'at'    => $p->created_at,
                'icon'  => 'flame', 'tone' => 'honey',
                'title' => ($p->is_anonymous ? '🤫 بوست مجهول' : ($p->user?->username.' نشر بوست')),
                'sub'   => \Illuminate\Support\Str::limit($p->title ?: $p->body, 70),
                'url'   => route('posts.show', $p),
            ]);
        }
        foreach (Alert::latest()->limit(6)->get() as $a) {
            $meta = $a->typeMeta();
            $events->push([
                'type'  => 'alert',
                'at'    => $a->created_at,
                'icon'  => $meta['icon'], 'tone' => $meta['tone'],
                'title' => $meta['label'].' في '.($a->zone?->name ?? '—'),
                'sub'   => \Illuminate\Support\Str::limit($a->description, 70),
                'url'   => route('alerts.show', $a),
            ]);
        }
        foreach (Business::latest()->limit(5)->get() as $b) {
            $events->push([
                'type'  => 'business',
                'at'    => $b->created_at,
                'icon'  => 'bag', 'tone' => 'mint',
                'title' => $b->name.' انضاف للدليل',
                'sub'   => ($b->subTypeMeta()['label'] ?? '').' · '.($b->zone?->name ?? '—'),
                'url'   => route('directory.show', $b),
            ]);
        }
        foreach (Report::where('status', 'open')->latest()->limit(5)->get() as $r) {
            $events->push([
                'type'  => 'report',
                'at'    => $r->created_at,
                'icon'  => 'flag', 'tone' => 'blush',
                'title' => 'بلاغ: '.$r->reason,
                'sub'   => $r->target_type.' #'.$r->target_id,
                'url'   => route('admin.reports'),
            ]);
        }

        $timeline = $events->sortByDesc('at')->take(12)->values();

        return view('admin.dashboard', [
            'stats'        => $stats,
            'charts'       => $charts,
            'topZones'     => $topZones,
            'maxZonePosts' => $maxZonePosts,
            'timeline'     => $timeline,
        ]);
    }

    public function users(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $filter = $request->query('filter');

        $users = User::query()
            ->when($q !== '', fn ($w) => $w->where(function ($x) use ($q) {
                $x->where('username', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            }))
            ->when($filter === 'banned',   fn ($w) => $w->where('is_banned', true))
            ->when($filter === 'verified', fn ($w) => $w->whereIn('verification_tier', ['silver', 'gold']))
            ->when($filter === 'admins',   fn ($w) => $w->where('is_admin', true))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.users', compact('users', 'q', 'filter'));
    }

    public function userBan(User $user)
    {
        if ($user->is_admin) abort(403, 'Cannot ban admin');
        $user->update(['is_banned' => ! $user->is_banned]);
        return back()->with('flash', $user->is_banned ? 'تم حظر '.$user->username : 'تم رفع الحظر عن '.$user->username);
    }

    /**
     * Per-user points audit page. Shows the full transaction log + summary
     * + manual award/penalty form. The route is admin-only via middleware.
     */
    public function userPoints(User $user, Request $request)
    {
        $txs = \App\Models\PointTransaction::where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate(50);

        // Top counter for quick fraud detection: spikes per reason
        $byReason = \DB::table('point_transactions')
            ->where('user_id', $user->id)
            ->selectRaw('reason, count(*) as c, sum(delta) as s')
            ->groupBy('reason')
            ->orderByDesc('c')
            ->get();

        return view('admin.user-points', compact('user', 'txs', 'byReason'));
    }

    /**
     * Admin: hand-award (or penalize) a user. Goes through PointsService so
     * the audit log captures the admin's IP + the reason note.
     */
    public function userPointsAward(User $user, Request $request)
    {
        $data = $request->validate([
            'delta' => ['required', 'integer', 'between:-5000,5000'],
            'note'  => ['nullable', 'string', 'max:200'],
        ]);
        \App\Services\PointsService::award(
            $user,
            'admin_award',
            null,
            $data['delta'],
            ['note' => $data['note'] ?? null, 'by_admin' => auth()->id()]
        );
        return back()->with('flash', "تم تطبيق {$data['delta']} نقطة على {$user->username}");
    }

    /** Admin: revoke a single transaction (writes a negating row). */
    public function userPointsRevoke(\App\Models\PointTransaction $tx, Request $request)
    {
        \App\Services\PointsService::revoke($tx, $request->input('note'));
        return back()->with('flash', 'تم إلغاء العملية.');
    }

    /** Admin: withdrawal queue. Filter by status (default pending). */
    public function withdrawals(Request $request)
    {
        $status = $request->query('status', 'pending');
        $withdrawals = \App\Models\Withdrawal::query()
            ->with('user:id,username,phone,verification_tier,reputation')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(30);

        $counts = \DB::table('withdrawals')->selectRaw('status, count(*) as c')
            ->groupBy('status')->pluck('c', 'status')->all();

        return view('admin.withdrawals', compact('withdrawals', 'status', 'counts'));
    }

    public function withdrawalApprove(\App\Models\Withdrawal $withdrawal, Request $request)
    {
        \App\Services\WithdrawalService::approve($withdrawal, auth()->user(), $request->input('note'));
        return back()->with('flash', '✓ الطلب اتعمد عليه. ابعت الفلوس ثم اضغط "اتدفع".');
    }

    public function withdrawalMarkPaid(\App\Models\Withdrawal $withdrawal, Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:64'],
        ]);
        \App\Services\WithdrawalService::markPaid($withdrawal, $data['reference']);
        return back()->with('flash', '✓ تم تسجيل الدفع.');
    }

    public function withdrawalReject(\App\Models\Withdrawal $withdrawal, Request $request)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:200'],
        ]);
        \App\Services\WithdrawalService::reject($withdrawal, auth()->user(), $data['reason']);
        return back()->with('flash', 'تم رفض الطلب.');
    }

    public function userTier(User $user, Request $request)
    {
        $tier = $request->input('tier');
        if (! in_array($tier, ['none','bronze','silver','gold'], true)) abort(422);

        $user->update(['verification_tier' => $tier, 'verified_at' => $tier === 'none' ? null : now()]);
        return back()->with('flash', 'الـ tier اتغير لـ '.$tier);
    }

    public function userAdmin(User $user)
    {
        $user->update(['is_admin' => ! $user->is_admin]);
        return back()->with('flash', $user->is_admin ? $user->username.' بقى أدمن' : $user->username.' مش أدمن');
    }

    public function posts(Request $request)
    {
        $status = $request->query('status', 'active');
        $posts = Post::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['user:id,username', 'zone:id,name'])
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.posts', compact('posts', 'status'));
    }

    public function postRemove(Post $post)
    {
        $post->update(['status' => 'removed']);
        return back()->with('flash', 'البوست اتشال.');
    }

    public function postRestore(Post $post)
    {
        $post->update(['status' => 'active', 'flag_count' => 0]);
        return back()->with('flash', 'البوست رجع.');
    }

    public function reports(Request $request)
    {
        $status = $request->query('status', 'open');
        $reports = Report::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        // Eager-load report targets
        $postIds = $reports->where('target_type', 'post')->pluck('target_id');
        $posts   = Post::whereIn('id', $postIds)->with('user:id,username')->get()->keyBy('id');

        $reporterIds = $reports->pluck('reporter_id');
        $reporters   = User::whereIn('id', $reporterIds)->get(['id','username'])->keyBy('id');

        return view('admin.reports', compact('reports', 'status', 'posts', 'reporters'));
    }

    public function reportResolve(Report $report, Request $request)
    {
        $action = $request->input('action', 'dismiss'); // dismiss | remove_post | ban_user

        if ($action === 'remove_post' && $report->target_type === 'post') {
            $post = Post::find($report->target_id);
            if ($post) {
                $post->update(['status' => 'removed']);
                // bump valid_reports for the post owner
                if ($post->user_id) {
                    User::where('id', $post->user_id)->increment('valid_reports_count');
                }
            }
        }

        if ($action === 'ban_user' && $report->target_type === 'post') {
            $post = Post::find($report->target_id);
            if ($post && $post->user_id) {
                User::where('id', $post->user_id)->update(['is_banned' => true]);
            }
        }

        $report->update(['status' => $action === 'dismiss' ? 'dismissed' : 'resolved']);

        return back()->with('flash', 'تم اتخاذ الإجراء.');
    }

    public function businesses(Request $request)
    {
        $filter = $request->query('filter');
        $businesses = Business::query()
            ->when($filter === 'pending',   fn ($q) => $q->where('is_verified', false)->where('is_active', true)->whereNotNull('owner_user_id'))
            ->when($filter === 'verified',  fn ($q) => $q->where('is_verified', true))
            ->when($filter === 'inactive',  fn ($q) => $q->where('is_active', false))
            ->with(['zone:id,name', 'owner:id,username'])
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.businesses', compact('businesses', 'filter'));
    }

    public function businessVerify(Business $business)
    {
        $wasVerified = (bool) $business->is_verified;
        $business->update(['is_verified' => ! $wasVerified]);

        // Reward the owner only on the upgrade path (not on every toggle)
        if (! $wasVerified && $business->owner_user_id) {
            \App\Services\PointsService::award($business->owner, 'business_verified', $business);
        }

        return back()->with('flash', $business->is_verified ? 'النشاط اتوثّق.' : 'تم رفع التوثيق.');
    }

    public function businessToggleActive(Business $business)
    {
        $business->update(['is_active' => ! $business->is_active]);
        return back()->with('flash', $business->is_active ? 'النشاط اترجع.' : 'النشاط اتقفل.');
    }

    /** Admin: extend (or set) the business promotion by N days. days=0 cancels promotion. */
    public function businessPromote(Business $business, Request $request)
    {
        $days = (int) $request->input('days', 7);
        if ($days <= 0) {
            $business->update(['promoted_until' => null]);
            $this->bustMapCache();
            return back()->with('flash', 'تم إلغاء الترويج.');
        }
        $start = ($business->promoted_until && $business->promoted_until->isFuture())
            ? $business->promoted_until
            : now();
        $business->update(['promoted_until' => $start->copy()->addDays($days)]);
        $this->bustMapCache();
        return back()->with('flash', "تم الترويج لمدة {$days} يوم — لحد ".$business->promoted_until->translatedFormat('d M Y'));
    }

    /** Forget all map-data caches so the new promotion shows up immediately. */
    private function bustMapCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('map-data:v4:all');
        \Illuminate\Support\Facades\Cache::forget('map-events:v1');
        foreach (array_keys(\App\Models\Business::CATEGORIES) as $cat) {
            \Illuminate\Support\Facades\Cache::forget('map-data:v4:'.$cat);
        }
    }

    /** Admin: same for marketplace listings. */
    public function listingFeature(\App\Models\Listing $listing, Request $request)
    {
        $days = (int) $request->input('days', 7);
        if ($days <= 0) {
            $listing->update(['featured_until' => null]);
            return back()->with('flash', 'تم إلغاء التمييز.');
        }
        $start = ($listing->featured_until && $listing->featured_until->isFuture())
            ? $listing->featured_until
            : now();
        $listing->update(['featured_until' => $start->copy()->addDays($days)]);
        return back()->with('flash', "تم التمييز لمدة {$days} يوم");
    }

    public function broadcastForm()
    {
        return view('admin.broadcast', [
            'zones'       => Zone::orderBy('sort')->get(),
            'subsCount'   => PushSubscription::count(),
            'subsByZone'  => PushSubscription::query()
                ->join('users', 'users.id', '=', 'push_subscriptions.user_id')
                ->select('users.zone_id', DB::raw('count(*) as c'))
                ->groupBy('users.zone_id')
                ->pluck('c', 'zone_id'),
        ]);
    }

    public function broadcastSend(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'body'  => ['required', 'string', 'max:200'],
            'url'   => ['nullable', 'string', 'max:255'],
            'zone'  => ['nullable', 'integer', 'exists:zones,id'],
        ]);

        $payload = [
            'title' => $data['title'],
            'body'  => $data['body'],
            'url'   => $data['url'] ?: '/feed',
            'tag'   => 'broadcast-'.now()->timestamp,
        ];

        $result = $data['zone']
            ? PushService::sendToZone($data['zone'], $payload)
            : PushService::sendToSubscriptions(PushSubscription::all(), $payload);

        return back()->with('flash', "تم الإرسال — وصل لـ {$result['sent']} جهاز · فشل {$result['failed']} · مسحنا {$result['pruned']}.");
    }

    /**
     * Admin form to publish an official infrastructure outage (electricity / water).
     * Creates an Alert (verified=true) and pushes a notification to subscribers in
     * the affected zone(s).
     */
    public function outageForm()
    {
        return view('admin.outage', [
            'zones'      => Zone::orderBy('sort')->get(),
            'recent'     => \App\Models\Alert::active()
                ->whereIn('type', ['electricity', 'water'])
                ->where('is_verified', true)
                ->with('zone:id,name')
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function outageStore(Request $request)
    {
        $data = $request->validate([
            'type'        => ['required', 'in:electricity,water'],
            'zone_id'     => ['nullable', 'exists:zones,id'],
            'description' => ['required', 'string', 'min:8', 'max:500'],
            'starts_at'   => ['nullable', 'date'],
            'ends_at'     => ['nullable', 'date', 'after_or_equal:starts_at'],
            'send_push'   => ['nullable', 'boolean'],
        ], [
            'description.min' => 'اكتب تفاصيل أوضح (التوقيت + الشارع/المنطقة).',
        ]);

        // Build a clean description that includes the schedule when given
        $body = trim($data['description']);
        if (! empty($data['starts_at'])) {
            $start = \Carbon\Carbon::parse($data['starts_at'])->translatedFormat('D j M · H:i');
            $end   = ! empty($data['ends_at'])
                ? \Carbon\Carbon::parse($data['ends_at'])->translatedFormat('H:i')
                : null;
            $body  = $body . "\nالتوقيت: {$start}" . ($end ? " إلى {$end}" : '');
        }

        // Expire when the outage ends (default 24h)
        $expiresAt = ! empty($data['ends_at'])
            ? \Carbon\Carbon::parse($data['ends_at'])->addHours(2)
            : now()->addHours(24);

        $alert = \App\Models\Alert::create([
            'user_id'      => Auth::id(),
            'zone_id'      => $data['zone_id'] ?? null,
            'type'         => $data['type'],
            'description'  => $body,
            'is_verified'  => true,
            'is_resolved'  => false,
            'confirmations'=> 0,
            'expires_at'   => $expiresAt,
        ]);

        // Push to relevant subscribers
        $sent = $failed = 0;
        if ($request->boolean('send_push', true)) {
            $payload = [
                'title' => $data['type'] === 'electricity' ? 'انقطاع كهرباء' : 'انقطاع مياه',
                'body'  => mb_strlen($body) > 140 ? mb_substr($body, 0, 137).'…' : $body,
                'url'   => route('alerts.show', $alert),
                'tag'   => 'outage-'.$alert->id,
            ];
            $result = $data['zone_id']
                ? PushService::sendToZone($data['zone_id'], $payload)
                : PushService::sendToSubscriptions(PushSubscription::all(), $payload);
            $sent   = $result['sent']   ?? 0;
            $failed = $result['failed'] ?? 0;
        }

        return back()->with('flash',
            "تم النشر — وصل لـ {$sent} جهاز" . ($failed ? " · فشل {$failed}" : '') . '.'
        );
    }

    public function outageResolve(\App\Models\Alert $alert)
    {
        $alert->update(['is_resolved' => true]);
        return back()->with('flash', 'تم إنهاء التنبيه.');
    }

    public function recheckTiers()
    {
        $count = 0;
        foreach (User::where('verification_tier', 'bronze')->get() as $u) {
            if (VerificationService::recheckSilver($u)) $count++;
        }
        return back()->with('flash', "تم ترقية {$count} مستخدم لـ Silver.");
    }
}
