@extends('layouts.app', [
    'title' => 'امتلك صفحة نشاطك على بنهاوي · مجاناً · بنها',
    'description' => 'صاحب محل أو مطعم أو عيادة في بنها؟ امتلك صفحتك المجانية على بنهاوي، استقبل الأوردرات مباشرة على لوحة تحكمك، شوف العملاء وتابع الإحصائيات.',
    'keywords' => 'امتلك نشاطك مجاناً, لوحة تحكم النشاط, إعلان بنها, دليل أعمال بنها, صفحة محل بنها',
])

@push('head')
<style>
/* ─── Local landing-only utilities ───────────────────────────── */
.lp-hero {
    background:
        radial-gradient(ellipse at top right, rgba(31, 70, 219, .06) 0%, transparent 55%),
        radial-gradient(ellipse at bottom left, rgba(255, 122, 77, .04) 0%, transparent 55%),
        #fff;
}
.lp-mockup {
    /* phone frame */
    border: 1px solid rgba(11, 11, 12, .08);
    border-radius: 22px;
    background: #f4f5f8;
    box-shadow:
        0 1px 2px rgba(11, 11, 12, .04),
        0 12px 28px -8px rgba(11, 11, 12, .12),
        0 22px 60px -20px rgba(31, 70, 219, .25);
    overflow: hidden;
}
.lp-mockup-screen { background: #fff; }
.lp-mockup-pill {
    background: #1F46DB;
    color: #fff;
}
.lp-badge-new {
    position: absolute;
    top: -6px; inset-inline-end: -6px;
    background: #DC4A1F;
    color: #fff;
    font-size: 9px;
    font-weight: 900;
    padding: 2px 6px;
    border-radius: 999px;
    box-shadow: 0 0 0 3px #fff;
}
@keyframes lp-pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .5; transform: scale(1.4); }
}
.lp-live-dot { animation: lp-pulse-dot 1.5s ease-in-out infinite; }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- ══════════════════════════════════════════════════════════
         HERO — split: copy + dashboard mockup
         ══════════════════════════════════════════════════════════ --}}
    <section class="lp-hero rounded-3xl ring-1 ring-ink-950/8 p-5 md:p-8 mb-8 rise rise-1">
        <div class="grid lg:grid-cols-2 gap-6 lg:gap-10 items-center">

            {{-- ── Copy ── --}}
            <div class="text-center lg:text-start">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-coral-50 text-coral-600 text-[11px] font-extrabold mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-coral-500 lp-live-dot"></span>
                    لأصحاب النشاطات في بنها
                </div>

                <h1 class="text-3xl md:text-5xl font-black text-ink-950 leading-[1.05] mb-4">
                    استقبل أوردراتك<br>
                    على
                    <span class="inline-flex items-center gap-1.5 align-middle">
                        <span class="w-8 h-8 md:w-10 md:h-10 rounded-xl brand-bg grid place-items-center shadow-lg">
                            <span class="text-white font-black text-base md:text-lg">ب</span>
                        </span>
                        <span class="text-coral-600">بنهاوي</span>
                    </span>
                    <br>مباشرة.
                </h1>

                <p class="text-sm md:text-base text-ink-500 leading-relaxed mb-5 max-w-xl mx-auto lg:mx-0">
                    لوحة تحكم نضيفة على بنهاوي — كل أوردر يوصلك فيها لحظياً، تعرف عميلك ومنطقته،
                    وتأكد بضغطة. الواتساب اختياري كباك-أب.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-2 mb-4">
                    <a href="{{ Auth::check() ? route('directory.create') : route('signup') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-6 py-3.5 rounded-full bg-coral-500 text-white text-sm font-extrabold hover:bg-coral-600 transition shadow-lg shadow-coral-500/25">
                        ابدأ مجاناً
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4 rtl:rotate-180">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                    <a href="#how-it-works"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-3 rounded-full bg-white ring-1 ring-ink-950/8 text-ink-950 text-sm font-extrabold hover:bg-cream-100 transition">
                        شوف ازاي بيشتغل
                    </a>
                </div>

                <div class="inline-flex items-center gap-2 text-[11px] font-extrabold text-mint-700">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>مجاني بالكامل — مفيش اشتراك ولا عمولة</span>
                </div>
            </div>

            {{-- ── Mockup: orders dashboard inside a phone frame ── --}}
            <div class="relative max-w-[320px] mx-auto lg:mx-0 lg:justify-self-end">
                <div class="lp-mockup">
                    <div class="lp-mockup-screen">

                        {{-- Branded header --}}
                        <div class="px-4 pt-3 pb-3 flex items-center gap-2 border-b border-ink-950/6">
                            <span class="w-7 h-7 rounded-lg brand-bg grid place-items-center">
                                <span class="text-white font-black text-xs">ب</span>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[11px] font-black text-ink-950">قصر الونش</div>
                                <div class="text-[9px] text-ink-500">لوحة الأوردرات · بنهاوي</div>
                            </div>
                            <span class="inline-flex items-center gap-1 text-[9px] font-extrabold text-mint-700 bg-mint-100 px-2 py-0.5 rounded-full">
                                <span class="w-1 h-1 rounded-full bg-mint-500 lp-live-dot"></span>
                                مباشر
                            </span>
                        </div>

                        {{-- Counters --}}
                        <div class="grid grid-cols-3 gap-1.5 px-3 py-2 bg-cream-50">
                            <div class="bg-white rounded-lg p-1.5 text-center ring-1 ring-ink-950/5">
                                <div class="text-base font-black text-coral-600">3</div>
                                <div class="text-[8px] font-bold text-ink-500">شغّالة</div>
                            </div>
                            <div class="bg-white rounded-lg p-1.5 text-center ring-1 ring-ink-950/5">
                                <div class="text-base font-black text-mint-700">12</div>
                                <div class="text-[8px] font-bold text-ink-500">تمّت</div>
                            </div>
                            <div class="bg-white rounded-lg p-1.5 text-center ring-1 ring-ink-950/5">
                                <div class="text-base font-black text-ink-950">٤٨٠ ج</div>
                                <div class="text-[8px] font-bold text-ink-500">النهارده</div>
                            </div>
                        </div>

                        {{-- Order rows --}}
                        <div class="p-3 space-y-2">
                            {{-- Row 1: brand-new (animated) --}}
                            <div class="relative bg-white rounded-xl ring-1 ring-coral-500/30 p-2.5 shadow-sm">
                                <span class="lp-badge-new">جديد</span>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-7 h-7 rounded-lg pill-coral grid place-items-center text-[9px] font-black">#42</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[10px] font-extrabold text-ink-950 truncate">محمد عبدالله</div>
                                        <div class="text-[8px] text-ink-500">الفلل · 250 ج</div>
                                    </div>
                                    <span class="text-[8px] font-extrabold text-honey-700 bg-honey-100 px-1.5 py-0.5 rounded-full">بانتظار</span>
                                </div>
                                <div class="flex gap-1">
                                    <button class="flex-1 lp-mockup-pill text-[9px] font-extrabold py-1 rounded-md">أكّد</button>
                                    <button class="flex-1 bg-ink-950 text-white text-[9px] font-extrabold py-1 rounded-md">اتصل</button>
                                </div>
                            </div>
                            {{-- Row 2 --}}
                            <div class="bg-white rounded-xl ring-1 ring-ink-950/8 p-2.5">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-7 h-7 rounded-lg bg-mint-100 text-mint-700 grid place-items-center text-[9px] font-black">#41</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[10px] font-extrabold text-ink-950 truncate">سارة أحمد</div>
                                        <div class="text-[8px] text-ink-500">المنشية · 180 ج</div>
                                    </div>
                                    <span class="text-[8px] font-extrabold text-coral-600 bg-coral-50 px-1.5 py-0.5 rounded-full">بيتجهّز</span>
                                </div>
                            </div>
                            {{-- Row 3 --}}
                            <div class="bg-white rounded-xl ring-1 ring-ink-950/8 p-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-mint-100 text-mint-700 grid place-items-center text-[9px] font-black">#40</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[10px] font-extrabold text-ink-950 truncate">أحمد فاروق</div>
                                        <div class="text-[8px] text-ink-500">وسط البلد · 50 ج</div>
                                    </div>
                                    <span class="text-[8px] font-extrabold text-mint-700 bg-mint-100 px-1.5 py-0.5 rounded-full">تم</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Floating WhatsApp-as-backup chip --}}
                <div class="absolute -bottom-3 -start-2 lg:start-auto lg:-end-3 bg-white rounded-xl ring-1 ring-ink-950/8 shadow-lg p-2 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-mint-100 text-mint-700 grid place-items-center">
                        <x-icon name="whatsapp" class="w-3.5 h-3.5"/>
                    </span>
                    <div>
                        <div class="text-[9px] text-ink-500">باك-أب</div>
                        <div class="text-[10px] font-extrabold text-ink-950">واتساب اختياري</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════
         TRUST STRIP
         ══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-3 gap-2 mb-8 rise rise-1">
        <div class="card-light p-3 text-center">
            <div class="text-2xl font-black text-coral-600">{{ number_format($stats['businesses']) }}+</div>
            <div class="text-[10px] font-bold text-ink-500 mt-0.5">نشاط مسجّل</div>
        </div>
        <div class="card-light p-3 text-center">
            <div class="text-2xl font-black text-mint-700">{{ number_format($stats['verified']) }}</div>
            <div class="text-[10px] font-bold text-ink-500 mt-0.5">نشاط موثّق</div>
        </div>
        <div class="card-light p-3 text-center">
            <div class="text-2xl font-black text-honey-700">{{ $stats['zones'] }}</div>
            <div class="text-[10px] font-bold text-ink-500 mt-0.5">منطقة في بنها</div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         WHERE ORDERS GO — the headline message
         ══════════════════════════════════════════════════════════ --}}
    <section class="mb-10 rise rise-2">
        <div class="grid md:grid-cols-2 gap-3">

            {{-- Primary: orders on the platform --}}
            <div class="rounded-3xl p-5 ring-1 ring-coral-500/20 bg-coral-50/40 relative overflow-hidden">
                <span class="absolute -top-2 start-5 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-coral-500 text-white text-[10px] font-extrabold">
                    القناة الأساسية
                </span>
                <div class="flex items-center gap-3 mb-3 mt-1">
                    <span class="w-12 h-12 rounded-2xl brand-bg grid place-items-center shadow-lg shadow-coral-500/30">
                        <span class="text-white font-black">ب</span>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-base font-black text-ink-950">على لوحة بنهاوي</div>
                        <div class="text-[11px] text-ink-500">في موقعك الإلكتروني · في أي وقت</div>
                    </div>
                </div>
                <ul class="space-y-1.5">
                    @foreach([
                        'كل أوردر يظهر فوراً في لوحتك',
                        'تأكيد / بيتجهّز / تم — بضغطة',
                        'بيانات العميل واضحة + المنطقة + الشحن',
                        'تاريخ كامل لكل أوردراتك',
                    ] as $p)
                        <li class="flex items-start gap-2 text-[12px] text-ink-950">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 mt-0.5 text-coral-600 shrink-0">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span class="font-bold">{{ $p }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Secondary: WhatsApp backup --}}
            <div class="rounded-3xl p-5 ring-1 ring-ink-950/8 bg-white">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-12 h-12 rounded-2xl bg-mint-100 text-mint-700 grid place-items-center">
                        <x-icon name="whatsapp" class="w-6 h-6"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-base font-black text-ink-950">واتساب — اختياري</div>
                        <div class="text-[11px] text-ink-500">يتبعتلك كباك-أب لو حابب</div>
                    </div>
                </div>
                <ul class="space-y-1.5">
                    @foreach([
                        'إشعار سريع لو لوحة بنهاوي مش مفتوحة',
                        'رسالة منسّقة فيها كل تفاصيل الأوردر',
                        'تقدر تقفله أو تفتحه أي وقت',
                        'مجاني — مش بياخد من اشتراك واتساب',
                    ] as $p)
                        <li class="flex items-start gap-2 text-[12px] text-ink-700">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 mt-0.5 text-mint-700 shrink-0">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <span class="font-bold">{{ $p }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════
         "ALL FREE" BANNER
         ══════════════════════════════════════════════════════════ --}}
    <div class="rounded-3xl p-5 mb-10 ring-1 ring-mint-500/30 bg-mint-50 rise rise-2">
        <div class="flex items-start gap-3">
            <span class="w-12 h-12 rounded-2xl bg-mint-500 text-white grid place-items-center shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                    <polyline points="20 12 20 22 4 22 4 12"/>
                    <rect x="2" y="7" width="20" height="5"/>
                    <line x1="12" y1="22" x2="12" y2="7"/>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <h2 class="text-base font-black text-ink-950 mb-1">كل المميزات مجاناً دلوقتي</h2>
                <p class="text-[12px] text-ink-700 leading-relaxed">
                    مفيش باقات ولا اشتراك شهري ولا عمولة على الأوردرات. كل صاحب نشاط في بنها بياخد كل المزايا كاملة.
                </p>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         FEATURES GRID (SVG only)
         ══════════════════════════════════════════════════════════ --}}
    <section class="mb-10 rise rise-2">
        <h2 class="text-xl font-black text-ink-950 mb-1">إيه اللي هتعمله من لوحتك؟</h2>
        <p class="text-[12px] text-ink-500 mb-4">٨ مزايا — كلها مجاناً، كلها من نفس اللوحة.</p>

        @php
            $features = [
                ['bg-coral-50', 'text-coral-600',
                    '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z"/>',
                    'عدّل الاسم والعنوان والمواعيد'],
                ['bg-honey-100', 'text-honey-700',
                    '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
                    'ضيف صور للمحل والمنتجات'],
                ['bg-mint-100', 'text-mint-700',
                    '<rect x="6" y="3" width="12" height="18" rx="2"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/>',
                    'اعمل منيو رقمي للأكل والأسعار'],
                ['bg-coral-50', 'text-coral-600',
                    '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>',
                    'استقبل أوردرات مباشرة في اللوحة'],
                ['bg-honey-100', 'text-honey-700',
                    '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
                    'استقبل اتصالات بضغطة زر'],
                ['bg-blush-100', 'text-blush-600',
                    '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
                    'انشر عروض بنها النهارده'],
                ['bg-honey-100', 'text-honey-700',
                    '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="3" y1="20" x2="21" y2="20"/>',
                    'تابع مشاهدات صفحتك وضغطات الاتصال'],
                ['bg-mint-100', 'text-mint-700',
                    '<rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
                    'حدّد أسعار الشحن لكل منطقة'],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach($features as [$bg, $fg, $path, $txt])
                <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 flex items-center gap-3 hover:ring-coral-500/30 transition">
                    <span class="w-10 h-10 rounded-xl {{ $bg }} {{ $fg }} grid place-items-center shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            {!! $path !!}
                        </svg>
                    </span>
                    <span class="text-sm font-bold text-ink-950 leading-snug">{{ $txt }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════
         HOW IT WORKS (with branded mini-mockups per step)
         ══════════════════════════════════════════════════════════ --}}
    <section id="how-it-works" class="mb-10 rise rise-3">
        <h2 class="text-xl font-black text-ink-950 mb-1">إزاي بيشتغل؟</h2>
        <p class="text-[12px] text-ink-500 mb-4">٣ خطوات بسيطة.</p>

        <div class="grid md:grid-cols-3 gap-3">

            {{-- Step 1: branded business profile mockup --}}
            <div class="bg-white rounded-3xl ring-1 ring-ink-950/8 p-4 relative">
                <span class="absolute -top-3 start-4 w-7 h-7 rounded-full bg-coral-500 text-white grid place-items-center text-xs font-black">١</span>
                <div class="lp-mockup mt-2 mb-3">
                    <div class="lp-mockup-screen p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-6 h-6 rounded-md brand-bg grid place-items-center">
                                <span class="text-white font-black text-[10px]">ب</span>
                            </span>
                            <div class="flex-1 text-[10px] font-extrabold text-ink-500">سجّل حسابك</div>
                        </div>
                        <div class="space-y-1.5">
                            <div class="bg-cream-100 rounded-md h-6 px-2 flex items-center text-[9px] text-ink-500">رقم موبايلك</div>
                            <div class="bg-cream-100 rounded-md h-6 px-2 flex items-center text-[9px] text-ink-500">اسم المستخدم</div>
                            <div class="bg-coral-500 text-white rounded-md h-6 grid place-items-center text-[9px] font-extrabold">ابدأ</div>
                        </div>
                    </div>
                </div>
                <div class="text-sm font-black text-ink-950 mb-0.5">سجّل حسابك</div>
                <div class="text-[11px] text-ink-500 leading-snug">دقيقة برقم موبايلك. مفيش إيميل ولا تعقيد.</div>
            </div>

            {{-- Step 2: business listed --}}
            <div class="bg-white rounded-3xl ring-1 ring-ink-950/8 p-4 relative">
                <span class="absolute -top-3 start-4 w-7 h-7 rounded-full bg-coral-500 text-white grid place-items-center text-xs font-black">٢</span>
                <div class="lp-mockup mt-2 mb-3">
                    <div class="lp-mockup-screen p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-7 h-7 rounded-lg bg-coral-100 grid place-items-center text-coral-600">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                                    <path d="M3 9h18l-1.5-5h-15z"/><path d="M4 9v11a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V9"/>
                                </svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] font-extrabold text-ink-950 truncate">مطعم نشاطك</div>
                                <div class="text-[8px] text-ink-500">مطعم · بنها</div>
                            </div>
                            <span class="text-[8px] font-extrabold text-mint-700 bg-mint-100 px-1.5 py-0.5 rounded-full">موثّق</span>
                        </div>
                        <div class="bg-cream-100 rounded-md h-12 grid place-items-center">
                            <span class="w-5 h-5 rounded brand-bg grid place-items-center">
                                <span class="text-white font-black text-[8px]">ب</span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-sm font-black text-ink-950 mb-0.5">ضيف نشاطك للدليل</div>
                <div class="text-[11px] text-ink-500 leading-snug">اسم، عنوان، صور، مواعيد، منيو. كل ده في صفحة واحدة.</div>
            </div>

            {{-- Step 3: orders flowing --}}
            <div class="bg-white rounded-3xl ring-1 ring-ink-950/8 p-4 relative">
                <span class="absolute -top-3 start-4 w-7 h-7 rounded-full bg-coral-500 text-white grid place-items-center text-xs font-black">٣</span>
                <div class="lp-mockup mt-2 mb-3">
                    <div class="lp-mockup-screen p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-6 h-6 rounded-md brand-bg grid place-items-center">
                                <span class="text-white font-black text-[10px]">ب</span>
                            </span>
                            <div class="flex-1 text-[10px] font-extrabold text-ink-950">الأوردرات</div>
                            <span class="inline-flex items-center gap-1 text-[8px] font-extrabold text-coral-600 bg-coral-50 px-1.5 py-0.5 rounded-full">
                                <span class="w-1 h-1 rounded-full bg-coral-500 lp-live-dot"></span>
                                ٣ جدد
                            </span>
                        </div>
                        <div class="space-y-1">
                            <div class="bg-cream-100 rounded-md h-5 px-2 flex items-center justify-between text-[8px]">
                                <span class="font-extrabold text-ink-950">#42 · محمد</span>
                                <span class="text-coral-600 font-extrabold">250 ج</span>
                            </div>
                            <div class="bg-cream-100 rounded-md h-5 px-2 flex items-center justify-between text-[8px]">
                                <span class="font-extrabold text-ink-950">#41 · سارة</span>
                                <span class="text-coral-600 font-extrabold">180 ج</span>
                            </div>
                            <div class="bg-cream-100 rounded-md h-5 px-2 flex items-center justify-between text-[8px]">
                                <span class="font-extrabold text-ink-950">#40 · أحمد</span>
                                <span class="text-coral-600 font-extrabold">50 ج</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-sm font-black text-ink-950 mb-0.5">استقبل الأوردرات</div>
                <div class="text-[11px] text-ink-500 leading-snug">كل أوردر يظهر فوراً في لوحتك. أكّد بضغطة وكلّم العميل.</div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════
         CLAIM-EXISTING SECTION
         ══════════════════════════════════════════════════════════ --}}
    <section class="mb-10 rise rise-3">
        <div class="rounded-3xl p-5 bg-cream-100 ring-1 ring-ink-950/8">
            <div class="flex items-start gap-3">
                <span class="w-12 h-12 rounded-2xl bg-coral-50 text-coral-600 grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <polyline points="9 12 11 14 15 10"/>
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-extrabold text-ink-950 mb-1">نشاطك موجود قبل ما تيجي؟</div>
                    <p class="text-[12px] text-ink-500 leading-relaxed">
                        لو لاقيت نشاطك في الدليل بالفعل، تقدر تطلب امتلاكه بكود واتساب على رقم النشاط — مجاناً.
                    </p>
                    <a href="{{ route('directory.index') }}"
                       class="inline-flex items-center gap-1.5 mt-2 text-sm font-extrabold text-coral-600 hover:underline">
                        دور على نشاطك في الدليل
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3.5 h-3.5 rtl:rotate-180">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════
         FAQ
         ══════════════════════════════════════════════════════════ --}}
    <section class="mb-10 rise rise-3">
        <h2 class="text-xl font-black text-ink-950 mb-3">أسئلة بنسمعها كتير</h2>
        <div class="space-y-2">
            @foreach([
                ['الأوردرات بتوصل ازاي؟', 'الأوردر بيوصل مباشرة على **لوحة بنهاوي** بتاعتك — تشوف العميل ومنطقته وتفاصيل الطلب. لو فعّلت الواتساب، بيتبعتلك نسخة هناك كمان كباك-أب.'],
                ['هتاخدوا عمولة على الأوردرات؟', 'لا. كل أوردر بيوصل لك مباشرة. الفلوس بينك وبين العميل، إحنا مش طرف.'],
                ['ممكن أعدّل بياناتي بعدين؟', 'طبعاً. كل حاجة قابلة للتعديل — الصور، المواعيد، المنيو، أسعار الشحن — في أي وقت.'],
                ['الإعلانات هتظهر على صفحتي؟', 'لأ. صفحة نشاطك نضيفة بالكامل — مفيش إعلانات لغيرك تظهر عليها.'],
            ] as [$q, $a])
                <details class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-4 group">
                    <summary class="text-sm font-extrabold text-ink-950 cursor-pointer list-none flex items-center gap-2">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-coral-600 transition group-open:rotate-90 rtl:rotate-180 rtl:group-open:rotate-90">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                        <span class="flex-1">{{ $q }}</span>
                    </summary>
                    <p class="text-[12px] text-ink-500 leading-relaxed mt-2 ps-6">{!! preg_replace('/\*\*(.+?)\*\*/u', '<b class="text-ink-950">$1</b>', $a) !!}</p>
                </details>
            @endforeach
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════
         FINAL CTA
         ══════════════════════════════════════════════════════════ --}}
    <section class="rise rise-4 mb-6">
        <div class="rounded-3xl p-6 relative overflow-hidden text-center" style="background: #1F46DB;">
            <div class="absolute -top-16 -end-16 w-56 h-56 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-16 -start-16 w-56 h-56 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 ring-1 ring-white/20 text-white text-[11px] font-extrabold mb-3">
                    <span class="w-6 h-6 rounded-md bg-white/20 grid place-items-center">
                        <span class="text-white font-black text-xs">ب</span>
                    </span>
                    بنهاوي
                </span>
                <h2 class="text-2xl md:text-3xl font-black text-white mb-2">جاهز تكبّر نشاطك؟</h2>
                <p class="text-[13px] text-white/85 mb-5 max-w-md mx-auto leading-relaxed">
                    دقيقتين بس وصفحتك شغّالة. الأوردرات هتبدأ توصلك على لوحتك في بنهاوي مباشرة.
                </p>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 max-w-sm mx-auto">
                    <a href="{{ Auth::check() ? route('directory.create') : route('signup') }}"
                       class="flex-1 inline-flex items-center justify-center gap-1.5 px-5 py-3 rounded-full bg-white text-coral-600 text-sm font-extrabold hover:bg-cream-100 transition shadow-lg">
                        فعّل نشاطك مجاناً
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4 rtl:rotate-180">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                    <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl(config('services.banhawy.support_whatsapp', '01000000000')) }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center justify-center gap-1.5 px-5 py-3 rounded-full bg-white/15 text-white text-sm font-extrabold hover:bg-white/25 transition backdrop-blur ring-1 ring-white/20">
                        <x-icon name="whatsapp" class="w-4 h-4"/>
                        كلمنا واتساب
                    </a>
                </div>
                <p class="text-[10px] text-white/70 mt-3">مفيش رسوم خفية · مفيش اشتراك · مفيش عمولة</p>
            </div>
        </div>
    </section>

</div>
@endsection
