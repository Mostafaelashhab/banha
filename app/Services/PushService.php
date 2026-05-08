<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushService
{
    public static function isConfigured(): bool
    {
        return ! empty(config('services.vapid.public_key'))
            && ! empty(config('services.vapid.private_key'));
    }

    public static function sendToUser(int $userId, array $payload): array
    {
        // Persist to inbox (so user can review history even without push permission)
        \App\Models\Notification::create([
            'user_id'    => $userId,
            'type'       => $payload['tag'] ?? 'general',
            'title'      => mb_substr($payload['title'] ?? '', 0, 200),
            'body'       => mb_substr($payload['body']  ?? '', 0, 300),
            'url'        => $payload['url']   ?? null,
            'created_at' => now(),
        ]);

        $subs = PushSubscription::where('user_id', $userId)->get();
        return self::sendToSubscriptions($subs, $payload);
    }

    public static function sendToZone(int $zoneId, array $payload): array
    {
        $subs = PushSubscription::query()
            ->whereHas('user', function ($q) use ($zoneId) {
                $q->where('zone_id', $zoneId)->where('is_banned', false);
            })
            ->get();

        return self::sendToSubscriptions($subs, $payload);
    }

    public static function sendToSubscriptions(Collection $subs, array $payload): array
    {
        if ($subs->isEmpty() || ! self::isConfigured()) {
            return ['sent' => 0, 'failed' => 0, 'pruned' => 0];
        }

        $auth = [
            'VAPID' => [
                'subject'    => config('services.vapid.subject', 'mailto:hello@banhawy.app'),
                'publicKey'  => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ];

        $webPush = new WebPush($auth);
        $body    = json_encode($payload, JSON_UNESCAPED_UNICODE);

        foreach ($subs as $row) {
            $sub = Subscription::create([
                'endpoint'        => $row->endpoint,
                'publicKey'       => $row->p256dh,
                'authToken'       => $row->auth,
                'contentEncoding' => 'aes128gcm',
            ]);
            $webPush->queueNotification($sub, $body);
        }

        $sent = 0; $failed = 0; $pruned = 0;
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $sent++;
                continue;
            }
            $failed++;
            // Subscription dead (410 Gone / 404) — remove from DB
            if (in_array($report->getResponse()?->getStatusCode(), [404, 410], true)) {
                PushSubscription::where('endpoint', $report->getRequest()->getUri())->delete();
                $pruned++;
            } else {
                Log::info('[Push] failed', [
                    'reason'   => $report->getReason(),
                    'endpoint' => (string) $report->getRequest()->getUri(),
                ]);
            }
        }

        return compact('sent', 'failed', 'pruned');
    }
}
