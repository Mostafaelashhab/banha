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
                ->filter(fn ($m) => ($m['category'] ?? null) === 'craftsmen')
                ->map(fn ($m, $key) => ['key' => $key, 'label' => $m['label'], 'emoji' => $m['emoji'] ?? '🔧'])
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
                ->filter(fn ($m) => ($m['category'] ?? null) === 'craftsmen')
                ->map(fn ($m, $key) => ['key' => $key, 'label' => $m['label'], 'emoji' => $m['emoji'] ?? '🔧'])
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
            'jobRequest', 'myEligibleBusinesses', 'existingResponse'
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
            ['quoted_price' => $data['quoted_price'] ?? null, 'note' => $data['note'] ?? null]
        );

        // Count distinct responses (first time only)
        if ($response->wasRecentlyCreated) {
            $jobRequest->increment('responses_count');
        }

        // Build WhatsApp link for the craftsman to message the user directly
        $waPhone = preg_replace('/\D/', '', $jobRequest->phone);
        if (str_starts_with($waPhone, '0')) $waPhone = '2' . $waPhone;
        $price = $data['quoted_price'] ? number_format($data['quoted_price']) . ' ج' : 'بنتفق';
        $msg = "السلام عليكم 👋\nبرد على طلبك من بنها.shop:\n\n"
             . "📋 " . mb_substr($jobRequest->description, 0, 80) . "\n"
             . "👷 " . $business->name . "\n"
             . "💰 السعر المبدأي: " . $price
             . ($data['note'] ? "\n📝 " . $data['note'] : '')
             . "\n\n(الرد عبر بنها.shop · #{$jobRequest->id})";
        $waLink = 'https://wa.me/' . $waPhone . '?text=' . urlencode($msg);

        // Record the contact moment
        $response->update(['contacted_at' => now()]);

        return redirect()->away($waLink);
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
                    try { PushService::sendToUser($uid, $payload); }
                    catch (\Throwable $e) { /* one bad sub shouldn't block the rest */ }
                }
            }
        } catch (\Throwable $e) {
            // Notification failures are non-fatal — the job is still on the public feed.
            Log::warning('JobRequest notify failed: ' . $e->getMessage());
        }
    }
}
