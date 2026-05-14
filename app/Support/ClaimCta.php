<?php

namespace App\Support;

use App\Models\Business;

/**
 * Category-aware copy for the "claim this business" callout on directory/show.
 *
 * The previous wording promised "ارفع منيو" to every owner including pharmacies
 * and government offices, which read as broken. This switches the title / desc
 * based on the business category so the pitch matches the actual product the
 * owner would use.
 */
class ClaimCta
{
    /**
     * Public-facing places (mosques / churches / transport / tourist landmarks)
     * usually don't have a single "owner" — the right ask is data correction,
     * not a paid claim.
     */
    private const CORRECTION_CATEGORIES = ['emergency', 'government', 'religious', 'transport', 'tourist'];

    /**
     * Return ['title', 'desc', 'mode'] for the given business.
     *
     * mode = 'claim'      → owner-facing claim CTA (default)
     *      = 'correction'  → "wrong data?" — switch action to data report
     */
    public static function forBusiness(Business $business): array
    {
        $cat = $business->category;

        if (in_array($cat, self::CORRECTION_CATEGORIES, true)) {
            // Stronger framing for emergency / government — these need to be
            // accurate above all else.
            if (in_array($cat, ['emergency', 'government'], true)) {
                return [
                    'title' => 'شايف بيانات غلط؟',
                    'desc'  => 'بلّغنا عن رقم أو عنوان غير صحيح عشان نحدّثه.',
                    'mode'  => 'correction',
                ];
            }
            return [
                'title' => 'ساعدنا نحدّث البيانات',
                'desc'  => 'بلّغ عن أي رقم أو عنوان أو معلومة غير دقيقة.',
                'mode'  => 'correction',
            ];
        }

        return match ($cat) {
            'food' => [
                'title' => 'ده نشاطك؟ امتلك الصفحة',
                'desc'  => 'عدّل البيانات، ضيف صور، ارفع منيو، وانشر عروض.',
                'mode'  => 'claim',
            ],
            'medical' => [
                'title' => 'دي عيادتك؟ امتلك الصفحة',
                'desc'  => 'عدّل المواعيد، ضيف التخصص والخدمات، وسهّل وصول المرضى ليك.',
                'mode'  => 'claim',
            ],
            'craftsmen', 'services' => [
                'title' => 'ده شغلك؟ امتلك الصفحة',
                'desc'  => 'ضيف صور شغلك، مناطق الخدمة، ورقم واتساب للتواصل.',
                'mode'  => 'claim',
            ],
            'shops' => [
                'title' => 'ده محلك؟ امتلك الصفحة',
                'desc'  => 'عدّل البيانات، ضيف صور، وانشر عروضك لعملاء بنها.',
                'mode'  => 'claim',
            ],
            default => [
                'title' => 'ده نشاطك؟ امتلك الصفحة',
                'desc'  => 'عدّل البيانات، ضيف صور، وسهّل وصول الناس ليك.',
                'mode'  => 'claim',
            ],
        };
    }
}
