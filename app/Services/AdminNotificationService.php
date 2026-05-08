<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Business;
use App\Models\Post;
use App\Models\PushSubscription;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Str;

class AdminNotificationService
{
    /**
     * Push to all admin push subscribers.
     */
    protected static function send(array $payload): void
    {
        if (! PushService::isConfigured()) return;

        $subs = PushSubscription::query()
            ->whereHas('user', fn ($q) => $q->where('is_admin', true))
            ->get();

        if ($subs->isEmpty()) return;

        PushService::sendToSubscriptions($subs, $payload);
    }

    public static function onUserSignup(User $user): void
    {
        self::send([
            'title' => '👤 يوزر جديد سجّل',
            'body'  => $user->username.' من '.($user->zone?->name ?? 'مكان غير محدّد'),
            'url'   => route('admin.users'),
            'tag'   => 'admin-signup',
        ]);
    }

    public static function onPostCreated(Post $post): void
    {
        $author = $post->is_anonymous
            ? '🤫 ('.$post->anon_seed.')'
            : ($post->user?->username ?? 'مستخدم');

        self::send([
            'title' => '📝 بوست جديد · '.($post->user?->zone?->name ?? ''),
            'body'  => $author.': '.Str::limit($post->title ?: $post->body, 80),
            'url'   => route('posts.show', $post),
            'tag'   => 'admin-post-'.$post->id,
        ]);
    }

    public static function onAlertCreated(Alert $alert): void
    {
        $meta = $alert->typeMeta();
        self::send([
            'title' => '⚠️ تنبيه جديد · '.($alert->zone?->name ?? ''),
            'body'  => $meta['label'].': '.Str::limit($alert->description, 80),
            'url'   => route('alerts.show', $alert),
            'tag'   => 'admin-alert-'.$alert->id,
        ]);
    }

    public static function onBusinessCreated(Business $business): void
    {
        $owner = $business->owner_user_id ? ($business->owner?->username ?? '') : 'seed';
        self::send([
            'title' => '🛍️ نشاط جديد بانتظار التوثيق',
            'body'  => $business->name.' ('.$business->subTypeMeta()['label'].') · '.$owner,
            'url'   => route('admin.businesses', ['filter' => 'pending']),
            'tag'   => 'admin-business-'.$business->id,
        ]);
    }

    public static function onReportCreated(Report $report): void
    {
        self::send([
            'title' => '🚩 بلاغ جديد',
            'body'  => 'سبب: '.$report->reason.' · على '.$report->target_type.' #'.$report->target_id,
            'url'   => route('admin.reports'),
            'tag'   => 'admin-report-'.$report->id,
        ]);
    }

    public static function onPriceSubmitted(string $product, string $zone, float $price, User $by): void
    {
        self::send([
            'title' => '🏷️ سعر جديد · '.$zone,
            'body'  => $product.' بـ '.number_format($price, 2).' ج · '.$by->username,
            'url'   => route('admin.dashboard'),
            'tag'   => 'admin-price',
        ]);
    }
}
