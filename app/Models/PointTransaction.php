<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'delta', 'reason', 'target_type', 'target_id', 'settled', 'meta'])]
class PointTransaction extends Model
{
    protected function casts(): array
    {
        return [
            'delta'   => 'integer',
            'settled' => 'boolean',
            'meta'    => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Display label for the reason code (Arabic, user-facing).
     * Keep in sync with PointsService::REASONS keys.
     */
    public static function reasonLabel(string $code): string
    {
        return match ($code) {
            'signup'              => 'تفعيل الحساب',
            'daily_login'         => 'فتح التطبيق اليوم',
            'first_post'          => 'أول بوست',
            'post_popular'        => 'بوست بـ ٣+ لايكات',
            'review_business'     => 'تقييم محل',
            'business_claimed'    => 'تأكيد ملكية نشاطك',
            'business_verified'   => 'توثيق النشاط',
            'business_quality'    => 'نشاط بكل التفاصيل',
            'invite_settled'      => 'صديقك انضم وفعّل',
            'alert_confirmed'     => 'بلاغك اتأكّد',
            'admin_award'         => 'منحة من الأدمن',
            'spam_penalty'        => 'خصم بسبب spam',
            'spend_promote'       => 'ترويج نشاط',
            'spend_avatar_frame'  => 'إطار avatar',
            'spend_silver_badge'  => 'بادج فضي',
            'spend_skip_queue'    => 'تسريع الدعم',
            'spend_market_feat'   => 'تمييز إعلان السوق',
            'admin_revoke'        => 'تعديل من الأدمن',
            default               => $code,
        };
    }
}
