<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaapiService
{
    /**
     * Send a WhatsApp message via WAAPI.
     *
     * Egyptian phones (01x) get auto-prefixed with +20.
     */
    public static function send(string $phone, string $message): array
    {
        if (! config('services.waapi.enabled')) {
            Log::info('[WAAPI] disabled — would have sent to '.$phone, ['message' => $message]);
            return ['ok' => true, 'simulated' => true];
        }

        $intl = self::toIntl($phone);

        try {
            $resp = Http::asForm()
                ->acceptJson()
                ->timeout(15)
                ->post(config('services.waapi.url'), [
                    'appkey'  => config('services.waapi.app_key'),
                    'authkey' => config('services.waapi.auth_key'),
                    'to'      => $intl,
                    'message' => $message,
                    'sandbox' => false,
                ]);

            $body = $resp->json() ?? [];

            if (! $resp->successful()) {
                Log::warning('[WAAPI] non-2xx response', [
                    'phone'  => $intl,
                    'status' => $resp->status(),
                    'body'   => $body,
                ]);
                return ['ok' => false, 'status' => $resp->status(), 'body' => $body];
            }

            return ['ok' => true, 'body' => $body];
        } catch (ConnectionException $e) {
            Log::error('[WAAPI] connection error', ['error' => $e->getMessage()]);
            return ['ok' => false, 'error' => 'connection'];
        } catch (\Throwable $e) {
            Log::error('[WAAPI] unexpected error', ['error' => $e->getMessage()]);
            return ['ok' => false, 'error' => 'unexpected'];
        }
    }

    public static function sendOtp(string $phone, string $code, string $purpose = 'تفعيل'): array
    {
        $msg = "كود {$purpose} بنهاوي:\n\n*{$code}*\n\nالكود صالح لـ ٥ دقائق فقط."
            ."\nمتشاركش الكود ده مع حد — فريق بنهاوي مش هيطلبه منك أبداً.";

        return self::send($phone, $msg);
    }

    /**
     * Convert local Egyptian format (01XXXXXXXXX) to international (201XXXXXXXXX).
     */
    public static function toIntl(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone);
        if (str_starts_with($clean, '20')) {
            return $clean;
        }
        if (str_starts_with($clean, '0')) {
            return '20'.substr($clean, 1);
        }
        return $clean;
    }
}
