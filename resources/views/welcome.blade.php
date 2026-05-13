<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" media="(prefers-color-scheme: light)" content="#FFF7F1">
    <meta name="theme-color" media="(prefers-color-scheme: dark)"  content="#0B0B0C">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>بنهاوي · مدينتك على راحة إيدك · بنها · القليوبية</title>
    <meta name="description" content="بنهاوي — أول منصة هايبر لوكال في بنها والقليوبية. تنبيهات لحظية للزحمة والكهربا، رادار أسعار، اعترافات مجهولة، ودكاترة وكافيهات. كل اللي بيحصل في حيك دلوقتي.">
    <meta name="keywords" content="بنها, القليوبية, جامعة بنها, تطبيق بنها, ضحجة بنها, أخبار بنها, أسعار السوق البلدي, زحمة كوبري بنها, اعترافات بنها, طوخ, القليوب, شبرا الخيمة, قها, كفر شكر, مصر">
    <meta name="author" content="Elashhab">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="generator" content="Banhawy">
    <meta http-equiv="content-language" content="ar-EG">
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="بنهاوي · بنها، حقيقي، دلوقتي">
    <meta property="og:description" content="كل اللي بيحصل في بنها دلوقتي — ترند، أسعار، زحمة، اعترافات. ابدأ في 30 ثانية.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="بنهاوي">
    <meta property="og:locale" content="ar_EG">
    <meta property="og:image" content="{{ url('/icons/icon-512.png') }}">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:image:alt" content="شعار بنهاوي">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="بنهاوي · بنها، حقيقي، دلوقتي">
    <meta name="twitter:description" content="كل اللي بيحصل في بنها دلوقتي — ترند، أسعار، زحمة، اعترافات.">
    <meta name="twitter:image" content="{{ url('/icons/icon-512.png') }}">

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="بنهاوي">
    <meta name="mobile-web-app-capable" content="yes">

    {{-- Structured data: Organization + WebApplication --}}
    @php
        $ld = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type'         => 'Organization',
                    'name'          => 'بنهاوي',
                    'alternateName' => 'Banhawy',
                    'url'           => url('/'),
                    'logo'          => url('/icons/icon-512.png'),
                    'address' => [
                        '@type'           => 'PostalAddress',
                        'addressLocality' => 'بنها',
                        'addressRegion'   => 'القليوبية',
                        'addressCountry'  => 'EG',
                    ],
                ],
                [
                    '@type'                => 'WebApplication',
                    'name'                 => 'بنهاوي',
                    'url'                  => url('/'),
                    'applicationCategory'  => 'SocialNetworkingApplication',
                    'operatingSystem'      => 'Any',
                    'inLanguage'           => 'ar-EG',
                    'isAccessibleForFree'  => true,
                    'offers' => [
                        '@type'         => 'Offer',
                        'price'         => '0',
                        'priceCurrency' => 'EGP',
                    ],
                    'description' => 'منصة هايبر لوكال لمدينة بنها والقليوبية',
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden" data-install-prompt="auto">

    {{-- ─── NAV ─────────────────────────────────────────────── --}}
    <header class="fixed top-0 inset-x-0 z-50">
        <div class="mx-auto max-w-6xl px-4 pt-4">
            <nav class="glass rounded-full flex items-center justify-between pe-2 ps-5 py-2 shadow-lg">
                <a href="#" class="flex items-center gap-2 font-extrabold text-base text-ink-950">
                    <span class="w-9 h-9 rounded-xl brand-bg grid place-items-center shadow">
                        <span class="text-white font-black">ب</span>
                    </span>
                    <span>بنهاوي</span>
                </a>
                <div class="hidden md:flex items-center gap-7 text-sm text-ink-500">
                    <a href="#live"     class="hover:text-ink-950 transition">شوف بنفسك</a>
                    <a href="#zones"    class="hover:text-ink-950 transition">المناطق</a>
                    <a href="#voices"   class="hover:text-ink-950 transition">آراء</a>
                    <button type="button" onclick="window.banhawyInstall?.maybeShow()" class="hover:text-ink-950 transition inline-flex items-center gap-1">
                        <x-icon name="arrow-left" class="w-4 h-4 rotate-90"/>
                        حمّل
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm font-bold text-ink-500 hover:text-ink-950 px-3">دخول</a>
                    <a href="{{ route('signup') }}" class="btn-primary !py-2 !px-5 text-sm">
                        ابدأ
                        <x-icon name="arrow-left" class="w-4 h-4"/>
                    </a>
                </div>
            </nav>
        </div>
    </header>

    {{-- ─── HERO ────────────────────────────────────────────── --}}
    <section class="relative pt-32 md:pt-40 pb-16 md:pb-24">
        <div class="absolute inset-0 bg-grid opacity-40 [mask-image:radial-gradient(ellipse_at_top,black_30%,transparent_70%)]"></div>
        <div class="absolute -top-32 -end-32 w-[36rem] h-[36rem] rounded-full bg-coral-300/30 blur-3xl"></div>
        <div class="absolute top-40 -start-40 w-[28rem] h-[28rem] rounded-full bg-honey-300/30 blur-3xl"></div>

        <div class="relative mx-auto max-w-6xl px-5 grid lg:grid-cols-12 gap-10 items-center">

            {{-- Left: copy --}}
            <div class="lg:col-span-7 text-center lg:text-start">

                <div class="inline-flex items-center gap-2 bg-white/90 backdrop-blur rounded-full px-3 py-1.5 text-xs md:text-sm mb-7 border border-ink-950/5 shadow-sm rise rise-1">
                    <span class="relative w-2 h-2 rounded-full bg-mint-500 text-mint-500 pulse-ring"></span>
                    <span class="text-ink-500">شغّال دلوقتي · بنها · القليوبية</span>
                </div>

                <h1 class="text-5xl md:text-7xl lg:text-[5.5rem] font-black leading-[0.95] tracking-tight text-ink-950 rise rise-2">
                    بنها،
                    <span class="text-coral">حقيقي</span>،
                    <br>
                    دلوقتي.
                </h1>

                <p class="mt-7 text-base md:text-xl text-ink-500 max-w-xl mx-auto lg:mx-0 leading-relaxed rise rise-3">
                    أول منصة بتجمع كل اللي بيحصل في بنها في مكان واحد — ترند، أسعار، تنبيهات لحظية،
                    دكاترة، كافيهات، واعترافات مجهولة. <b class="text-ink-950">في ٣٠ ثانية انت جوة.</b>
                </p>

                <div class="mt-9 flex flex-wrap items-center justify-center lg:justify-start gap-3 rise rise-4">
                    <a href="{{ route('signup') }}" class="btn-primary !py-3 !px-7 text-base">
                        افتح حساب مجاناً
                        <x-icon name="arrow-left" class="w-4 h-4"/>
                    </a>
                    <a href="#live" class="btn-ghost !py-3 !px-6 text-base">
                        <x-icon name="flame" class="w-4 h-4 text-coral-500"/>
                        شوف بنفسك
                    </a>
                </div>

                {{-- Trust strip --}}
                <div class="mt-10 grid grid-cols-3 gap-4 max-w-md mx-auto lg:mx-0 rise rise-4">
                    @php
                        $trust = [
                            ['1,200+', 'بنهاوي مسجّل'],
                            ['9',      'مناطق'],
                            ['100%',   'مجاني'],
                        ];
                    @endphp
                    @foreach($trust as [$num, $lbl])
                        <div class="text-center lg:text-start">
                            <div class="text-2xl md:text-3xl font-black text-ink-950">{{ $num }}</div>
                            <div class="text-[11px] text-ink-500 mt-0.5">{{ $lbl }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: phone mockup --}}
            <div class="lg:col-span-5 flex justify-center lg:justify-end relative">

                {{-- Floating: confession --}}
                <div class="absolute -top-3 -start-3 lg:start-0 z-20 float-2 hidden sm:block">
                    <div class="card-light px-3 py-2.5 flex items-center gap-2.5 shadow-lg">
                        <span class="w-8 h-8 rounded-full pill-coral grid place-items-center">
                            <x-icon name="mask" class="w-3.5 h-3.5"/>
                        </span>
                        <div class="text-start">
                            <div class="text-[10px] text-ink-500">اعتراف · جامعة بنها</div>
                            <div class="text-xs font-bold text-ink-950">+182 صوت</div>
                        </div>
                    </div>
                </div>

                {{-- Floating: alert --}}
                <div class="absolute top-32 -end-3 lg:end-0 z-20 float-3 hidden sm:block">
                    <div class="card-light px-3 py-2.5 flex items-center gap-2.5 shadow-lg">
                        <span class="w-8 h-8 rounded-full pill-blush grid place-items-center">
                            <x-icon name="traffic" class="w-3.5 h-3.5"/>
                        </span>
                        <div class="text-start">
                            <div class="text-[10px] text-ink-500">كوبري بنها</div>
                            <div class="text-xs font-bold text-ink-950">+25 دقيقة</div>
                        </div>
                    </div>
                </div>

                {{-- Floating: price --}}
                <div class="absolute bottom-6 -start-2 lg:start-6 z-20 float-1 hidden sm:block">
                    <div class="card-light px-3 py-2.5 flex items-center gap-2.5 shadow-lg">
                        <span class="w-8 h-8 rounded-full pill-mint grid place-items-center">
                            <x-icon name="tag" class="w-3.5 h-3.5"/>
                        </span>
                        <div class="text-start">
                            <div class="text-[10px] text-ink-500">طماطم · السوق البلدي</div>
                            <div class="text-xs font-bold text-ink-950">١٢ ج/ك ↓</div>
                        </div>
                    </div>
                </div>

                <div class="phone float-1">
                    <div class="phone-screen">
                        <div class="flex items-center justify-between text-white/90 text-[11px] font-bold px-2 mb-3">
                            <span>09:41</span>
                            <span class="flex items-center gap-1.5">
                                <x-icon name="signal" class="w-3.5 h-3.5"/>
                                <x-icon name="wifi" class="w-3.5 h-3.5"/>
                                <x-icon name="battery" class="w-4 h-4"/>
                            </span>
                        </div>

                        <div class="flex items-center justify-between mb-3 px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-white/25 grid place-items-center text-xs font-black text-white">ب</span>
                                <span class="font-extrabold text-white">بنهاوي</span>
                            </div>
                            <button class="w-8 h-8 rounded-full bg-white/25 grid place-items-center text-white">
                                <x-icon name="bell" class="w-4 h-4"/>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="bg-ink-950 text-white rounded-2xl p-3">
                                <div class="text-[10px] text-white/60 mb-1">ترند النهاردة</div>
                                <div class="text-2xl font-black">٢٤٧</div>
                                <div class="text-[10px] text-coral-300">+18% ↑</div>
                            </div>
                            <div class="bg-ink-950 text-white rounded-2xl p-3">
                                <div class="text-[10px] text-white/60 mb-1">جنبك دلوقتي</div>
                                <div class="text-2xl font-black">٨١</div>
                                <div class="text-[10px] text-mint-100 inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-mint-100 animate-pulse"></span> live
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-3 mb-2">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-7 h-7 rounded-full bg-coral-100 text-coral-700 grid place-items-center">
                                    <x-icon name="mask" class="w-3.5 h-3.5"/>
                                </span>
                                <div class="flex-1">
                                    <div class="text-[11px] font-bold text-ink-950">مجهول · قسم أول</div>
                                    <div class="text-[10px] text-ink-500">من ٣ دقايق</div>
                                </div>
                                <span class="pill-mint text-[10px] font-bold px-2 py-0.5 rounded-full">+182</span>
                            </div>
                            <div class="text-[12px] leading-relaxed text-ink-950">شوفت أحسن كشري في بنها فين النهاردة؟</div>
                        </div>

                        <div class="bg-white rounded-2xl p-3">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-7 h-7 rounded-full bg-honey-300/40 text-coral-700 grid place-items-center">
                                    <x-icon name="tag" class="w-3.5 h-3.5"/>
                                </span>
                                <div class="flex-1">
                                    <div class="text-[11px] font-bold text-ink-950">سعر الطماطم</div>
                                    <div class="text-[10px] text-ink-500">السوق البلدي</div>
                                </div>
                                <span class="pill-blush text-[10px] font-bold px-2 py-0.5 rounded-full">١٢ج/ك</span>
                            </div>
                            <div class="text-[12px] leading-relaxed text-ink-950">نزل ٢ جنيه عن أمبارح · ٤ تأكيدات</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── LIVE TICKER ─────────────────────────────────────── --}}
    <section class="bg-ink-950 text-white py-3 overflow-hidden border-y border-white/5">
        <div class="marquee-track text-sm font-bold whitespace-nowrap">
            @php
                $ticks = [
                    ['flame', 'coral-400',  'ترند النهاردة في كلية تجارة'],
                    ['bolt',  'honey-400',  'كهربا رجعت في قسم تاني'],
                    ['tag',   'mint-500',   'الكشري في طارق نزل ٥ ج'],
                    ['traffic','blush-500', 'زحمة على كوبري بنها · +٢٥ د'],
                    ['mask',  'coral-400',  '٤٧ اعتراف جديد في الساعة الأخيرة'],
                    ['cart',  'honey-400',  'البطاطس · ١٤ ج/ك في السوق البلدي'],
                    ['stethoscope','mint-500','د. أحمد فاتح في عيادة شارع الجيش'],
                    ['coffee','coral-400',  'كافيه نوار · عرض ٢٠٪ للطلبة'],
                ];
            @endphp
            @for($i=0; $i<2; $i++)
                @foreach($ticks as [$ic, $color, $tx])
                    <span class="inline-flex items-center gap-2">
                        <x-icon :name="$ic" class="w-4 h-4 text-{{ $color }}"/>
                        <span class="text-white/85">{{ $tx }}</span>
                    </span>
                    <span class="text-white/20 px-1">●</span>
                @endforeach
            @endfor
        </div>
    </section>

    {{-- ─── LIVE PREVIEW ────────────────────────────────────── --}}
    <section id="live" class="relative py-24 md:py-32 bg-ink-950 text-white">
        <div class="absolute inset-0 bg-grid opacity-[.07]"></div>
        <div class="absolute top-0 left-0 right-0 h-32 bg-gradient-to-b from-cream-100 to-transparent"></div>
        <div class="absolute -top-40 start-1/4 w-[40rem] h-[40rem] rounded-full bg-coral-500/20 blur-3xl"></div>

        <div class="relative mx-auto max-w-6xl px-5">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-block text-coral-400 font-bold text-sm tracking-wider mb-3">شوف بنفسك</span>
                <h2 class="text-3xl md:text-5xl lg:text-6xl font-black leading-tight">
                    كل حاجة في بنها،
                    <br>
                    <span class="text-coral-400">بتحصل دلوقتي.</span>
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                {{-- 1: Confessions --}}
                <div class="rounded-3xl p-6 bg-white/[.04] border border-white/10 backdrop-blur hover:bg-white/[.06] transition reveal">
                    <div class="flex items-center justify-between mb-5">
                        <span class="icon-tile bg-coral-500/15 border-coral-500/30 text-coral-400">
                            <x-icon name="mask" class="w-5 h-5"/>
                        </span>
                        <span class="pill-coral text-[10px] font-bold px-2.5 py-1 rounded-full">حصري</span>
                    </div>
                    <h3 class="text-xl font-extrabold mb-2">حيطة الهمسات</h3>
                    <p class="text-white/60 text-sm leading-relaxed mb-5">بوّح بأي حاجة بشكل مجهول. AI بيحمي من التنمر قبل ما يوصل لحد.</p>

                    <div class="space-y-2">
                        <div class="bg-ink-900 rounded-2xl p-3 border border-white/5">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-6 h-6 rounded-full bg-coral-500/20 text-coral-400 grid place-items-center">
                                    <x-icon name="mask" class="w-3 h-3"/>
                                </span>
                                <span class="text-[11px] font-bold">قطار_شاطر_1078</span>
                                <span class="text-[10px] text-white/40 ms-auto">٣ د</span>
                            </div>
                            <div class="text-[12px] text-white/85 leading-relaxed">أنا فعلاً تايه في كلية الهندسة ومش عارف أعمل إيه</div>
                        </div>
                        <div class="bg-ink-900 rounded-2xl p-3 border border-white/5">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-6 h-6 rounded-full bg-honey-400/20 text-honey-400 grid place-items-center">
                                    <x-icon name="mask" class="w-3 h-3"/>
                                </span>
                                <span class="text-[11px] font-bold">فولـ_مغمور_4127</span>
                                <span class="text-[10px] text-white/40 ms-auto">٧ د</span>
                            </div>
                            <div class="text-[12px] text-white/85 leading-relaxed">حد جرّب فطار "أبو راجح" في شارع فريد ندا؟</div>
                        </div>
                    </div>
                </div>

                {{-- 2: Live Alerts --}}
                <div class="rounded-3xl p-6 bg-white/[.04] border border-white/10 backdrop-blur hover:bg-white/[.06] transition reveal">
                    <div class="flex items-center justify-between mb-5">
                        <span class="icon-tile bg-blush-500/15 border-blush-500/30 text-blush-500">
                            <x-icon name="bolt" class="w-5 h-5"/>
                        </span>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-mint-500/15 text-mint-100 border border-mint-500/30 inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-mint-500 animate-pulse"></span> LIVE
                        </span>
                    </div>
                    <h3 class="text-xl font-extrabold mb-2">تنبيهات لحظية</h3>
                    <p class="text-white/60 text-sm leading-relaxed mb-5">زحمة، كهربا، مياه، حوادث — موثّقة من سكان حيك مش إشاعات.</p>

                    <div class="space-y-2">
                        <div class="bg-ink-900 rounded-2xl p-3 border border-white/5 flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full pill-blush grid place-items-center shrink-0">
                                <x-icon name="traffic" class="w-4 h-4"/>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[11px] text-white/50">زحمة · كوبري بنها</div>
                                <div class="text-[13px] font-bold">+25 دقيقة تأخير</div>
                            </div>
                            <span class="text-[10px] text-white/40">٥ د</span>
                        </div>
                        <div class="bg-ink-900 rounded-2xl p-3 border border-white/5 flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full pill-mint grid place-items-center shrink-0">
                                <x-icon name="bolt" class="w-4 h-4"/>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[11px] text-white/50">كهربا · قسم تاني</div>
                                <div class="text-[13px] font-bold">رجعت دلوقتي</div>
                            </div>
                            <span class="text-[10px] text-white/40">١ د</span>
                        </div>
                        <div class="bg-ink-900 rounded-2xl p-3 border border-white/5 flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full pill-coral grid place-items-center shrink-0">
                                <x-icon name="map-pin" class="w-4 h-4"/>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[11px] text-white/50">حادثة · طريق شبين</div>
                                <div class="text-[13px] font-bold">مسار يمين متقفل</div>
                            </div>
                            <span class="text-[10px] text-white/40">١٢ د</span>
                        </div>
                    </div>
                </div>

                {{-- 3: Prices --}}
                <div class="rounded-3xl p-6 bg-white/[.04] border border-white/10 backdrop-blur hover:bg-white/[.06] transition reveal">
                    <div class="flex items-center justify-between mb-5">
                        <span class="icon-tile bg-mint-500/15 border-mint-500/30 text-mint-100">
                            <x-icon name="tag" class="w-5 h-5"/>
                        </span>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-honey-400/15 text-honey-400 border border-honey-400/30">يومي</span>
                    </div>
                    <h3 class="text-xl font-extrabold mb-2">رادار الأسعار</h3>
                    <p class="text-white/60 text-sm leading-relaxed mb-5">اعرف سعر كل حاجة في كل سوق في بنها — قبل ما تنزل تشتري.</p>

                    <div class="space-y-2">
                        @php
                            $prices = [
                                ['🍅', 'طماطم',  '12 ج/ك', '-2 ج', 'mint'],
                                ['🥔', 'بطاطس',  '14 ج/ك', '+1 ج', 'blush'],
                                ['🐔', 'فراخ',   '95 ج/ك', '-5 ج', 'mint'],
                                ['⛽', 'بنزين 92','15.50ج', 'ثابت', 'ink'],
                            ];
                        @endphp
                        <div class="bg-ink-900 rounded-2xl border border-white/5 divide-y divide-white/5">
                            @foreach($prices as [$em, $name, $val, $delta, $tone])
                                <div class="flex items-center gap-3 px-3 py-2.5">
                                    <span class="text-base">{{ $em }}</span>
                                    <span class="flex-1 text-[12px] font-bold">{{ $name }}</span>
                                    <span class="text-[12px] font-bold">{{ $val }}</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full
                                        {{ $tone === 'mint' ? 'pill-mint' : ($tone === 'blush' ? 'pill-blush' : 'bg-white/10 text-white/60') }}">
                                        {{ $delta }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── ZONES SHOWCASE ──────────────────────────────────── --}}
    <section id="zones" class="relative py-24 md:py-32">
        <div class="mx-auto max-w-6xl px-5">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="reveal">
                    <span class="text-coral font-bold text-sm tracking-wider">جغرافيا حقيقية</span>
                    <h2 class="mt-3 text-3xl md:text-5xl font-black leading-tight text-ink-950">
                        مغطّيين كل
                        <span class="text-coral">القليوبية</span>
                    </h2>
                    <p class="mt-4 text-ink-500 text-lg max-w-lg">
                        من قسم أول بنها لحد كفر شكر، فيد كل منطقة بمحتواها — مش طوفان من الكل لكلٍ.
                    </p>

                    <div class="mt-8 grid grid-cols-2 gap-3 max-w-md">
                        @php
                            $zonesShowcase = [
                                ['بنها',         '247 بوست', true],
                                ['شبرا الخيمة',  '189 بوست', true],
                                ['طوخ',          '94 بوست',  false],
                                ['القليوب',      '67 بوست',  false],
                                ['قها',          '42 بوست',  false],
                                ['الخانكة',      '38 بوست',  false],
                            ];
                        @endphp
                        @foreach($zonesShowcase as [$name, $count, $live])
                            <div class="card-light p-3 flex items-center gap-2.5">
                                <span class="w-9 h-9 rounded-xl grid place-items-center text-white font-black text-sm shrink-0"
                                      style="background: {{ \App\Support\AnonSeed::avatarColor($name) }}">
                                    {{ \App\Support\AnonSeed::initial($name) }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-extrabold text-ink-950 truncate">{{ $name }}</div>
                                    <div class="text-[10px] text-ink-500">{{ $count }}</div>
                                </div>
                                @if($live)
                                    <span class="w-2 h-2 rounded-full bg-mint-500 animate-pulse"></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Live zones dashboard mockup --}}
                <div class="relative reveal">
                    <div class="card-orange p-6 md:p-8 relative overflow-hidden">
                        <div class="absolute -top-16 -end-16 w-64 h-64 rounded-full bg-white/15 blur-3xl"></div>
                        <div class="absolute -bottom-16 -start-16 w-64 h-64 rounded-full bg-honey-400/40 blur-3xl"></div>

                        {{-- Header --}}
                        <div class="relative flex items-center justify-between mb-6">
                            <div class="text-white/85 text-xs font-bold inline-flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                                مباشر · القليوبية
                            </div>
                            <div class="text-[10px] font-bold text-white/95 px-2.5 py-1 rounded-full bg-ink-950/30 border border-white/15">
                                NOW
                            </div>
                        </div>

                        {{-- Stacked zone cards (offset for depth) --}}
                        <div class="relative space-y-2.5 mb-6">
                            <div class="float-1">
                                <div class="bg-white rounded-2xl p-3 shadow-2xl flex items-center gap-2.5 -mx-2">
                                    <span class="w-10 h-10 rounded-xl grid place-items-center text-white font-black shrink-0"
                                          style="background: {{ \App\Support\AnonSeed::avatarColor('بنها') }}">ب</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-extrabold text-ink-950">بنها</div>
                                        <div class="text-[11px] text-ink-500">247 بوست النهاردة</div>
                                    </div>
                                    <span class="pill-mint text-[10px] font-bold px-2 py-0.5 rounded-full inline-flex items-center gap-1 shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-mint-500 animate-pulse"></span> live
                                    </span>
                                </div>
                            </div>

                            <div class="float-2">
                                <div class="bg-white rounded-2xl p-3 shadow-2xl flex items-center gap-2.5 mx-2">
                                    <span class="w-10 h-10 rounded-xl grid place-items-center text-white font-black shrink-0"
                                          style="background: {{ \App\Support\AnonSeed::avatarColor('شبرا الخيمة') }}">ش</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-extrabold text-ink-950">شبرا الخيمة</div>
                                        <div class="text-[11px] text-ink-500">189 بوست النهاردة</div>
                                    </div>
                                    <span class="pill-mint text-[10px] font-bold px-2 py-0.5 rounded-full inline-flex items-center gap-1 shrink-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-mint-500 animate-pulse"></span> live
                                    </span>
                                </div>
                            </div>

                            <div class="float-3">
                                <div class="bg-white rounded-2xl p-3 shadow-2xl flex items-center gap-2.5 -mx-1">
                                    <span class="w-10 h-10 rounded-xl grid place-items-center text-white font-black shrink-0"
                                          style="background: {{ \App\Support\AnonSeed::avatarColor('طوخ') }}">ط</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-extrabold text-ink-950">طوخ</div>
                                        <div class="text-[11px] text-ink-500">94 بوست النهاردة</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Big counters --}}
                        <div class="relative pt-5 border-t border-white/20 flex items-end justify-between gap-4">
                            <div class="text-white">
                                <div class="text-5xl md:text-6xl font-black leading-none">9</div>
                                <div class="text-white/85 text-sm mt-1.5">منطقة شغّالة</div>
                            </div>
                            <div class="text-end text-white">
                                <div class="text-3xl md:text-4xl font-black leading-none">+1.2k</div>
                                <div class="text-white/85 text-xs mt-1">بوست النهاردة</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── VOICES (testimonials) ───────────────────────────── --}}
    <section id="voices" class="py-24 md:py-32 bg-cream-50">
        <div class="mx-auto max-w-6xl px-5">
            <div class="text-center mb-14 reveal">
                <span class="text-coral font-bold text-sm tracking-wider">أصوات بنهاوية</span>
                <h2 class="mt-3 text-3xl md:text-5xl font-black text-ink-950">الناس بتقول إيه؟</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                @php
                    $voices = [
                        ['أحمد · طالب هندسة',     'بقيت أعرف أسعار البقالة قبل ما أنزل من البيت. توفير وقت ولا أحلى.', 'A', '#2D5BFF'],
                        ['نورا · صيدلانية',       'الكونفيشن وول حاجة جنان. نص مدينة بنها بتحكي قصصها هناك.',           'N', '#1FA857'],
                        ['محمد · سواق توك توك',   'تنبيهات الزحمة وفرت لي ٤٠ دقيقة في يومي. عمري ما هرجع زي الأول.',   'M', '#FFD440'],
                    ];
                @endphp
                @foreach($voices as [$who, $quote, $init, $color])
                    <div class="card-light p-6 reveal hover:-translate-y-1 hover:shadow-lg transition">
                        <div class="flex gap-1 mb-3 text-coral-500">
                            @for($i=0;$i<5;$i++)
                                <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9z"/></svg>
                            @endfor
                        </div>
                        <p class="text-ink-950 text-base leading-relaxed mb-5">"{{ $quote }}"</p>
                        <div class="flex items-center gap-3 pt-4 border-t border-ink-950/5">
                            <span class="w-10 h-10 rounded-full grid place-items-center text-white font-black"
                                  style="background: {{ $color }}">{{ $init }}</span>
                            <div class="text-sm font-bold text-ink-950">{{ $who }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── CTA ─────────────────────────────────────────────── --}}
    <section class="py-20 md:py-28">
        <div class="mx-auto max-w-5xl px-5">
            <div class="card-orange p-10 md:p-16 relative overflow-hidden text-center">
                <div class="absolute -top-32 -end-32 w-96 h-96 rounded-full bg-white/15 blur-3xl"></div>
                <div class="absolute -bottom-32 -start-32 w-96 h-96 rounded-full bg-honey-400/40 blur-3xl"></div>

                <div class="relative">
                    <h2 class="text-3xl md:text-6xl font-black text-white leading-tight">
                        جاهز تنضم لـ بنهاوي؟
                    </h2>
                    <p class="mt-4 text-white/90 text-lg md:text-xl max-w-xl mx-auto">
                        ٣٠ ثانية، رقم موبايلك، وكود ٤ أرقام. مفيش إيميل، مفيش SMS.
                    </p>

                    <div class="mt-9 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3 max-w-md mx-auto">
                        <a href="{{ route('signup') }}" class="btn-dark !py-3.5 !px-7 text-base flex-1 justify-center shadow-lg">
                            ابدأ النهاردة
                            <x-icon name="arrow-left" class="w-4 h-4"/>
                        </a>
                        <a href="{{ route('login') }}"
                           class="text-white font-bold px-5 py-3 text-sm rounded-full bg-white/15 hover:bg-white/25 backdrop-blur transition border border-white/20">
                            عندي حساب
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-sm text-white/95 font-bold">
                        @foreach([['check','مجاني تماماً'], ['mask','خصوصية أولاً'], ['flame','شغّال دلوقتي']] as [$ic, $txt])
                            <span class="inline-flex items-center gap-2">
                                <span class="w-7 h-7 rounded-full bg-white/15 grid place-items-center border border-white/20">
                                    <x-icon :name="$ic" class="w-3.5 h-3.5"/>
                                </span>
                                {{ $txt }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── FOOTER ──────────────────────────────────────────── --}}
    <footer class="py-12 border-t border-ink-950/8 bg-white/40">
        <div class="mx-auto max-w-6xl px-5">
            <div class="grid md:grid-cols-3 gap-8 items-center">
                <div class="flex items-center gap-2 font-extrabold text-lg text-ink-950">
                    <span class="w-9 h-9 rounded-xl brand-bg grid place-items-center">
                        <span class="text-white font-black">ب</span>
                    </span>
                    <span>بنهاوي</span>
                </div>
                <p class="text-ink-500 text-sm text-center inline-flex items-center justify-center gap-2">
                  
                </p>
                <div class="flex items-center justify-center md:justify-end gap-2">
                    @php
                        $socials = [
                            ['twitter', 'تويتر / X'],
                            ['facebook', 'فيسبوك'],
                            ['instagram', 'إنستجرام'],
                            ['youtube', 'يوتيوب'],
                            ['whatsapp', 'واتساب'],
                        ];
                    @endphp
                    @foreach($socials as [$ic, $label])
                        <a href="#" aria-label="{{ $label }}"
                           class="w-9 h-9 rounded-full bg-white grid place-items-center hover:bg-cream-200 hover:text-coral-600 transition border border-ink-950/8 text-ink-500">
                            <x-icon :name="$ic" class="w-4 h-4"/>
                        </a>
                    @endforeach
                </div>
            </div>
            <p class="mt-8 text-center text-ink-400 text-xs">
                © {{ date('Y') }}
                <span class="font-bold text-ink-500">Elashhab</span>
                · جميع الحقوق محفوظة
            </p>
        </div>
    </footer>
</body>
</html>
