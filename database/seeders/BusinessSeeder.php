<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $banhaId       = Zone::where('slug', 'banha')->value('id');
        $toukhId       = Zone::where('slug', 'toukh')->value('id');
        $qalyubId      = Zone::where('slug', 'qalyub')->value('id');
        $shubraId      = Zone::where('slug', 'shubra-elkheima')->value('id');
        $qahaId        = Zone::where('slug', 'qaha')->value('id');

        $businesses = [
            // ─── Craftsmen (صنايعية) ────────────────────────────────────
            ['عم محمد السباك',      'craftsmen', 'plumber',        $banhaId,  '01012345001', null,           'شارع الجيش — قسم أول بنها', 'يومي ٩ص-٩م', false, true,  'سباكة وتسليك بالواوي · خبرة ٢٠ سنة', '🔧'],
            ['أبو علي للسباكة',     'craftsmen', 'plumber',        $toukhId,  '01112345002', '01112345002',  'ميدان البلد — طوخ',          'يومي ٨ص-١٠م', false, false, 'سباكة وكشف تسربات بالأجهزة',          '🔧'],
            ['كهرباء الشاطر',       'craftsmen', 'electrician',    $banhaId,  '01212345003', '01212345003',  'شارع فريد ندا',              '٢٤ ساعة',     true,  true,  'كهرباء منازل وفلل · طوارئ ٢٤ ساعة',  '💡'],
            ['عم سيد كهربائي',      'craftsmen', 'electrician',    $qalyubId, '01512345004', null,           'شارع الفلكي — القليوب',      'يومي ٩ص-١١م', false, false, 'تأسيس وصيانة + إنارة LED',           '💡'],
            ['ورشة محمود نجار',    'craftsmen', 'carpenter',      $banhaId,  '01012345005', '01012345005',  'شارع البنوك',                'سبت-خميس ٨ص-٨م', false, true, 'موبيليا حديثة وكلاسيك + تركيب', '🔨'],
            ['نقاش أبو حازم',       'craftsmen', 'painter',        $toukhId,  '01112345006', null,           'طوخ — قسم تاني',             'يومي ٧ص-٦م', false, false, 'بوية بلاستيك وزيت + ديكور',          '🎨'],
            ['تكييفات الجوهري',    'craftsmen', 'ac_tech',        $banhaId,  '01012345007', '01012345007',  'شارع الجيش',                  'يومي ١٠ص-١٢م', false, true,  'صيانة وتركيب وفك وتنظيف تكييفات',    '❄️'],
            ['فني تكييف الأمل',    'craftsmen', 'ac_tech',        $shubraId, '01212345008', null,           'شبرا الخيمة — مساكن',         'يومي ٩ص-١١م', false, false, 'صيانة سبليت وشباك',                  '❄️'],
            ['أبو يوسف غسالات',    'craftsmen', 'appliance_tech', $banhaId,  '01512345009', null,           'بنها قسم تاني',               'سبت-خميس',     false, true,  'تصليح غسالات وبوتاجازات وثلاجات',    '⚙️'],
            ['عم رمضان بوتاجاز',  'craftsmen', 'gas_tech',       $banhaId,  '01012345010', null,           'شارع كاب',                    'يومي ٩ص-١٠م', false, false, 'تركيب وصيانة بوتاجاز ومواقد',        '🔥'],
            ['ألوميتال الشيمي',    'craftsmen', 'aluminum',       $banhaId,  '01112345011', '01112345011',  'طريق بنها-شبين',              'سبت-خميس ٩ص-٧م', false, true, 'شبابيك ألوميتال وسكيورت', '🪟'],

            // ─── Food (مطاعم وكافيهات) ───────────────────────────────────
            ['كشري طارق',          'food', 'restaurant', $banhaId,  '01012001001', null,          'ميدان المحطة — بنها',         'يومي ١١ص-٢ص', false, true, 'أحسن كشري في بنها · الطبق ٣٠ ج',  '🍝'],
            ['فول كوبيرا',         'food', 'fast_food',  $banhaId,  '01012001002', null,          'شارع الجيش',                  'يومي ٦ص-١٢م', false, true, 'فول وطعمية أصلي · سندوتش ٥ ج',    '🥙'],
            ['عم سعيد فطار',       'food', 'fast_food',  $banhaId,  '01112001003', null,           'قسم أول بنها',               'يومي ٦ص-٢م', false, false, 'فطار بنهاوي + توصيل',             '🥙'],
            ['كافيه نوار',         'food', 'cafe',       $banhaId,  '01012001004', '01012001004', 'شارع فريد ندا',               'يومي ١م-٢ص', false, true, 'هاديء للمذاكرة + واي فاي + خصم طلبة',  '☕'],
            ['كافيه البلكونة',     'food', 'cafe',       $banhaId,  '01512001005', null,          'طريق بنها-شبين',              'يومي ٣م-٣ص', false, false, 'سيشة وقهوة + شاشات ماتشات',     '☕'],
            ['مطعم أبو حسن',       'food', 'restaurant', $banhaId,  '01212001006', '01212001006', 'شارع الجيش',                  'يومي ١٢م-١٢ص', false, true, 'مشويات · فراخ مشوية ١٢٠ ج',     '🍗'],
            ['حلواني الإمام',      'food', 'sweets',     $banhaId,  '01012001007', null,          'ميدان المحطة',                'يومي ٩ص-١٢م', false, true, 'حلويات شرقية + بقلاوة',       '🍰'],
            ['مخبز الحرمين',       'food', 'bakery',     $toukhId,  '01112001008', null,          'طوخ — السوق',                 'يومي ٥ص-١٠م', false, false, 'خبز سياحي وبلدي + معجنات',         '🍞'],
            ['عصاير شجرة الدر',    'food', 'juice',      $banhaId,  '01512001009', null,           'شارع كاب',                   'يومي ١٠ص-١م', false, false, 'عصاير طبيعية + كوكتيلات',         '🥤'],

            // ─── Medical (دكاترة وصيدليات) ────────────────────────────────
            ['د. أحمد عبد الله — أطفال', 'medical', 'pediatrician', $banhaId, '01012002001', '01012002001', 'برج الأطباء — ميدان المحطة', 'سبت-خميس ٤م-١٠م', false, true, 'استشاري طب أطفال · كشف ١٢٠ ج', '👶'],
            ['د. سارة محمود — نسا',     'medical', 'doctor',       $banhaId, '01112002002', null,          'شارع البنوك',                 'سبت/أحد/ثلاثاء/خميس ٥م-٩م', false, true, 'استشاري نسا وتوليد', '🩺'],
            ['د. هاني السيد — أسنان',   'medical', 'dentist',      $banhaId, '01012002003', '01012002003', 'شارع الجيش — برج النور',     'سبت-خميس ٤م-١٠م', false, true, 'تجميل وتقويم وزراعة أسنان', '🦷'],
            ['صيدلية الشفاء',          'medical', 'pharmacy',     $banhaId, '01012002004', '01012002004', 'شارع فريد ندا',               '٢٤ ساعة', true, true, 'كل الأدوية + توصيل مجاني فوق ١٠٠ ج', '💊'],
            ['صيدلية القلوبية',        'medical', 'pharmacy',     $qalyubId, '01112002005', null,         'القليوب — شارع الفلكي',       'يومي ٩ص-٢ص', false, false, 'صيدلية شاملة', '💊'],
            ['صيدلية الكوثر',          'medical', 'pharmacy',     $toukhId, '01512002006', null,          'طوخ',                          '٢٤ ساعة', true, false, 'فتح ٢٤ ساعة', '💊'],
            ['معمل المختبر الحديث',   'medical', 'lab',           $banhaId, '01012002007', null,           'شارع الجيش',                  'يومي ٧ص-١٠م', false, true, 'تحاليل عامة وهرمونية', '🧪'],

            // ─── Shops (محلات) ──────────────────────────────────────────
            ['سوبر ماركت العائلة',  'shops', 'supermarket', $banhaId, '01012003001', '01012003001', 'شارع البنوك',          'يومي ٩ص-١م', false, true, 'كل احتياجات البيت + توصيل', '🛒'],
            ['بقالة عم سيد',         'shops', 'grocery',     $banhaId, '01112003002', null,          'قسم تاني',              'يومي ٦ص-١٢م', false, false, 'بقالة الحي', '🥫'],
            ['جزارة الأنصاري',       'shops', 'butcher',     $banhaId, '01012003003', null,          'ميدان المحطة',          'يومي ٧ص-١٠م', false, true, 'لحوم بلدي + كباب', '🥩'],
            ['سمكري الصياد',         'shops', 'fish_shop',   $toukhId, '01512003004', null,          'طوخ — السوق',           'يومي ٥ص-٨م', false, false, 'سمك طازة من البحر', '🐟'],
            ['خضار وفاكهة سوق بنها', 'shops', 'fruit_veg',   $banhaId, '01112003005', null,          'السوق البلدي',         'يومي ٦ص-١٠م', false, false, 'كل الخضار والفاكهة بأسعار جملة', '🥬'],

            // ─── Services (خدمات) ───────────────────────────────────────
            ['مغسلة النور',          'services', 'laundry',     $banhaId, '01012004001', null,          'شارع الجيش',          'يومي ٨ص-١٠م', false, false, 'تنظيف جاف وفاب', '🧺'],
            ['كوافير ليليان',        'services', 'salon',       $banhaId, '01112004002', '01112004002', 'شارع فريد ندا',      'سبت-خميس ١٠ص-١٠م', false, true, 'تسريحات أفراح ومناسبات', '💇'],
            ['حلاق الصالح',          'services', 'barber',      $banhaId, '01012004003', null,          'شارع كاب',            'يومي ١٠ص-١٢م', false, false, 'حلاقة كلاسيك وحديثة', '💈'],
            ['سنتر أبو هاشم',        'services', 'tutor',       $banhaId, '01512004004', '01512004004', 'شارع البنوك',         'سبت-خميس ٤م-١٠م', false, true, 'سنتر دروس تجارة وحقوق وآداب', '📚'],
            ['استوديو لمسة',         'services', 'photographer',$banhaId, '01112004005', '01112004005', 'ميدان المحطة',        'يومي ١٠ص-١٢م', false, false, 'تصوير أفراح وخطوبة', '📷'],
            ['خياط الإحسان',         'services', 'tailor',      $qahaId,  '01012004006', null,          'قها',                 'سبت-خميس',     false, false, 'تفصيل بدل وفساتين', '✂️'],
        ];

        foreach ($businesses as [$name, $cat, $sub, $zoneId, $phone, $whatsapp, $address, $hours, $is24h, $isVerified, $desc, $emoji]) {
            Business::updateOrCreate(
                ['name' => $name],
                [
                    'category'    => $cat,
                    'sub_type'    => $sub,
                    'zone_id'     => $zoneId,
                    'phone'       => $phone,
                    'whatsapp'    => $whatsapp,
                    'address'     => $address,
                    'hours'       => $hours,
                    'is_24h'      => $is24h,
                    'is_verified' => $isVerified,
                    'is_active'   => true,
                    'description' => $desc,
                    'emoji'       => $emoji,
                    'rating_avg'  => round(mt_rand(35, 50) / 10, 1),
                    'ratings_count' => mt_rand(5, 80),
                ]
            );
        }
    }
}
