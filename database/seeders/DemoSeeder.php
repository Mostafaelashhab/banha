<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use App\Services\BadgeService;
use App\Services\VerificationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $zones    = Zone::orderBy('sort')->get();
        $products = Product::all();

        // ─── Users ─────────────────────────────────────
        $users = $this->seedUsers($zones);

        // ─── Posts ─────────────────────────────────────
        $this->seedPosts($users, $zones);

        // ─── Prices ────────────────────────────────────
        $this->seedPrices($users, $products, $zones);

        // ─── Alerts ────────────────────────────────────
        $this->seedAlerts($users, $zones);

        // ─── Recompute hot scores + tier upgrades ─────
        Post::query()->orderByDesc('id')->chunk(100, function ($posts) {
            foreach ($posts as $post) {
                $post->recomputeHotScore();
                $post->save();
            }
        });

        foreach ($users as $u) {
            BadgeService::onPost($u);
            BadgeService::onComment($u);
            BadgeService::onPriceSubmit($u);
            BadgeService::onReputationChange($u);
        }
    }

    private function seedUsers($zones): array
    {
        $names = [
            // [username, persona]
            ['mahmoud_a',       'student'],
            ['ahmed_aly',       'worker'],
            ['mariam_sayed',    'student'],
            ['nora_h',          'homemaker'],
            ['sara_mohamed',    'student'],
            ['youssef_ibrahim', 'merchant'],
            ['rana_fouad',      'student'],
            ['kareem_sami',     'worker'],
            ['hossam_reda',     'merchant'],
            ['omar_tarek',      'student'],
            ['eman_nabil',      'homemaker'],
            ['fatma_abdullah',  'student'],
            ['seif_eldin',      'student'],
            ['dina_maged',      'worker'],
            ['hady_ayman',      'student'],
            ['yasmin_khaled',   'student'],
            ['ali_mostafa',     'merchant'],
            ['mohamed_essam',   'worker'],
            ['ola_naser',       'homemaker'],
            ['ramy_atef',       'worker'],
            ['hana_galal',      'student'],
            ['tarek_lotfy',     'merchant'],
            ['aya_hossam',      'student'],
            ['amir_zaki',       'worker'],
            ['salma_emad',      'student'],
            ['hesham_qasem',    'merchant'],
            ['ghada_aly',       'homemaker'],
            ['marwan_diab',     'student'],
            ['rola_kamal',      'worker'],
            ['mostafa_elashhab','worker'],
            ['nada_hatem',      'student'],
            ['walid_safwat',    'worker'],
            ['heba_anwar',      'homemaker'],
            ['khaled_fouad',    'merchant'],
            ['noha_sami',       'student'],
            ['baha_eldin',      'worker'],
            ['aliaa_ramadan',   'student'],
            ['gamal_abdo',      'merchant'],
            ['rania_helmy',     'homemaker'],
            ['ezz_eldin',       'student'],
            ['shimaa_adel',     'worker'],
            ['mahmoud_qenawy',  'merchant'],
            ['layla_amgad',     'student'],
            ['waleed_salem',    'worker'],
            ['mai_kamal',       'homemaker'],
            ['islam_essam',     'student'],
            ['amira_mansour',   'student'],
            ['hassan_aboulnaga','merchant'],
            ['ranya_zaky',      'homemaker'],
            ['kamal_eltayeb',   'merchant'],
        ];

        $arabicSeeds = [
            'بنهاوي_أصيل','قطار_شاطر','فولـ_صباحي','كشري_عاشق','جامعة_بنها','محطة_مغمور',
            'قسم_أول_ساكن','شارع_الجيش','زراعي_عابر','نيلي_حر','تمر_حنّاوي','شبيني_متروح',
        ];

        $users = [];
        foreach ($names as $i => [$username, $persona]) {
            $zone = $zones->random();
            $phone = '01' . str_pad((string) random_int(0, 9999999999), 9, '0', STR_PAD_LEFT);

            $u = User::create([
                'phone'       => $phone,
                'username'    => $username,
                'password'    => Hash::make('demo1234'),
                'zone_id'     => $zone->id,
                'persona'     => $persona,
                'avatar_seed' => $arabicSeeds[$i % count($arabicSeeds)] . '_' . random_int(100, 9999),
                'reputation'  => random_int(10, 380),
                'level'       => random_int(1, 6),
                'created_at'  => now()->subDays(random_int(1, 90))->subHours(random_int(0, 23)),
            ]);

            VerificationService::markBronzeOnSignup($u);
            BadgeService::onSignup($u);

            $users[] = $u;
        }
        return $users;
    }

    private function seedPosts(array $users, $zones): void
    {
        // [category, title (or null), body]
        $content = [
            // Confessions
            ['confession', null, 'أنا في تجارة بنها سنة تالتة وحاسس إني تايه. الكلية صعبة والأهل ضغطهم عالي. حد جرّب الموضوع ده؟'],
            ['confession', null, 'حبيبتي في الكلية مش عارفة أعرّفها لأمي. عندي خوف من رد فعلها. مين عنده نفس المشكلة؟'],
            ['confession', null, 'أنا متخرج من سنتين ولسه مش لاقي شغل في بنها. كل شغل في القاهرة وانا مش قادر اروح كل يوم.'],
            ['confession', null, 'بقالي أسبوع مش نايم بسبب القلق على نتيجة الترم. مين بيحس بنفس الإحساس؟'],
            ['confession', null, 'أنا طالبة هندسة وأهلي عايزني اتجوز. مش عايزة أسيب الكلية. ايه رأيكم؟'],
            ['confession', null, 'قعدت ١٠ سنين في القاهرة ورجعت بنها — صدمة. المدينة اتغيرت كتير.'],
            ['confession', null, 'بحب جامعة بنها بصراحة. الناس أبسط، الكلية أقرب، والكافيهات أرخص.'],

            // Questions
            ['question', 'أحسن دكتور أسنان في بنها؟', 'محتاج دكتور أسنان شاطر وأسعاره معقولة. اللي يعرف يقولّي.'],
            ['question', 'فين أرخص ملازم كلية تجارة؟', 'بدور على ملازم تجارة سنة تانية بسعر كويس. حد يعرف؟'],
            ['question', 'في حد بيوصّل من بنها للقاهرة بسعر مناسب؟', 'محتاج وسيلة مواصلات ثابتة من بنها للسلام كل يوم.'],
            ['question', 'فطار حلو في بنها يفتح بدري؟', 'بدور على مكان فطار يفتح ٧ الصبح في بنها.'],
            ['question', 'أحسن دكتور أطفال في القليوبية؟', 'بنتي عيانة ومحتاجة دكتور أطفال شاطر. يفضل في بنها أو طوخ.'],
            ['question', 'في كافيه هاديء للمذاكرة في بنها؟', 'محتاج كافيه فيه واي فاي كويس وهاديء عشان أذاكر.'],
            ['question', null, 'فين أحسن جيم في بنها؟ بالأسعار لو سمحتم.'],
            ['question', null, 'حد جرّب فول "كوبيرا"؟ سمعت عنه كتير وعايز رأي حقيقي.'],
            ['question', 'قطار بنها القاهرة بيتأخر كتير؟', 'هل القطار رايق ولا أركب ميكروباص أحسن؟ شغلي ٩ الصبح.'],

            // Complaints
            ['complaint', null, 'كهربا قطعت من ٣ ساعات في قسم تاني. حد عنده تحديث؟'],
            ['complaint', null, 'زحمة شديدة على الزراعي النهاردة. ٤٥ دقيقة لما عدّيت من بنها لطوخ.'],
            ['complaint', null, 'مياه قطعت من إمبارح في شارع الجيش. مين تاني عنده نفس المشكلة؟'],
            ['complaint', 'الزحمة على كوبري بنها كل يوم', 'الناس بتتأخر على الشغل بسبب الزحمة. لازم يحلوا الموضوع ده.'],
            ['complaint', null, 'الميكروباصات في ميدان المحطة بتاخد ضعف السعر بعد الساعة ٩. ده مش طبيعي.'],
            ['complaint', null, 'النضافة في شارع فريد ندا حالة. القمامة متراكمة من أسبوع.'],

            // Reviews
            ['review', 'كافيه نوار في شارع فريد ندا', 'مكان جميل وهاديء، الأسعار معقولة، والقهوة لذيذة. ينصح بيه للمذاكرة.'],
            ['review', 'كشري طارق في ميدان المحطة', 'الكشري عندهم لذيذ جداً وأسعارهم مناسبة. الطبق ٣٠ ج. ينفع للأكل أو التيك أواي.'],
            ['review', 'د. أحمد عبد الله — طب أطفال', 'دكتور رائع، يكشف بدقة وأسعاره مناسبة. الكشف ١٢٠ ج. عيادته جنب ميدان المحطة.'],
            ['review', 'مطعم عم سعيد — فول وطعمية', 'فطار بنهاوي أصيل. السندوتش بـ ٥ ج والفول الطازة جامد. بيفتح ٦ الصبح.'],
            ['review', 'صيدلية الشفاء — شارع فريد ندا', 'بيشتغلوا ٢٤ ساعة، فيهم كل الأدوية، والأسعار مظبوطة. توصيل مجاني فوق ١٠٠ ج.'],
            ['review', null, 'جربت محل "أبو حسن" للمشويات في بنها. والله أحسن من الإسكندرية. الفراخ المشوية ١٢٠ ج.'],
            ['review', null, 'سنتر أبو هاشم لدروس تجارة محترم جداً. د. هاني شاطر في المحاسبة.'],

            // News
            ['news', 'افتتاح فرع جديد لـ Talabat في بنها', 'فرع جديد افتتح في شارع الجيش. أول طلب بـ ٢٠٪ خصم لمدة أسبوع.'],
            ['news', 'مهرجان الأكل البنهاوي السبت الجاي', 'في ميدان المحطة. أكل بنهاوي تقليدي + موسيقى شعبية. الدخول مجاني.'],
            ['news', 'جامعة بنها فتحت تنسيق جديد للماجستير', 'كلية تجارة فتحت برنامج ماجستير في إدارة الأعمال بالتعاون مع جامعة في ألمانيا.'],
            ['news', null, 'محاضرة مجانية في كلية تجارة عن ريادة الأعمال يوم الخميس الجاي ٤ مساءً.'],
            ['news', null, 'بلدية بنها بتطور شارع الجيش — هيتقفل لمدة أسبوع في الجزء بين كوبري بنها وميدان المحطة.'],

            // Memes / casual
            ['meme', null, 'لما تكون في بنها وتقولّك حد "البيت بعيد شوية" يعني هتمشي ٤٠ دقيقة 😂'],
            ['meme', null, 'الفرق بين بنهاوي حقيقي وبنهاوي مزيف: البنهاوي الحقيقي بيفهم لما حد يقول "ميدان المحطة" بدون توضيح.'],
            ['meme', null, 'الكهربا قطعت في رمضان قبل المغرب بـ ١٠ دقايق = نهاية العالم 🤡'],

            // Help
            ['help', 'فقدت موبايلي في القطار', 'فقدت iPhone 13 في قطار بنها-القاهرة الساعة ٨ الصبح. لو حد لقاه يكلمني الرقم في البايو.'],
            ['help', 'بدور على شريك سكن', 'طالب في كلية تجارة بدور على شريك سكن في بنها. الإيجار حصة. كلمني لو مهتم.'],
            ['help', null, 'محتاج توصيل من بنها للمنصورة الجمعة الجاية. حد عنده عربية رايح؟'],

            // Sale
            ['sale', 'لاب توب HP للبيع', 'لاب توب HP Pavilion 15 — حالته ممتازة، استخدام ٦ شهور. السعر ١٢٫٠٠٠ ج للجاد.'],
            ['sale', 'دراجة هوائية', 'دراجة هوائية ماركة Trinx — حالتها كويسة. ١٫٨٠٠ ج. للبيع بسبب السفر.'],
            ['sale', null, 'كتب كلية تجارة سنة أولى وتانية للبيع. كلهم في حالة ممتازة. ٣٠٠ ج للسنتين.'],
        ];

        $now = now();
        foreach ($content as $i => [$cat, $title, $body]) {
            $author = $users[array_rand($users)];
            $zone   = $zones->random();

            // Some are anonymous (especially confessions)
            $isAnon = $cat === 'confession' || ($i % 7 === 0);

            $created = $now->copy()->subMinutes(random_int(5, 60 * 24 * 14)); // last 14 days

            $post = Post::create([
                'user_id'        => $author->id,
                'zone_id'        => $zone->id,
                'is_anonymous'   => $isAnon,
                'anon_seed'      => $isAnon ? \App\Support\AnonSeed::generate() : null,
                'category'       => $cat,
                'title'          => $title,
                'body'           => $body,
                'status'         => 'active',
                'upvotes'        => random_int(2, 250),
                'downvotes'      => random_int(0, 12),
                'comments_count' => 0,
                'created_at'     => $created,
                'updated_at'     => $created,
            ]);

            // Comments per category (contextually relevant)
            $commentPools = [
                'confession' => [
                    'ربنا معاك يا صاحبي', 'مش لوحدك في الموضوع ده',
                    'شكراً إنك بُحت — مهم نتكلم', 'خد وقتك، مش لازم تستعجل',
                    'صبر ودوام، الدنيا هتفرج', 'ده اللي حصل معايا برضو',
                    'إنت أقوى مما تتخيل', 'الكلام ده محتاج جرأة فعلاً',
                ],
                'question' => [
                    'معرفش بصراحة، عايز أعرف برضو', 'تابع التعليقات، أكيد حد هيرد',
                    'جرّب تسأل في جامعة بنها', 'لو لقيت إجابة قولّي',
                    'أنا عارف، كلمني خاص', 'بصراحة كنت بدوّر على نفس الإجابة',
                    'اللي قلته في البوست صح', 'في حد سأل نفس السؤال قبل كده',
                ],
                'complaint' => [
                    'بصراحة مزعج فعلاً', 'نفس الشكوى عندنا',
                    'والله الناس تعبت', 'لازم حد يحرّك ساكن',
                    'معاك حق ١٠٠٪', 'المفروض الجهات المسؤولة تتحرّك',
                    'كل يوم نفس المشكلة', 'صبرك يا صديقي',
                ],
                'review' => [
                    'جربته وفعلاً كده', 'شكراً للريفيو الصادق',
                    'هاجرّب بناءً على كلامك', 'أنا بحب المكان ده جداً',
                    'فعلاً ينصح بيه', 'الأسعار اتغيرت دلوقتي للأسف',
                    'تجربتي كانت كويسة برضو', 'المكان ده الأفضل في بنها',
                ],
                'news' => [
                    'خبر كويس، شكراً للنشر', 'إن شاء الله تكون فايدة',
                    'مين عنده تفاصيل أكتر؟', 'الخبر ده فعلاً مهم',
                    'متابعين', 'هل في رابط رسمي؟',
                ],
                'meme' => [
                    '😂😂😂', 'موت من الضحك',
                    'ده اللي بيحصل بالظبط', 'كلامك صح ١٠٠٪',
                    'البنهاويين بس اللي بيفهموا', 'حرفياً 🤣',
                ],
                'help' => [
                    'لو لقيت هكلمك', 'أنا متابع، خلّي عينيك مفتوحة',
                    'جرّب تتواصل مع البلدية', 'ربنا يقدّرك',
                    'كلمني خاص لو محتاج مساعدة', 'إن شاء الله تلاقي',
                ],
                'sale' => [
                    'السعر مناسب', 'ممكن تفاصيل أكتر؟',
                    'كلمني خاص', 'هل في صور إضافية؟',
                    'الجادين بس', 'مهتم — ابعتلي تفاصيل التواصل',
                ],
            ];
            $samples = $commentPools[$cat] ?? $commentPools['question'];

            $cCount = random_int(0, 8);
            for ($c = 0; $c < $cCount; $c++) {
                $cAuthor  = $users[array_rand($users)];
                $cIsAnon  = ($cat === 'confession' && $c % 2 === 0);
                $cCreated = $created->copy()->addMinutes(random_int(2, 60 * 12));

                Comment::create([
                    'post_id'      => $post->id,
                    'user_id'      => $cAuthor->id,
                    'is_anonymous' => $cIsAnon,
                    'anon_seed'    => $cIsAnon ? \App\Support\AnonSeed::generate() : null,
                    'body'         => $samples[array_rand($samples)],
                    'created_at'   => $cCreated,
                    'updated_at'   => $cCreated,
                ]);
            }

            $post->update(['comments_count' => $cCount]);
        }
    }

    private function seedPrices(array $users, $products, $zones): void
    {
        $now = now();

        // realistic egyptian prices (roughly based on early 2026 reality)
        $base = [
            'tomato'         => [10,  14],
            'potato'         => [12,  16],
            'onion'          => [14,  18],
            'cucumber'       => [10,  14],
            'green-pepper'   => [18,  24],
            'eggplant'       => [10,  13],
            'apple'          => [55,  85],
            'banana'         => [22,  30],
            'orange'         => [15,  22],
            'chicken'        => [88,  100],
            'beef'           => [380, 450],
            'tilapia'        => [110, 150],
            'eggs'           => [80,  92],
            'milk'           => [42,  52],
            'cooking-oil'    => [88,  105],
            'rice'           => [45,  55],
            'sugar'          => [32,  40],
            'tea-500g'       => [110, 140],
            'french-bread'   => [3,   5],
            'baladi-bread'   => [1,   2],
            'gasoline-80'    => [13.50, 13.50],
            'gasoline-92'    => [15.50, 15.50],
            'gasoline-95'    => [17,    17],
            'diesel'         => [13.50, 13.50],
            'gas-cylinder'   => [200, 220],
        ];

        foreach ($products as $product) {
            // Use slug to find a base, otherwise default range
            $matchedKey = collect(array_keys($base))->first(function ($k) use ($product) {
                return Str::contains($product->slug, $k);
            });
            [$min, $max] = $matchedKey ? $base[$matchedKey] : [10, 100];

            // Generate 5-15 reports per product, spread over the last 7 days
            $reports = random_int(5, 15);
            for ($i = 0; $i < $reports; $i++) {
                $author  = $users[array_rand($users)];
                $zone    = $zones->random();
                $price   = round($min + ($max - $min) * (mt_rand(0, 100) / 100), 2);
                $created = $now->copy()->subDays(random_int(0, 7))->subHours(random_int(0, 23));

                Price::create([
                    'product_id' => $product->id,
                    'zone_id'    => $zone->id,
                    'user_id'    => $author->id,
                    'price'      => $price,
                    'shop_name'  => array_rand(array_flip([
                        'السوق البلدي', 'سوبر ماركت العائلة', 'بقالة عم سيد', 'محل أبو محمد',
                        'سوبر جوميا', 'البقال الكبير', 'شارع الجيش', 'فاميلي ماركت',
                    ])),
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
            }
        }
    }

    private function seedAlerts(array $users, $zones): void
    {
        $samples = [
            ['traffic',     'زحمة شديدة على كوبري بنها — اتجاه القاهرة، تأخير ٤٠ دقيقة'],
            ['traffic',     'الطريق الزراعي مقفول جزئياً قبل طوخ بسبب حادثة'],
            ['traffic',     'زحمة في ميدان المحطة بسبب أعمال تطوير'],
            ['electricity', 'كهربا قطعت في قسم تاني من ٣ ساعات — مش راجعة لسه'],
            ['electricity', 'الكهربا رجعت دلوقتي في شارع الجيش، الحمد لله'],
            ['electricity', 'تخفيف أحمال متوقع الساعة ٣ في قسم أول ٤٥ دقيقة'],
            ['water',       'مياه قطعت من امبارح في شارع فريد ندا'],
            ['water',       'ضغط المياه ضعيف جداً في كل قسم تاني'],
            ['accident',    'حادثة تصادم على طريق شبين — احذروا'],
            ['accident',    'حادثة في ميدان المحطة، فيه سيارات إسعاف'],
            ['checkpoint',  'كمين على مدخل بنها من ناحية الزراعي — جاهزوا الرخص'],
            ['checkpoint',  'كمين متابعة في طوخ — ميدان البلد'],
            ['other',       'كلب ضال في شارع الجيش، حذاري على الأطفال'],
            ['other',       'دوريات أمنية كتير اليومين دول، ممنوع التجمع'],
        ];

        foreach ($samples as [$type, $desc]) {
            $author  = $users[array_rand($users)];
            $zone    = $zones->random();
            $age     = random_int(5, 60 * 5); // last 5 hours
            $created = now()->subMinutes($age);

            $confirms = random_int(1, 8);
            $verified = $confirms >= 3;

            $alert = Alert::create([
                'user_id'        => $author->id,
                'zone_id'        => $zone->id,
                'type'           => $type,
                'description'    => $desc,
                'confirmations'  => $confirms,
                'is_verified'    => $verified,
                'expires_at'     => now()->addHours($verified ? 12 : 6),
                'created_at'     => $created,
                'updated_at'     => $created,
            ]);

            // Add real confirmation rows for the count - 1 (since the poster's "1" is implicit)
            $confirmedBy = collect($users)
                ->where('id', '!=', $author->id)
                ->shuffle()
                ->take($confirms - 1);

            foreach ($confirmedBy as $u) {
                DB::table('alert_confirmations')->insertOrIgnore([
                    'alert_id'   => $alert->id,
                    'user_id'    => $u->id,
                    'created_at' => $created,
                    'updated_at' => $created,
                ]);
            }
        }
    }
}
