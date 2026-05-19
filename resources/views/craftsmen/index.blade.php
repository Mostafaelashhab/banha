@extends('layouts.app', [
    'title'       => 'صنايعية بنها والقليوبية · سباك، كهربائي، تكييف، نقاش — بنها.shop',
    'description' => 'أكبر دليل صنايعية في بنها والقليوبية. سباك، كهربائي، فني تكييف، نقاش، نجار وغيرهم — مع تقييمات وأسعار وأرقام مباشرة.',
    'canonical'   => route('craftsmen.index'),
    'keywords'    => 'صنايعية بنها, سباك بنها, كهربائي بنها, فني تكييف بنها, نقاش بنها, نجار بنها, تشطيبات بنها, صنايعي القليوبية',
])

@push('json-ld')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'ItemList',
    'name'     => 'صنايعية بنها',
    'numberOfItems' => $totalCraftsmen,
    'itemListElement' => $trades->map(fn ($t, $i) => [
        '@type'    => 'ListItem',
        'position' => $i + 1,
        'name'     => $t['label'] . ' في بنها',
        'url'      => route('craftsmen.trade', $t['key']),
    ])->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="max-w-2xl mx-auto" data-no-edge-swipe>

    {{-- ───── HERO ───── --}}
    <div class="relative -mx-4 mb-4 overflow-hidden rounded-b-3xl"
         style="background: linear-gradient(135deg, #FFB85C 0%, #FF9933 60%, #FF7A33 100%);">
        <svg class="absolute inset-0 w-full h-full opacity-15" preserveAspectRatio="none" aria-hidden="true">
            <defs>
                <pattern id="craft-grid" x="0" y="0" width="34" height="34" patternUnits="userSpaceOnUse">
                    <path d="M 34 0 L 0 0 0 34" fill="none" stroke="white" stroke-width="1.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#craft-grid)"/>
        </svg>

        <div class="relative px-5 py-8 text-white">
            <div class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm rounded-full px-3 py-1 text-[10px] font-extrabold mb-3">
                <span>🛠️</span> صنايعية بنها والقليوبية
            </div>
            <h1 class="text-2xl md:text-3xl font-black leading-tight">
                محتاج صنايعي؟<br/>
                كلّمه في خمس دقايق.
            </h1>
            <p class="text-white/90 text-sm mt-2 leading-relaxed">
                {{ number_format($totalCraftsmen) }} صنايعي في بنها والقليوبية ·
                @if($verifiedCount > 0) {{ $verifiedCount }} موثّق · @endif
                ٢٤ ساعة طلبات طارئة
            </p>

            <div class="grid grid-cols-2 gap-2 mt-5">
                <a href="{{ route('craft-jobs.create') }}" class="block py-3.5 rounded-full bg-white text-coral-700 text-center font-extrabold text-sm shadow-lg hover:scale-[1.02] transition">
                    📋 اطلب صنايعي دلوقتي
                </a>
                <a href="{{ route('craftsmen.signup') }}" class="block py-3.5 rounded-full bg-ink-950 text-white text-center font-extrabold text-sm shadow-lg hover:scale-[1.02] transition">
                    🛠️ أنا صنايعي - سجّل
                </a>
            </div>
        </div>
    </div>

    {{-- ───── TRADES GRID ───── --}}
    <h2 class="text-lg font-black text-ink-950 mb-3 px-1">اختار التخصص</h2>
    <div class="grid grid-cols-3 gap-2 mb-6">
        @foreach($trades as $t)
            <a href="{{ route('craftsmen.trade', $t['key']) }}"
               class="card-light p-3 text-center hover:bg-cream-100 transition group">
                <div class="text-2xl mb-1">{{ $t['emoji'] }}</div>
                <div class="text-xs font-extrabold text-ink-950 leading-tight">{{ $t['label'] }}</div>
                @if($t['count'] > 0)
                    <div class="text-[10px] text-coral-600 font-bold mt-1">{{ $t['count'] }} موجود</div>
                @else
                    <div class="text-[10px] text-ink-400 mt-1">قريباً</div>
                @endif
            </a>
        @endforeach
    </div>

    {{-- ───── OPEN JOBS — proves the marketplace is alive ───── --}}
    @if($openJobs->isNotEmpty())
        <div class="card-light p-4 mb-5 bg-mint-50 ring-1 ring-mint-500/30">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-2">
                    <span class="relative flex w-2 h-2">
                        <span class="absolute inline-flex w-full h-full rounded-full bg-mint-500 opacity-75 animate-ping"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full bg-mint-500"></span>
                    </span>
                    طلبات شغل لحظية
                </h3>
                <a href="{{ route('craft-jobs.index') }}" class="text-xs font-bold text-coral-600 hover:underline">شوف الكل ←</a>
            </div>
            <div class="space-y-2">
                @foreach($openJobs as $job)
                    @php $tm = $job->tradeMeta(); @endphp
                    <a href="{{ route('craft-jobs.show', $job) }}" class="block bg-white rounded-xl p-3 hover:bg-cream-50 transition">
                        <div class="flex items-start gap-2">
                            <span class="text-xl shrink-0">{{ $tm['emoji'] ?? '🔧' }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-extrabold text-ink-950">{{ $tm['label'] ?? $job->sub_type }}</span>
                                    <span class="text-[10px] text-ink-500">· {{ $job->zone->name ?? 'بنها' }}</span>
                                    <span class="text-[10px] font-bold text-coral-600">· {{ $job->urgencyLabel() }}</span>
                                </div>
                                <p class="text-xs text-ink-600 mt-1 line-clamp-2">{{ $job->description }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ───── FEATURED CRAFTSMEN ───── --}}
    @if($featured->isNotEmpty())
        <h3 class="text-sm font-extrabold text-ink-950 mb-3 inline-flex items-center gap-2">
            <span>⭐</span> أكتر صنايعية تقييماً
        </h3>
        <div class="space-y-2 mb-5">
            @foreach($featured as $b)
                @include('craftsmen.partials.craftsman-row', ['craftsman' => $b])
            @endforeach
        </div>
    @endif

    {{-- ───── EMERGENCY 24H ───── --}}
    @if($emergency->isNotEmpty())
        <h3 class="text-sm font-extrabold text-ink-950 mb-3 inline-flex items-center gap-2">
            <span>⚡</span> متاحين ٢٤ ساعة + طلبات طارئة
        </h3>
        <div class="space-y-2 mb-5">
            @foreach($emergency as $b)
                @include('craftsmen.partials.craftsman-row', ['craftsman' => $b])
            @endforeach
        </div>
    @endif

    {{-- ───── WHY BANHA.SHOP ───── --}}
    <div class="card-light p-4 bg-cream-50 mb-5">
        <h3 class="text-sm font-extrabold text-ink-950 mb-3">ليه بنها.shop للصنايعية؟</h3>
        <ul class="space-y-3 text-sm">
            <li class="flex items-start gap-2">
                <span class="w-6 h-6 rounded-full bg-mint-100 text-mint-700 grid place-items-center text-xs shrink-0 mt-0.5">✓</span>
                <div>
                    <strong class="text-ink-950">مجاناً تماماً</strong>
                    <p class="text-[11px] text-ink-500">مفيش عمولة على الشغلانات. هتفضل دايماً مجاناً.</p>
                </div>
            </li>
            <li class="flex items-start gap-2">
                <span class="w-6 h-6 rounded-full bg-mint-100 text-mint-700 grid place-items-center text-xs shrink-0 mt-0.5">✓</span>
                <div>
                    <strong class="text-ink-950">تواصل مباشر بالواتساب</strong>
                    <p class="text-[11px] text-ink-500">العميل يكلّمك على رقمك بدون وسيط.</p>
                </div>
            </li>
            <li class="flex items-start gap-2">
                <span class="w-6 h-6 rounded-full bg-mint-100 text-mint-700 grid place-items-center text-xs shrink-0 mt-0.5">✓</span>
                <div>
                    <strong class="text-ink-950">إشعارات بالشغلانات الجديدة</strong>
                    <p class="text-[11px] text-ink-500">أي طلب في تخصصك ومنطقتك بييجيلك notification.</p>
                </div>
            </li>
            <li class="flex items-start gap-2">
                <span class="w-6 h-6 rounded-full bg-honey-100 text-honey-700 grid place-items-center text-xs shrink-0 mt-0.5">★</span>
                <div>
                    <strong class="text-ink-950">شارة "موثّق" مدفوعة (اختياري)</strong>
                    <p class="text-[11px] text-ink-500">شارة موثّق رسمية + الظهور أول في النتايج. ادفع مرة، تظهر كنشاط موثّق.</p>
                </div>
            </li>
        </ul>
    </div>

    {{-- ───── BIG CTA ───── --}}
    <div class="card-light p-5 mb-6 text-center bg-gradient-to-br from-coral-500 to-coral-600 text-white">
        <h3 class="text-lg font-black mb-2">عندك صنايعة وعاوز تجيب شغل؟</h3>
        <p class="text-sm text-white/85 mb-4 leading-relaxed">
            سجّل نشاطك مجاناً، هتلاقي شغل في تخصصك ومنطقتك يوصلك تلقائياً.
        </p>
        <a href="{{ route('craftsmen.signup') }}"
           class="inline-flex items-center gap-2 py-3 px-6 rounded-full bg-white text-coral-700 font-extrabold text-sm hover:scale-[1.02] transition">
            ابدأ التسجيل مجاناً
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </a>
    </div>
</div>
@endsection
