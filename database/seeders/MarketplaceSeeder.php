<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->inRandomOrder()->limit(40)->get();
        if ($users->isEmpty()) {
            $this->command?->warn('No users to attach listings to — run DemoSeeder first.');
            return;
        }
        $zones = Zone::query()->whereNotNull('lat')->whereNotNull('lng')->get();
        if ($zones->isEmpty()) {
            $this->command?->warn('No zones with coords — run ZoneSeeder first.');
            return;
        }

        // ── Sale (the bulk of any real marketplace) ────────────────────────
        // [category, title, price, description (nullable), negotiable]
        $sale = [
            ['mobile',      'آيفون 13 برو 256 جيجا — لون فاتح',                                 38000, 'الجهاز نضيف جدًا، باتري ٩٢٪. معاه العلبة والشاحن الأصلي. مفيش خدوش.', true],
            ['mobile',      'سامسونج A54 5G — استعمال شهرين',                                  11500, 'ضامنته من المتجر، ضمانه ساري. السبب: هاجر العيلة.', true],
            ['mobile',      'شاحن آيفون أصلي 20W — جديد بالعلبة',                                 650, null, false],
            ['mobile',      'سماعة AirPods Pro الجيل التاني — بحالة الزيرو',                    8200, 'مستعملة كام مرة بس. كل حاجة موجودة معاها.', true],
            ['electronics', 'بلايستيشن 5 ديسك — معاه ٢ دراع و٣ ألعاب',                          27000, 'حالة ممتازة، كل حاجة شغالة. الألعاب: FIFA 24 / EA Sports UFC / Spider-Man.', true],
            ['electronics', 'تليفزيون LG 55 بوصة سمارت 4K',                                    14500, 'مشترَى من ٨ شهور، فاتورة موجودة. السبب: هغير بأكبر.', true],
            ['electronics', 'لاب توب Dell i7 الجيل الـ١١ — رامات ١٦',                          16500, 'شغّال على شغل تصميم ومونتاج بدون مشاكل. شاحن أصلي.', true],
            ['electronics', 'بلاي ستيشن 4 سليم + ٣ دراع + ٧ ألعاب',                            8500,  'الجهاز بكامل أغراضه ونضيف.', true],
            ['electronics', 'كاميرا كانون 200D + عدسة 50mm',                                    13000, 'تصوير فوتوغرافي ممتاز. الشاتر تحت 5K.', true],
            ['electronics', 'مكنسة كهربا فيليبس 2200 وات',                                       1100, 'شغّالة تمام، استعمال نضيف.', true],
            ['electronics', 'مكواه بخار براون مستعملة',                                            450, null, true],
            ['electronics', 'PSP محمولة + كارت ميموري + ١٠ ألعاب',                              1800, 'حالة نضيفة، فولاش جاهز.', true],
            ['electronics', 'سبيكر JBL Charge 5 أصلي',                                          3200, 'بسعر مغري، السبب: مكرر عندي.', true],

            ['furniture',   'كنبة ٣ مقاعد + ٢ كرسي — حالتهم زي الجديد',                        8500, 'سحب طلب، استلام أكتوبر اللي فات. لون بيج هادي.', true],
            ['furniture',   'سرير كبير ٢ × ١٫٨٠ + مرتبة طبية',                                 6500, 'المرتبة عمرها سنة بس. سحب جوّاز. ', true],
            ['furniture',   'دولاب ٦ ضلف خشب موسكي',                                          4200, 'مقاس ٢٫٤٠ متر. حالة كويسة.', true],
            ['furniture',   'سفرة ٨ كراسي خشب زان',                                            5500, null, true],
            ['furniture',   'مكتب كومبيوتر زاوية + كرسي مكتب',                                 1900, 'ينفع لشغل من البيت.', true],
            ['furniture',   'مرتبة طبية مديكال ٢ × ١٫٨٠ — جديدة بكرتونة',                     4500, 'الفاتورة موجودة، عليها ضمان.', false],

            ['vehicles',    'هيونداي النترا 2018 — وارد فابريكا',                              285000, 'الفحص في أي مكان. ماشية ٩٠ ألف. أوتوماتيك.', true],
            ['vehicles',    'موتوسيكل بجاج CT 100 موديل 2022',                                 23500, 'ماشية ٧٠٠٠ كم بس. حالة ممتازة.', true],
            ['vehicles',    'عجلة جبلية هيد رود استعمال شهرين',                                 2800, null, true],
            ['vehicles',    'إطارات ميشلان ١٧ بوصة — استعمال ٥٠٠٠ كم',                          3500, 'الـ٤ بسعر واحد.', true],

            ['home',        'غسالة LG فول أوتوماتيك ٨ كيلو',                                   5800, 'شغّالة بكفاءة، السبب: غيرت بأكبر.', true],
            ['home',        'تكييف يونيون اير ١٫٥ حصان كول/هوت',                              7900, 'تركيب أصلي ٢٠٢١، نضيف وفلتر متغير.', true],
            ['home',        'دفاية كهربا حديد ٢٠٠٠ وات',                                        400, null, true],
            ['home',        'ميكروويف شارب ٢٥ لتر',                                            1500, 'شغّال تمام، استعمال نضيف.', true],
            ['home',        'بوتاجاز يونيفرسال ٥ شعلة + شفاط',                                4200, 'سحب نقلة، أوضة طبخ.', true],

            ['clothing',    'فستان سواريه أوف وايت مقاس ميديوم',                              1200, 'لُبس مرة واحدة. شغل حرير.', true],
            ['clothing',    'جاكيت شتوي رجالي M جلد طبيعي',                                    900, null, true],
            ['clothing',    'حذاء نايك Air Max 270 مقاس 42',                                  2100, 'أوريجينال من الإمارات. لُبس قليل.', true],
            ['clothing',    'بدلة كحلي رجالي مقاس 50 — لُبست مرة في فرح',                     1400, 'الجاكيت والبنطلون شغل أنيق.', true],
            ['clothing',    'حقيبة يد نسائي شانيل ريبليكا A+',                                 850, 'مش أصلي بس شغلها زي الأوريجينال.', true],

            ['baby',        'عربية أطفال شيكو كومبيلت',                                       2500, 'الكرسي والسلة معاها. حالة ممتازة.', true],
            ['baby',        'كرسي أكل أطفال خشب — قابل للتعديل',                              650, null, true],
            ['baby',        'لبس بيبي ولادي ٠-٦ شهور — لوط كامل',                              400, '٢٠ قطعة، أغلبهم لُبس قليل.', true],
            ['baby',        'مرتبة سرير أطفال إسفنج طبي',                                       550, null, true],

            ['books',       'مجموعة روايات نجيب محفوظ — ١٢ كتاب',                              900, 'حالة الكتب ممتازة، الأغلفة سليمة.', true],
            ['books',       'مذكرات الثانوية العامة كاملة — ٢٠٢٤',                              250, 'كل المواد علمي علوم. شركة الأضواء.', true],
            ['books',       'دكتور CCNA + كتب نتورك',                                            450, 'مفيد جدًا لطلاب IT.', true],

            ['sports',      'دمبلز معدل ٢ × ١٠ كيلو',                                            550, 'حالة ممتازة، استعمال بيتي.', true],
            ['sports',      'سيكلة ثابتة Spinning بحالة كويسة',                                  3200, null, true],
            ['sports',      'كرة قدم نايك ميرلين — مقاس 5',                                       450, 'جديدة بالعلبة.', false],

            ['pets',        'كلب لابرادور صغير ٣ شهور — تطعيمات كاملة',                         2800, 'مرح ولعّيب. عاوزله بيت كويس.', true],
            ['pets',        'قطة فارسي ٤ شهور أبيض',                                            1500, 'تطعيمات كاملة، آكل دراي.', true],
            ['pets',        'قفص طيور كبير + علاّقة',                                            350, null, true],

            ['real_estate', 'شقة للإيجار ١٢٠م — قسم أول بنها',                                  4500, 'دور ثالث، تشطيب لوكس، فيها مطبخ مجهز.', true],
            ['real_estate', 'شقة تمليك ١٥٠م — كفر سعد',                                       950000, 'استلام فوري، التشطيب نص تشطيب.', true],
            ['real_estate', 'محل تجاري للإيجار ٢٥م — شارع الجيش',                              8000, 'موقع مميز جدًا، أوّل شارع رئيسي.', true],
            ['real_estate', 'فيلا للبيع ٣٠٠م — كفر شبين',                                   3800000, 'دورين + حديقة. مفروشة جزئيًا.', true],

            ['jobs',        'مدرّس رياضيات للثانوية العامة — جلسات أونلاين',                    150, 'الحصة ١٥٠ ج / ساعة. خبرة ١٠ سنين.', true],
            ['jobs',        'سايس عربيات بالساعة — شغل نضيف وأمين',                              80, null, true],
            ['jobs',        'مصممة جرافيك فري لانس — لوجوهات وسوشيال ميديا',                  500, 'ابعتلي بريف وانا أبعتلك السعر.', true],
            ['jobs',        'دروس تأسيس انجليزي ابتدائي وإعدادي',                              100, null, true],

            ['other',       'دراجة هوائية للأطفال ٦-٩ سنين',                                   600,  'تلت كاوتش، نضيفة جدًا.', true],
            ['other',       'بوكس Air Fryer فيليبس ٥ لتر',                                    2200, 'استعمال شهرين بس.', true],
            ['other',       'ساعة ذكية Xiaomi Mi Band 7 — جديدة',                              800, 'مفيش استعمال، علبة مقفولة.', false],
        ];

        // ── Buy / wanted ──────────────────────────────────────────────────
        $buy = [
            ['mobile',      'مطلوب آيفون مستعمل من 11 لـ 13 — حالة كويسة',                     null, 'لو عند حد ابعتلي السعر والصور لو سمحت.'],
            ['electronics', 'محتاج لاب توب شغل برمجة — i5 / 8 رام بحد أقصى ١٠٠٠٠ ج',           10000, null],
            ['furniture',   'مطلوب سرير ١٢٠ سم بحالة كويسة',                                    null, null],
            ['baby',        'محتاج عربية بيبي مستعملة بحالة كويسة',                            1200, null],
            ['vehicles',    'مطلوب موتوسيكل ١٠٠ سي سي — لشغل توصيل',                          15000, 'الفحص في أي مكان.'],
            ['home',        'مطلوب تكييف ١٫٥ حصان مستعمل',                                    5000, null],
            ['real_estate', 'مطلوب إيجار شقة ١٠٠م في بنها — لعروسين',                         3500, 'مفروشة أو فاضية، الميزانية للإيجار الشهري.'],
            ['books',       'محتاج كتب أولى ثانوي علمي علوم',                                    null, null],
            ['other',       'مطلوب باور بانك ٢٠٠٠٠ مللي أمبير',                                  400, null],
        ];

        // ── Lost ───────────────────────────────────────────────────────────
        $lost = [
            ['other',       'ضاع موبايل سامسونج A52 أزرق — قسم أول بنها',                       'لو حد لقاه يتواصل ربنا يعوضه. فيه صور مهمة جدًا. مكافأة مجزية.'],
            ['other',       'ضاعت محفظة سوداء فيها بطاقات شخصية',                              'حول جامعة بنها. ابعتلي لو لقيتها — البطاقة باسم محمد ع.'],
            ['pets',        'ضاعت قطة شيرازي بيضا — منطقة شبين القناطر',                         'بترد على اسم "لولي". مكافأة لمن يجدها.'],
            ['other',       'ضاع كاوتش عجلة في الميدان قدام محطة القطار',                     null],
            ['other',       'ضاعت مفاتيح فيها ٤ مفاتيح وميدالية مرسيدس',                        'حوالين شارع فريد ندا.'],
        ];

        // ── Found ──────────────────────────────────────────────────────────
        $found = [
            ['other',       'لقطنا كارت بنك القاهرة باسم "أحمد م." قدام مترو السوق',         'صاحبه يتواصل ويوصفه يستلمه.'],
            ['other',       'لقيت بطاقة شخصية باسم "سارة ع." — جمب جامعة بنها',                'محتفظ بيها لحد ما صاحبتها تتواصل.'],
            ['pets',        'لقينا كلب بلدي صغير في شارع الجيش — معاه طوق أزرق',              'لو كلب حد ولاد الحلال يتواصل.'],
            ['other',       'لقيت مفاتيح عربية تويوتا قدام صيدلية العزبي',                       null],
        ];

        $created = 0;

        foreach ($sale as $i => [$cat, $title, $price, $desc, $neg]) {
            $u = $users->random();
            $z = $zones->random();
            $createdAt = now()->subDays(random_int(0, 45))->subHours(random_int(0, 23))->subMinutes(random_int(0, 59));
            $hasOwnCoords = random_int(1, 100) <= 35;
            [$lat, $lng] = $hasOwnCoords ? $this->jitter($z) : [null, null];

            Listing::create([
                'user_id'          => $u->id,
                'zone_id'          => $z->id,
                'lat'              => $lat,
                'lng'              => $lng,
                'kind'             => 'sale',
                'category'         => $cat,
                'title'            => $title,
                'description'      => $desc,
                'price'            => $price,
                'currency'         => 'EGP',
                'negotiable'       => $neg,
                'contact_phone'    => $u->phone,
                'contact_whatsapp' => random_int(0, 1) ? $u->phone : null,
                'status'           => $i < 4 ? 'sold' : 'active', // a few are marked sold for realism
                'views'            => random_int(8, 720),
                'expires_at'       => $createdAt->copy()->addDays(60),
                'featured_until'   => ($i % 9 === 0) ? now()->addDays(random_int(2, 14)) : null,
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);
            $created++;
        }

        foreach ($buy as [$cat, $title, $price, $desc]) {
            $u = $users->random();
            $z = $zones->random();
            $createdAt = now()->subDays(random_int(0, 30))->subHours(random_int(0, 23));

            Listing::create([
                'user_id'          => $u->id,
                'zone_id'          => $z->id,
                'kind'             => 'buy',
                'category'         => $cat,
                'title'            => $title,
                'description'      => $desc,
                'price'            => $price,
                'currency'         => 'EGP',
                'negotiable'       => true,
                'contact_phone'    => $u->phone,
                'contact_whatsapp' => random_int(0, 1) ? $u->phone : null,
                'status'           => 'active',
                'views'            => random_int(5, 220),
                'expires_at'       => $createdAt->copy()->addDays(60),
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);
            $created++;
        }

        foreach ($lost as [$cat, $title, $desc]) {
            $u = $users->random();
            $z = $zones->random();
            $createdAt = now()->subDays(random_int(0, 14))->subHours(random_int(0, 23));
            [$lat, $lng] = $this->jitter($z);

            Listing::create([
                'user_id'          => $u->id,
                'zone_id'          => $z->id,
                'lat'              => $lat,
                'lng'              => $lng,
                'kind'             => 'lost',
                'category'         => $cat,
                'title'            => $title,
                'description'      => $desc,
                'currency'         => 'EGP',
                'negotiable'       => false,
                'contact_phone'    => $u->phone,
                'contact_whatsapp' => $u->phone,
                'status'           => 'active',
                'views'            => random_int(30, 900),
                'expires_at'       => $createdAt->copy()->addDays(30),
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);
            $created++;
        }

        foreach ($found as [$cat, $title, $desc]) {
            $u = $users->random();
            $z = $zones->random();
            $createdAt = now()->subDays(random_int(0, 10))->subHours(random_int(0, 23));
            [$lat, $lng] = $this->jitter($z);

            Listing::create([
                'user_id'          => $u->id,
                'zone_id'          => $z->id,
                'lat'              => $lat,
                'lng'              => $lng,
                'kind'             => 'found',
                'category'         => $cat,
                'title'            => $title,
                'description'      => $desc,
                'currency'         => 'EGP',
                'negotiable'       => false,
                'contact_phone'    => $u->phone,
                'contact_whatsapp' => $u->phone,
                'status'           => 'active',
                'views'            => random_int(20, 600),
                'expires_at'       => $createdAt->copy()->addDays(30),
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);
            $created++;
        }

        // Bust map cache so new listings show up immediately
        \Illuminate\Support\Facades\Cache::forget('map-listings:v2');

        $this->command?->info("Seeded {$created} marketplace listings.");
    }

    /** Random point within roughly ±250m of the zone centroid. */
    private function jitter(Zone $zone): array
    {
        $dLat = (random_int(-220, 220)) / 100000;
        $dLng = (random_int(-220, 220)) / 100000;
        return [
            (float) $zone->lat + $dLat,
            (float) $zone->lng + $dLng,
        ];
    }
}
