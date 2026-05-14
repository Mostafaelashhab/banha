@extends('layouts.app', [
    'title' => 'امتلك صفحة نشاطك على بنهاوي · بنها والقليوبية',
    'description' => 'صاحب محل أو مطعم أو عيادة في بنها؟ امتلك صفحتك المجانية على بنهاوي، استقبل عملاء من واتساب والاتصال، انشر عروض وصور، وتابع المشاهدات.',
    'keywords' => 'امتلك نشاطك, إعلان بنها, تسويق بنها, دليل أعمال بنها, صفحة محل بنها, نشاط مجاني بنها',
])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ─── Headline ─── --}}
    <div class="mb-6 rise rise-1">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-coral-50 text-coral-600 text-[11px] font-extrabold mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-coral-500"></span>
            لأصحاب النشاطات في بنها والقليوبية
        </div>
        <h1 class="text-3xl md:text-4xl font-black text-ink-950 leading-tight mb-3">
            امتلك صفحة نشاطك<br>على بنهاوي
        </h1>
        <p class="text-sm md:text-base text-ink-500 leading-relaxed max-w-xl">
            أهل بنها بيدوّروا كل يوم على مطاعم، عيادات، محلات، وصنايعية على بنهاوي.
            خلّيهم يلاقوا نشاطك أول.
        </p>
    </div>

    {{-- ─── Trust strip ─── --}}
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
            <div class="text-[10px] font-bold text-ink-500 mt-0.5">منطقة مغطّاة</div>
        </div>
    </div>

    {{-- ─── What you get (free) ─── --}}
    <section class="mb-8 rise rise-2">
        <h2 class="text-xl font-black text-ink-950 mb-3">إيه اللي هتعمله؟</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach([
                ['✏️', 'عدّل الاسم والعنوان والمواعيد'],
                ['📷', 'ضيف صور للمحل والمنتجات'],
                ['📜', 'اعمل منيو رقمي للأكل والأسعار'],
                ['💬', 'استقبل عملاء على واتساب'],
                ['📞', 'استقبل اتصالات بضغطة زر'],
                ['🎯', 'انشر عروض بنها النهارده'],
                ['📊', 'تابع مشاهدات صفحتك وضغطات الاتصال'],
                ['🛒', 'استقبل طلبات أوردر مباشرة (للمطاعم)'],
            ] as [$em, $txt])
                <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 flex items-center gap-3">
                    <span class="text-xl">{{ $em }}</span>
                    <span class="text-sm font-bold text-ink-950">{{ $txt }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ─── Pricing tiers ─── --}}
    <section class="mb-8 rise rise-3">
        <h2 class="text-xl font-black text-ink-950 mb-1">خطط متاحة</h2>
        <p class="text-[12px] text-ink-500 mb-4">ابدأ مجاناً، وارفع باقتك لما تحتاج أدوات أقوى.</p>

        <div class="space-y-3">
            @php
                $plans = [
                    [
                        'name' => 'مجاني',
                        'price' => '٠ ج',
                        'sub' => 'للأبد',
                        'badge' => null,
                        'ring' => 'ring-ink-950/8',
                        'features' => ['اسم النشاط والعنوان والتليفون', 'ظهور في الدليل والبحث', 'مشاركة صفحتك على واتساب'],
                        'cta' => 'ابدأ مجاناً',
                    ],
                    [
                        'name' => 'موثّق',
                        'price' => '٢٩٩ ج',
                        'sub' => 'في الشهر',
                        'badge' => 'الأكثر طلباً',
                        'ring' => 'ring-coral-500',
                        'features' => ['كل مزايا الباقة المجانية', 'علامة توثيق ✓ زرقاء', 'صور للمحل والمنتجات', 'زر واتساب + جدول المواعيد', 'تعديل البيانات في أي وقت'],
                        'cta' => 'فعّل الباقة',
                    ],
                    [
                        'name' => 'مميّز',
                        'price' => '٦٩٩ ج',
                        'sub' => 'في الشهر',
                        'badge' => null,
                        'ring' => 'ring-ink-950/8',
                        'features' => ['كل مزايا الباقة الموثّقة', 'ظهور أعلى في نتايج البحث', 'نشر عروض بنها النهارده', 'إحصائيات تفصيلية', 'شارة "مميّز" على الكارد'],
                        'cta' => 'فعّل الباقة',
                    ],
                    [
                        'name' => 'برو',
                        'price' => '٩٩٩ ج',
                        'sub' => 'في الشهر',
                        'badge' => null,
                        'ring' => 'ring-ink-950/8',
                        'features' => ['كل مزايا باقة مميّز', 'بنر إعلاني على الصفحة الرئيسية', 'كوبونات وحملات تسويقية', 'إحصائيات Advanced + تصدير', 'دعم أولوية على واتساب'],
                        'cta' => 'كلّمنا للتفعيل',
                    ],
                ];
            @endphp

            @foreach($plans as $p)
                <div class="bg-white rounded-3xl ring-1 {{ $p['ring'] }} p-5 relative">
                    @if($p['badge'])
                        <span class="absolute -top-2 start-5 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-coral-500 text-white text-[10px] font-extrabold">
                            {{ $p['badge'] }}
                        </span>
                    @endif
                    <div class="flex items-baseline gap-2 mb-1">
                        <h3 class="text-lg font-black text-ink-950">{{ $p['name'] }}</h3>
                        <span class="text-xs font-bold text-ink-500">·</span>
                        <span class="text-2xl font-black text-coral-600" dir="ltr">{{ $p['price'] }}</span>
                        <span class="text-[11px] font-bold text-ink-500">{{ $p['sub'] }}</span>
                    </div>
                    <ul class="mt-3 space-y-1.5">
                        @foreach($p['features'] as $f)
                            <li class="flex items-start gap-2 text-[13px] text-ink-950">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 mt-0.5 text-mint-600 shrink-0">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span>{{ $f }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ Auth::check() ? route('directory.create') : route('signup') }}"
                       class="mt-4 w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-full bg-coral-500 text-white text-sm font-extrabold hover:bg-coral-600 transition">
                        {{ $p['cta'] }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4 rtl:rotate-180">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ─── Already on Banhawy? Claim CTA ─── --}}
    <section class="mb-8 rise rise-3">
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

    {{-- ─── Final CTA ─── --}}
    <section class="rise rise-4 mb-6">
        <div class="rounded-3xl p-6 relative overflow-hidden text-center" style="background: #1F46DB;">
            <div class="absolute -top-16 -end-16 w-56 h-56 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-16 -start-16 w-56 h-56 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative">
                <h2 class="text-xl font-black text-white mb-2">جاهز تكبّر نشاطك؟</h2>
                <p class="text-[13px] text-white/85 mb-4 max-w-sm mx-auto">
                    دقيقتين بس وصفحتك شغّالة. أهل بنها هيلاقوك أسرع.
                </p>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 max-w-sm mx-auto">
                    <a href="{{ Auth::check() ? route('directory.create') : route('signup') }}"
                       class="flex-1 inline-flex items-center justify-center gap-1.5 px-5 py-3 rounded-full bg-white text-coral-600 text-sm font-extrabold hover:bg-cream-100 transition">
                        فعّل نشاطك الآن
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
            </div>
        </div>
    </section>

</div>
@endsection
