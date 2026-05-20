<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\JobRequest;
use App\Models\JobResponse;
use App\Models\Zone;
use App\Services\PushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class JobRequestController extends Controller
{
    public function create()
    {
        return view('craftsmen.post-job', [
            'trades' => collect(Business::SUB_TYPES)
                ->filter(fn($m) => ($m['category'] ?? null) === 'craftsmen')
                ->map(fn($m, $key) => ['key' => $key, 'label' => $m['label'], 'emoji' => $m['emoji'] ?? '🔧'])
                ->values()->all(),
            'zones'  => Zone::where('is_active', true)->orderBy('sort')->get(),
            'user'   => Auth::user(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'min:3', 'max:80'],
            'phone'       => ['required', 'regex:/^01[0125][0-9]{8}$/'],
            'sub_type'    => ['required', 'string'],
            'zone_id'     => ['required', 'exists:zones,id'],
            'address'     => ['nullable', 'string', 'max:200'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'budget_min'  => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'budget_max'  => ['nullable', 'integer', 'min:0', 'max:1000000', 'gte:budget_min'],
            'urgency'     => ['required', Rule::in(array_keys(JobRequest::URGENCIES))],
        ], [
            'phone.regex' => 'رقم الموبايل لازم يكون مصري صحيح.',
            'budget_max.gte' => 'الحد الأقصى لازم أكبر من الأدنى.',
        ]);

        $meta = Business::SUB_TYPES[$data['sub_type']] ?? null;
        abort_unless($meta && ($meta['category'] ?? null) === 'craftsmen', 422);

        $job = JobRequest::create($data + [
            'user_id'    => Auth::id(),
            'status'     => 'open',
            // Auto-expire jobs after 7 days so the feed stays fresh.
            'expires_at' => now('Africa/Cairo')->addDays(7),
        ]);

        // Fan-out: notify every craftsman in matching trade who serves this zone.
        $this->notifyMatching($job);

        return redirect()->route('craft-jobs.show', $job)
            ->with('flash', '✓ تم نشر طلبك. الصنايعية اللي في تخصصك هيكلموك قريب.');
    }

    public function index(Request $request)
    {
        $tradeFilter = $request->query('trade');
        $zoneFilter  = (int) $request->query('zone');

        $q = JobRequest::open()
            ->with('zone')
            ->orderByRaw("FIELD(urgency, 'asap', 'today', 'this_week', 'flexible')")
            ->orderByDesc('created_at');

        if ($tradeFilter) $q->forTrade($tradeFilter);
        if ($zoneFilter)  $q->forZone($zoneFilter);

        $jobs = $q->limit(60)->get();

        // Count by status for context (open is the primary one we list)
        $openCount = JobRequest::open()->count();

        return view('craftsmen.jobs', [
            'jobs'        => $jobs,
            'openCount'   => $openCount,
            'tradeFilter' => $tradeFilter,
            'zoneFilter'  => $zoneFilter,
            'trades'      => collect(Business::SUB_TYPES)
                ->filter(fn($m) => ($m['category'] ?? null) === 'craftsmen')
                ->map(fn($m, $key) => ['key' => $key, 'label' => $m['label'], 'emoji' => $m['emoji'] ?? '🔧'])
                ->values()->all(),
            'zones'       => Zone::where('is_active', true)->orderBy('sort')->get(),
        ]);
    }

    public function show(JobRequest $jobRequest)
    {
        $jobRequest->increment('views_count');
        $jobRequest->load('zone', 'matchedBusiness');

        $user = Auth::user();
        // If the viewer owns a craftsman business in the matching trade → enable "Respond"
        $myEligibleBusinesses = $user
            ? Business::where('owner_user_id', $user->id)
            ->where('category', 'craftsmen')
            ->where('sub_type', $jobRequest->sub_type)
            ->where('is_active', true)
            ->get()
            : collect();

        $existingResponse = $myEligibleBusinesses->isNotEmpty()
            ? JobResponse::where('job_request_id', $jobRequest->id)
            ->whereIn('business_id', $myEligibleBusinesses->pluck('id'))
            ->first()
            : null;

        return view('craftsmen.job-show', compact(
            'jobRequest',
            'myEligibleBusinesses',
            'existingResponse'
        ));
    }

    public function respond(JobRequest $jobRequest, Request $request)
    {
        abort_unless($jobRequest->status === 'open', 422, 'الطلب مش متاح للرد.');

        $data = $request->validate([
            'business_id'   => ['required', 'exists:businesses,id'],
            'quoted_price'  => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'note'          => ['nullable', 'string', 'max:300'],
        ]);

        $business = Business::findOrFail($data['business_id']);
        abort_unless($business->owner_user_id === Auth::id(), 403);
        abort_unless($business->sub_type === $jobRequest->sub_type, 422, 'النشاط لازم يكون في نفس التخصص.');

        $response = JobResponse::updateOrCreate(
            ['job_request_id' => $jobRequest->id, 'business_id' => $business->id],
            [
                'quoted_price' => $data['quoted_price'] ?? null,
                'note'         => $data['note'] ?? null,
                'contacted_at' => now(),
            ]
        );

        if ($response->wasRecentlyCreated) {
            $jobRequest->increment('responses_count');
        }

        // Fan out two WhatsApp messages (Banhawy → customer, Banhawy → craftsman)
        // + two push notifications. Failures here are non-fatal — the response
        // is already saved, so the user can always re-open the job to see it.
        $this->fanOutResponse($jobRequest, $business, $response);

        return redirect()->route('craft-jobs.show', $jobRequest)
            ->with('flash', '✓ تم إرسال ردك. العميل هيوصله مسج واتساب ونوتيفيكيشن بتفاصيلك دلوقتي.');
    }

    /**
     * Notify both sides about a new response.
     *  • Customer gets WAAPI with the craftsman's details + push (if registered).
     *  • Craftsman gets WAAPI with the customer's details + push.
     */
    private function fanOutResponse(JobRequest $job, Business $business, JobResponse $response): void
    {
        $price = $response->quoted_price ? number_format($response->quoted_price) . ' ج' : 'بنتفق على السعر';
        $note  = $response->note ? "\n📝 " . $response->note : '';
        $descShort = mb_substr($job->description, 0, 90) . (mb_strlen($job->description) > 90 ? '…' : '');
        $jobUrl = url('/craftsmen/jobs/' . $job->id);

        // ── 1) WAAPI → customer ──
        $msgToCustomer = "السلام عليكم 👋\n"
            . "صنايعي رد على طلبك على *بنهاوي*\n\n"
            . "📋 طلبك: {$descShort}\n"
            . "👷 الصنايعي: *{$business->name}*\n"
            . "📱 رقمه: {$business->phone}\n"
            . ($business->whatsapp && $business->whatsapp !== $business->phone ? "💬 واتساب: {$business->whatsapp}\n" : '')
            . "💰 سعر مبدأي: {$price}"
            . $note
            . "\n\nشوف التفاصيل: {$jobUrl}";
        $this->safeWaapi($job->phone, $msgToCustomer);

        // ── 2) WAAPI → craftsman ──
        $craftsmanWa = $business->whatsapp ?: $business->phone;
        if ($craftsmanWa) {
            $msgToCraftsman = "السلام عليكم 👋\n"
                . "تم تسجيل ردك على طلب شغل #{$job->id} على *بنهاوي*\n\n"
                . "📋 الطلب: {$descShort}\n"
                . "👤 العميل: *{$job->name}*\n"
                . "📱 رقمه: {$job->phone}\n"
                . "📍 المنطقة: " . ($job->zone->name ?? 'بنها')
                . ($job->address ? "\n📌 العنوان: {$job->address}" : '')
                . "\n⚡ الاستعجال: " . $job->urgencyLabel()
                . "\n\nاتصل بالعميل دلوقتي قبل ما حد يسبقك.\nالطلب: {$jobUrl}";
            $this->safeWaapi($craftsmanWa, $msgToCraftsman);
        }

        // ── 3) Push → craftsman (responder) ──
        if ($business->owner_user_id && class_exists(PushService::class) && PushService::isConfigured()) {
            try {
                PushService::sendToUser((int) $business->owner_user_id, [
                    'title' => '✓ تم تسجيل ردك',
                    'body'  => 'الطلب: ' . mb_substr($job->description, 0, 60),
                    'url'   => '/craftsmen/jobs/' . $job->id,
                    'tag'   => 'jobresp-' . $response->id . '-c',
                ]);
            } catch (\Throwable $e) {
            }
        }

        // ── 4) Push → customer (only if they have an account here) ──
        if ($job->user_id && class_exists(PushService::class) && PushService::isConfigured()) {
            try {
                PushService::sendToUser((int) $job->user_id, [
                    'title' => '🛠️ صنايعي رد على طلبك',
                    'body'  => $business->name . ' · ' . $price,
                    'url'   => '/craftsmen/jobs/' . $job->id,
                    'tag'   => 'jobresp-' . $response->id . '-u',
                ]);
            } catch (\Throwable $e) {
            }
        }
    }

    private function safeWaapi(?string $phone, string $message): void
    {
        if (! $phone) return;
        try {
            \App\Services\WaapiService::send($phone, $message);
        } catch (\Throwable $e) {
            Log::warning('JobResponse WAAPI failed: ' . $e->getMessage());
        }
    }

    /** Send a push notification to all craftsmen matching the job's trade + zone. */
    private function notifyMatching(JobRequest $job): void
    {
        try {
            $zoneId = (int) $job->zone_id;

            // Match either home zone == job zone OR job zone in service_zones JSON
            $matches = Business::query()
                ->where('category', 'craftsmen')
                ->where('sub_type', $job->sub_type)
                ->where('is_active', true)
                ->whereNotNull('owner_user_id')
                ->where(function ($w) use ($zoneId) {
                    $w->where('zone_id', $zoneId)
                        ->orWhereRaw('JSON_CONTAINS(service_zones, ?)', [(string) $zoneId])
                        ->orWhereRaw('JSON_CONTAINS(service_zones, ?)', ['"' . $zoneId . '"']);
                })
                ->pluck('owner_user_id')->unique()->values();

            if ($matches->isEmpty()) return;

            $meta  = $job->tradeMeta();
            $title = '🛠️ شغلانة جديدة في تخصصك';
            $body  = ($meta['label'] ?? 'صنايعي') . ' · ' . ($job->zone->name ?? 'بنها') . ' — ' . $job->urgencyLabel();
            $url   = '/craftsmen/jobs/' . $job->id;

            // PushService::sendToUser is the existing helper for web-push subscribers
            if (class_exists(PushService::class) && PushService::isConfigured()) {
                $payload = [
                    'title' => $title,
                    'body'  => $body,
                    'url'   => $url,
                    'tag'   => 'job-' . $job->id,
                ];
                foreach ($matches as $uid) {
                    try {
                        PushService::sendToUser($uid, $payload);
                    } catch (\Throwable $e) { /* one bad sub shouldn't block the rest */
                    }
                }
            }
        } catch (\Throwable $e) {
            // Notification failures are non-fatal — the job is still on the public feed.
            Log::warning('JobRequest notify failed: ' . $e->getMessage());
        }
    }
}
