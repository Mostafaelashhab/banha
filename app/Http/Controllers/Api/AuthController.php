<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\OtpService;
use App\Support\AnonSeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $key = 'login:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages([
                'phone' => ['حاول تاني بعد ١٠ دقايق.'],
            ]);
        }

        $data = $request->validate([
            'phone'    => ['required', 'regex:/^01[0125][0-9]{8}$/'],
            'password' => ['required', 'string', 'min:6', 'max:80'],
            'device'   => ['nullable', 'string', 'max:120'],
        ], [
            'phone.regex'  => 'لازم رقم موبايل مصري صحيح.',
            'password.min' => 'الباسورد لازم ٦ حروف على الأقل.',
        ]);

        $user = User::where('phone', $data['phone'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, 600);
            throw ValidationException::withMessages([
                'phone' => ['الرقم أو الباسورد غلط.'],
            ]);
        }

        if ($user->is_banned) {
            return response()->json(['message' => 'حسابك متوقف'], 403);
        }

        $token = $user->createToken($data['device'] ?? 'mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user->load('zone')),
            'needs_verification' => ! $user->is_verified,
        ]);
    }

    public function signup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone'    => ['required', 'regex:/^01[0125][0-9]{8}$/', 'unique:users,phone'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[\p{Arabic}A-Za-z0-9_]+$/u', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'max:80', 'confirmed'],
            'zone_id'  => ['required', 'exists:zones,id'],
            'agree'    => ['required', 'accepted'],
            'device'   => ['nullable', 'string', 'max:120'],
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

        // Send activation OTP via WhatsApp immediately
        $otp = ['sent' => false];
        try {
            $otp = OtpService::send($user->phone, OtpService::PURPOSE_ACTIVATE, $request->ip());
        } catch (\Throwable) {
            // ignore — client can resend
        }

        $token = $user->createToken($data['device'] ?? 'mobile')->plainTextToken;

        return response()->json([
            'token'              => $token,
            'user'               => new UserResource($user->load('zone')),
            'needs_verification' => true,
            'otp_sent'           => (bool) ($otp['sent'] ?? false),
            'debug_code'         => $otp['code'] ?? null, // only set in local
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->json(['ok' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load('zone')),
            'needs_verification' => ! $request->user()->is_verified,
        ]);
    }

    // ─── OTP: send & verify activation ──────────────────────────────────
    public function sendOtp(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_verified) {
            return response()->json(['message' => 'الحساب متفعّل بالفعل'], 422);
        }

        $otp = OtpService::send($user->phone, OtpService::PURPOSE_ACTIVATE, $request->ip());

        return response()->json([
            'sent'       => (bool) ($otp['sent'] ?? false),
            'simulated'  => (bool) ($otp['simulated'] ?? false),
            'debug_code' => $otp['code'] ?? null,
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ], [
            'code.regex' => 'الكود لازم ٦ أرقام.',
        ]);

        $user = $request->user();

        if (! OtpService::verify($user->phone, OtpService::PURPOSE_ACTIVATE, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => ['الكود غلط أو انتهت صلاحيته. اطلب كود جديد.'],
            ]);
        }

        $user->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verification_tier' => $user->verification_tier ?: 'bronze',
        ]);

        return response()->json([
            'user' => new UserResource($user->fresh()->load('zone')),
            'needs_verification' => false,
        ]);
    }
}
