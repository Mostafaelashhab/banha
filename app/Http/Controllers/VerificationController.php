<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\VerificationPayment;
use App\Services\ImageUploader;
use App\Services\PushService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Paid-verified-badge flow.
 *
 *   Owner side:
 *     GET  /directory/business/{business}/verify-badge   → pricing + payment instructions
 *     POST same                                          → submit proof (transaction id + screenshot)
 *
 *   Admin side:
 *     GET  /admin/verifications                          → queue of pending submissions
 *     POST /admin/verifications/{payment}/approve        → flip is_verified_paid, push the owner
 *     POST /admin/verifications/{payment}/reject         → reject with reason
 */
class VerificationController extends Controller
{
    public function show(Business $business)
    {
        $this->authorizeOwner($business);

        $pending = VerificationPayment::where('business_id', $business->id)
            ->pending()
            ->latest()
            ->first();

        return view('craftsmen.verify-badge', [
            'business'       => $business,
            'price'          => (int) config('verification.price_egp'),
            'months'         => (int) config('verification.duration_months'),
            'instapay'       => config('verification.instapay_handle'),
            'vodafone'       => config('verification.vodafone_number'),
            'cashWa'         => config('verification.cash_whatsapp'),
            'pending'        => $pending,
            'methods'        => VerificationPayment::METHODS,
        ]);
    }

    public function store(Business $business, Request $request)
    {
        $this->authorizeOwner($business);

        // One pending submission at a time keeps the admin queue clean.
        $existingPending = VerificationPayment::where('business_id', $business->id)->pending()->exists();
        if ($existingPending) {
            throw ValidationException::withMessages([
                'method' => 'فيه طلب تفعيل سابق لسه بانتظار المراجعة. هنرد عليك خلال 24 ساعة.',
            ]);
        }

        $data = $request->validate([
            'method'         => ['required', Rule::in(array_keys(VerificationPayment::METHODS))],
            'transaction_id' => ['nullable', 'string', 'max:80'],
            'proof'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'note'           => ['nullable', 'string', 'max:300'],
        ]);

        // For digital methods (instapay / vodafone_cash) we want EITHER a
        // transaction id OR a screenshot — at least one. Cash skips both.
        if ($data['method'] !== 'cash' && empty($data['transaction_id']) && ! $request->hasFile('proof')) {
            throw ValidationException::withMessages([
                'proof' => 'محتاج رقم العملية أو screenshot من التحويل عشان نراجعه.',
            ]);
        }

        $proofUrl = null;
        if ($request->hasFile('proof')) {
            $proofUrl = ImageUploader::store($request->file('proof'), 'verification-proofs');
        }

        VerificationPayment::create([
            'business_id'    => $business->id,
            'user_id'        => Auth::id(),
            'method'         => $data['method'],
            'amount'         => (int) config('verification.price_egp'),
            'months'         => (int) config('verification.duration_months'),
            'transaction_id' => $data['transaction_id'] ?? null,
            'proof_url'      => $proofUrl,
            'note'           => $data['note'] ?? null,
            'status'         => 'pending',
        ]);

        return redirect()->route('verify.show', $business)
            ->with('flash', '✓ تم استلام طلبك. هنراجعه خلال 24 ساعة وهتوصلك notification لما يتم التفعيل.');
    }

    // ─────────── Admin ────────────
    public function adminIndex(Request $request)
    {
        $this->authorizeAdmin();
        $filter = $request->query('filter', 'pending');

        $q = VerificationPayment::with('business', 'user')->latest();
        if (in_array($filter, ['pending', 'approved', 'rejected'])) {
            $q->where('status', $filter);
        }
        $payments = $q->limit(100)->get();

        $counts = [
            'pending'  => VerificationPayment::pending()->count(),
            'approved' => VerificationPayment::where('status', 'approved')->count(),
            'rejected' => VerificationPayment::where('status', 'rejected')->count(),
        ];

        return view('admin.verifications', compact('payments', 'counts', 'filter'));
    }

    public function approve(VerificationPayment $payment, Request $request)
    {
        $this->authorizeAdmin();
        abort_unless($payment->status === 'pending', 422);

        $business = $payment->business;
        $months   = max(1, (int) $payment->months);

        $newUntil = Carbon::now('Africa/Cairo');
        if ($business->verified_paid_until && $business->verified_paid_until->isFuture()) {
            // Extend existing subscription instead of resetting
            $newUntil = $business->verified_paid_until;
        }
        $newUntil = $newUntil->copy()->addMonths($months);

        $business->update([
            'is_verified_paid'    => true,
            'verified_paid_until' => $newUntil,
        ]);

        $payment->update([
            'status'             => 'approved',
            'reviewed_by_admin'  => Auth::user()->username ?? 'admin',
            'reviewed_at'        => now(),
        ]);

        // Notify the owner (push if subscribed; otherwise it shows in the panel)
        $this->notifyOwner($payment, true);

        return back()->with('flash', '✓ تم تفعيل البادج لـ ' . $business->name);
    }

    public function reject(VerificationPayment $payment, Request $request)
    {
        $this->authorizeAdmin();
        abort_unless($payment->status === 'pending', 422);

        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'min:5', 'max:300'],
        ]);

        $payment->update([
            'status'             => 'rejected',
            'reject_reason'      => $data['reject_reason'],
            'reviewed_by_admin'  => Auth::user()->username ?? 'admin',
            'reviewed_at'        => now(),
        ]);

        $this->notifyOwner($payment, false);

        return back()->with('flash', '× تم رفض الطلب وإبلاغ صاحب النشاط.');
    }

    // ─────────── Helpers ────────────
    private function authorizeOwner(Business $business): void
    {
        $u = Auth::user();
        $isOwner = $u && $business->owner_user_id && $u->id === $business->owner_user_id;
        $isAdmin = $u && $u->is_admin;
        abort_unless($isOwner || $isAdmin, 403);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->is_admin, 403);
    }

    private function notifyOwner(VerificationPayment $payment, bool $approved): void
    {
        if (! $payment->business?->owner_user_id) return;
        if (! class_exists(PushService::class) || ! PushService::isConfigured()) return;
        try {
            PushService::sendToUser($payment->business->owner_user_id, [
                'title' => $approved ? '✓ تم تفعيل شارة موثّق' : 'طلب الـ verified بادج اتراجع',
                'body'  => $approved
                    ? 'نشاطك ' . $payment->business->name . ' بقى موثّق. الشارة هتظهر على صفحتك.'
                    : 'طلبك اتراجع: ' . ($payment->reject_reason ?? '—'),
                'url'   => '/directory/business/' . $payment->business_id . '/verify-badge',
                'tag'   => 'verify-' . $payment->id,
            ]);
        } catch (\Throwable $e) {
            // notification failures are non-fatal
        }
    }
}
