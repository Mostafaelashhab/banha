<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            // [slug, name, description, emoji, color, tier, criteria_kind, criteria_value, sort]
            ['early-bird',     'من الأوائل',     'سجّلت ضمن أول ١٠٠٠ بنهاوي',                 '🌅', '#FFB85C', 'rare',      'user_id_under',     1000, 1],
            ['first-post',     'أول بوست',       'نشرت أول بوست في بنهاوي',                  '🎉', '#FF7A4D', 'common',    'post_count',        1,    2],
            ['ten-posts',      'مساهم نشيط',     'نشرت ١٠ بوستات',                            '✍️', '#FF7A4D', 'common',    'post_count',        10,   3],
            ['fifty-posts',    'صوت بنها',       'نشرت ٥٠ بوست',                              '📣', '#F55A2C', 'rare',      'post_count',        50,   4],
            ['first-hot',      'في الترند',      'بوست بتاعك وصل ٥٠ صوت إيجابي',              '🔥', '#FF7A4D', 'rare',      'hot_post',          1,    5],
            ['trend-king',     'ملك الترند',     '٥ بوستات وصلوا الترند',                      '👑', '#FFB85C', 'legendary', 'hot_post',          5,    6],
            ['first-comment',  'مشارك',          'كتبت أول كومنت',                            '💬', '#FF7A4D', 'common',    'comment_count',     1,    7],
            ['commenter-100',  'لسان بنها',      '١٠٠ كومنت',                                  '🗣️', '#F55A2C', 'rare',      'comment_count',     100,  8],
            ['first-price',    'رادار سعر',      'سجّلت أول سعر في رادار الأسعار',           '🛒', '#1FA857', 'common',    'price_count',       1,    9],
            ['price-radar',    'بياع سوق',        '٢٥ سعر مسجّل',                              '🏪', '#0D8A3F', 'rare',      'price_count',       25,   10],
            ['first-alert',    'يقظ',            'بلّغت عن أول تنبيه',                        '🚨', '#E64646', 'common',    'alert_count',       1,    11],
            ['verified-alert', 'تنبيه موثّق',     'تنبيه بتاعك حصل على ٣ تأكيدات',             '✅', '#1FA857', 'rare',      'verified_alert',    1,    12],
            ['rep-100',        'سمعة ١٠٠',       'وصلت ١٠٠ نقطة سمعة',                       '⭐', '#FFB85C', 'common',    'reputation',        100,  13],
            ['rep-500',        'سمعة ٥٠٠',       'وصلت ٥٠٠ نقطة سمعة',                       '🌟', '#F55A2C', 'legendary', 'reputation',        500,  14],
            ['whisper-friend', 'صديق الأسرار',   '١٠ بوستات مجهولة',                          '🤫', '#7C3AED', 'rare',      'anon_post_count',   10,   15],
            ['silver-verified','موثّق فضي',      'استحققت توثيق فضي بعد الالتزام والنشاط',   '🥈', '#9CA3AF', 'legendary', 'silver_tier',       1,    16],
        ];

        foreach ($badges as [$slug, $name, $desc, $emoji, $color, $tier, $kind, $value, $sort]) {
            Badge::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'           => $name,
                    'description'    => $desc,
                    'emoji'          => $emoji,
                    'color'          => $color,
                    'tier'           => $tier,
                    'criteria_kind'  => $kind,
                    'criteria_value' => $value,
                    'is_secret'      => false,
                    'sort'           => $sort,
                ]
            );
        }
    }
}
