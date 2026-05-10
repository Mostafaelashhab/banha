<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * "ده نشاطي" — claim ownership of an unowned (OSM-imported) business.
 *
 * Flow:
 *   1. requestOtp(): user picks a business with no owner; if it has a phone
 *      number, we send an OTP to that phone via WhatsApp. The premise is
 *      the real owner is the only one who can read messages on that line.
 *   2. verify(): user enters the code; on success owner_user_id = current user.
 *
 * Businesses without a phone can't be claimed via OTP — the user must use
 * the manual "تواصل مع الدعم" link instead (admin verifies offline).
 */
class BusinessClaimController extends Controller
{
    public function show(Business $business)
    {
        $this->guardClaimable($business);
        return view('directory.claim', compact('business'));
    }

    public function requestOtp(Business $business)
    {
        $this->guardClaimable($business);

        $phone = $this->normalizePhone($business->phone);
        if (! $phone) {
            return back()->with('flash', 'النشاط ده مفيش رقم تليفون مسجّل — تواصل مع الدعم لنقل الملكية يدوياً.');
        }

        // Per-user rate limit on claim attempts (prevent brute-force across businesses)
        $key = 'claim:'.Auth::id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'code' => 'حاولت كتير. استنى ساعة وبعدين جرّب تاني.',
            ]);
        }
        RateLimiter::hit($key, 3600);

        try {
            $result = OtpService::send($phone, OtpService::PURPOSE_CLAIM, request()->ip());
        } catch (ValidationException $e) {
            throw $e;
        }

        // Stash the business id in session so verify() knows which biz this OTP unlocks
        session([
            'claim.business_id' => $business->id,
            'claim.phone'       => $phone,
        ]);

        return redirect()->route('directory.claim.show', $business)
            ->with('flash', 'بعتنالك كود على واتساب رقم النشاط: '.$this->mask($phone))
            ->with('claim_otp_sent', true);
    }

    public function verify(Request $request, Business $business)
    {
        $this->guardClaimable($business);

        if (session('claim.business_id') !== $business->id) {
            return redirect()->route('directory.claim.show', $business)
                ->with('flash', 'ابدأ من أول، اطلب كود جديد.');
        }

        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'دخّل الكود.',
            'code.digits'   => 'الكود ٦ أرقام.',
        ]);

        $phone = session('claim.phone');
        if (! $phone) abort(422);

        $ok = OtpService::verify($phone, OtpService::PURPOSE_CLAIM, $data['code']);
        if (! $ok) {
            throw ValidationException::withMessages(['code' => 'الكود غلط أو انتهت صلاحيته.']);
        }

        // Transfer ownership atomically
        $business->update([
            'owner_user_id' => Auth::id(),
        ]);

        // Reward claim — DB UNIQUE keeps it once-per-business-per-user
        \App\Services\PointsService::award(Auth::user(), 'business_claimed', $business);

        // Bust map cache so any future map updates respect the new owner
        \Illuminate\Support\Facades\Cache::forget('map-data:v4:all');
        \Illuminate\Support\Facades\Cache::forget('map-data:v4:'.$business->category);

        // Clean up session
        session()->forget(['claim.business_id', 'claim.phone']);

        \App\Services\AdminNotificationService::onBusinessCreated($business->fresh()->load('owner'));

        return redirect()->route('directory.show', $business)
            ->with('flash', 'تم! بقا نشاطك. روح "تعديل" واملا أي تفاصيل ناقصة.');
    }

    /** A business is claimable iff it has no owner and is active. */
    private function guardClaimable(Business $business): void
    {
        if ($business->owner_user_id !== null) {
            abort(404, 'النشاط ده له صاحب بالفعل.');
        }
        if (! $business->is_active) {
            abort(404);
        }
    }

    /** Strip non-digits and normalize to 11-digit Egyptian mobile if possible. */
    private function normalizePhone(?string $raw): ?string
    {
        if (! $raw) return null;
        $digits = preg_replace('/\D/', '', $raw);
        if (! $digits) return null;
        // Last 11 digits often capture local number (drops country code if present)
        $tail = substr($digits, -11);
        return preg_match('/^01[0125]\d{8}$/', $tail) ? $tail : null;
    }

    /** "01****56789" — show last 5 digits only. */
    private function mask(string $phone): string
    {
        return substr($phone, 0, 2).'****'.substr($phone, -5);
    }
}
