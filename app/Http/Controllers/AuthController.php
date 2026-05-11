<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use App\Services\BadgeService;
use App\Services\VerificationService;
use App\Support\AnonSeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($r = $request->query('redirect')) {
            if (str_starts_with($r, '/') && ! str_starts_with($r, '//')) {
                $request->session()->put('url.intended', url($r));
            }
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $key = 'login:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages([
                'phone' => 'حاول تاني بعد ١٠ دقايق.',
            ]);
        }

        $data = $request->validate([
            'phone'    => ['required', 'regex:/^01[0125][0-9]{8}$/'],
            'password' => ['required', 'string', 'min:6', 'max:80'],
        ], [
            'phone.regex'      => 'لازم رقم موبايل مصري صحيح.',
            'password.min'     => 'الباسورد لازم ٦ حروف على الأقل.',
        ]);

        $user = User::where('phone', $data['phone'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, 600);
            throw ValidationException::withMessages([
                'phone' => 'الرقم أو الباسورد غلط.',
            ]);
        }

        if ($user->is_banned) {
            throw ValidationException::withMessages([
                'phone' => 'حسابك متوقف. ابعتلنا على support@banhawy.app',
            ]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('feed'));
    }

    public function showSignup(Request $request)
    {
        // Sticky-capture the referral code so it survives the user browsing
        // around before clicking signup. Session > cookie for 30 days.
        $ref = $request->query('ref');
        if ($ref) {
            $request->session()->put('ref_code', $ref);
            cookie()->queue(cookie('ref', $ref, 60 * 24 * 30));  // 30 days
        }

        return view('auth.signup', [
            'zones'    => Zone::orderBy('sort')->get(),
            'refCode'  => $ref ?? $request->session()->get('ref_code') ?? $request->cookie('ref'),
        ]);
    }

    public function signup(Request $request)
    {
        $data = $request->validate([
            'phone'    => ['required', 'regex:/^01[0125][0-9]{8}$/', 'unique:users,phone'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[\p{Arabic}A-Za-z0-9_]+$/u', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'max:80', 'confirmed'],
            'zone_id'  => ['required', 'exists:zones,id'],
            'agree'    => ['required', 'accepted'],
        ], [
            'phone.regex'        => 'لازم رقم موبايل مصري صحيح.',
            'phone.unique'       => 'الرقم ده مسجّل قبل كده.',
            'username.regex'     => 'اليوزر نيم: حروف عربي/إنجليزي وأرقام و _ بس.',
            'username.unique'    => 'اليوزر نيم محجوز، جرّب واحد تاني.',
            'password.min'       => 'الباسورد لازم ٦ حروف على الأقل.',
            'password.confirmed' => 'الباسورد مش متطابق.',
            'agree.accepted'     => 'لازم توافق على الشروط.',
        ]);

        $user = User::create([
            'phone'       => $data['phone'],
            'username'    => $data['username'],
            'password'    => $data['password'],
            'zone_id'     => $data['zone_id'],
            'persona'     => 'resident',
            'avatar_seed' => AnonSeed::generate(),
            'reputation'  => 50,
        ]);

        // Referral capture — link the invitee to whoever sent the ?ref= code.
        // The link is recorded but the inviter is NOT credited yet — payout
        // happens later when this user earns 50+ organic points (ReferralService).
        \App\Services\ReferralService::capture(
            $user,
            $request->session()->pull('ref_code') ?? $request->input('ref') ?? $request->cookie('ref')
        );

        BadgeService::onSignup($user);
        \App\Services\AdminNotificationService::onUserSignup($user->fresh()->load('zone'));

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        // Send OTP immediately on signup; user lands on the activation page
        try {
            \App\Services\OtpService::send($user->phone, \App\Services\OtpService::PURPOSE_ACTIVATE, $request->ip());
        } catch (\Throwable $e) {
            // ignore; user can resend manually from the activation page
        }

        return redirect()->route('verify.show')
            ->with('flash', 'بعتنالك كود تفعيل على واتساب: '.$user->phone);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
