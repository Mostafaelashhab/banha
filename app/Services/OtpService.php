<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public const PURPOSE_ACTIVATE = 'activate';
    public const PURPOSE_PASSWORD = 'password';

    public const TTL_MINUTES   = 5;
    public const MAX_ATTEMPTS  = 5;
    public const SEND_COOLDOWN = 60;       // seconds between sends per phone+purpose
    public const SEND_LIMIT    = 5;        // max sends per phone per hour
    public const SEND_WINDOW   = 3600;     // window for SEND_LIMIT

    /**
     * Generate + send a fresh OTP. Throws ValidationException on rate limit.
     */
    public static function send(string $phone, string $purpose, ?string $ip = null): array
    {
        $cooldownKey = "otp:cooldown:{$phone}:{$purpose}";
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            throw ValidationException::withMessages([
                'code' => 'استنى '.RateLimiter::availableIn($cooldownKey).' ثانية قبل ما تطلب كود جديد.',
            ]);
        }

        $hourKey = "otp:hour:{$phone}";
        if (RateLimiter::tooManyAttempts($hourKey, self::SEND_LIMIT)) {
            throw ValidationException::withMessages([
                'code' => 'كتر طلب الكود. حاول تاني بعد ساعة.',
            ]);
        }

        $code = (string) random_int(100000, 999999);

        // Invalidate prior unused codes for this phone+purpose
        OtpCode::where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->update(['expires_at' => now()->subMinute()]);

        OtpCode::create([
            'phone'      => $phone,
            'purpose'    => $purpose,
            'code_hash'  => Hash::make($code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            'ip'         => $ip,
        ]);

        $purposeLabel = match ($purpose) {
            self::PURPOSE_PASSWORD => 'تغيير الباسورد',
            default                => 'التفعيل',
        };
        $result = WaapiService::sendOtp($phone, $code, $purposeLabel);

        RateLimiter::hit($cooldownKey, self::SEND_COOLDOWN);
        RateLimiter::hit($hourKey, self::SEND_WINDOW);

        return [
            'sent'      => $result['ok'],
            'simulated' => $result['simulated'] ?? false,
            'code'      => app()->isLocal() ? $code : null,  // expose only in local for testing
        ];
    }

    /**
     * Verify a code. Returns true if valid + marks the row as verified.
     */
    public static function verify(string $phone, string $purpose, string $code): bool
    {
        $row = OtpCode::where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $row) return false;

        if ($row->attempts >= self::MAX_ATTEMPTS) {
            $row->update(['expires_at' => now()->subMinute()]);
            return false;
        }

        $row->increment('attempts');

        if (! Hash::check($code, $row->code_hash)) {
            return false;
        }

        $row->update(['verified_at' => now()]);
        return true;
    }
}
