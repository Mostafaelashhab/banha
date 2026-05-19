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
<div class="max-w-3xl mx-auto" data-no-edge-swipe>

    {{-- ───── HERO — brand-accent card (no big gradient) ───── --}}
    <x-card padding="lg" class="mb-4">
        <div class="flex items-start gap-3 mb-4">
            <x-icon-tile icon="tools" size="lg"/>
            <div class="flex-1 min-w-0">
                <span class="inline-block text-[10px] font-extrabold text-coral-600 mb-1">صنايعية بنها والقليوبية</span>
                <h1 class="text-xl md:text-2xl font-black leading-tight text-ink-950">
                    محتاج صنايعي؟ كلّمه في خمس دقايق.
                </h1>
                <p class="text-xs text-ink-500 mt-1.5 leading-relaxed">
                    {{ number_format($totalCraftsmen) }} صنايعي في بنها والقليوبية
                    @if($verifiedCount > 0) · {{ $verifiedCount }} موثّق @endif
                    · ٢٤ ساعة طلبات طارئة
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <x-button :href="route('craft-jobs.create')" icon="clipboard" size="lg" block>اطلب صنايعي</x-button>
            <x-button :href="route('craftsmen.signup')" variant="secondary" icon="tools" size="lg" block>أنا صنايعي · سجّل</x-button>
        </div>
    </x-card>

    {{-- ───── TRADES GRID ───── --}}
    <h2 class="text-base font-extrabold text-ink-950 mb-3 px-1">اختار التخصص</h2>
    <div class="grid grid-cols-3 gap-2 mb-6">
        @foreach($trades as $t)
            <x-card as="a" :href="route('craftsmen.trade', $t['key'])" padding="sm" class="text-center hover:bg-cream-100 transition">
                <x-icon-tile :icon="$t['icon'] ?? 'tools'" class="mx-auto mb-1.5"/>
                <div class="text-xs font-extrabold text-ink-950 leading-tight">{{ $t['label'] }}</div>
                @if($t['count'] > 0)
                    <div class="text-[10px] text-coral-600 font-bold mt-1">{{ $t['count'] }} موجود</div>
                @else
                    <div class="text-[10px] text-ink-400 mt-1">قريباً</div>
                @endif
            </x-card>
        @endforeach
    </div>

    {{-- ───── OPEN JOBS — proves the marketplace is alive ───── --}}
    @if($openJobs->isNotEmpty())
        <x-card class="mb-5 bg-mint-50 ring-1 ring-mint-500/30">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-2">
                    <span class="relative flex w-2 h-2" aria-hidden="true">
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
                    <a href="{{ route('craft-jobs.show', $job) }}"
                       class="card-light p-3 block hover:bg-cream-50 transition focus:outline-none focus-visible:ring-4 focus-visible:ring-coral-500/30">
                        <div class="flex items-start gap-2.5">
                            <x-icon-tile :icon="$tm['icon'] ?? 'tools'" size="sm"/>
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
        </x-card>
    @endif

    {{-- ───── FEATURED CRAFTSMEN ───── --}}
    @if($featured->isNotEmpty())
        <h3 class="text-sm font-extrabold text-ink-950 mb-3 inline-flex items-center gap-2">
            <x-icon-tile icon="star" tone="honey" size="sm"/>
            أكتر صنايعية تقييماً
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
            <x-icon-tile icon="bolt" tone="coral" size="sm"/>
            متاحين ٢٤ ساعة + طلبات طارئة
        </h3>
        <div class="space-y-2 mb-5">
            @foreach($emergency as $b)
                @include('craftsmen.partials.craftsman-row', ['craftsman' => $b])
            @endforeach
        </div>
    @endif

    {{-- ───── WHY BANHA.SHOP ───── --}}
    <x-card class="mb-5">
        <h3 class="text-sm font-extrabold text-ink-950 mb-3">ليه بنهاوي للصنايعية؟</h3>
        <ul class="space-y-3 text-sm">
            <li class="flex items-start gap-2.5">
                <x-icon-tile icon="check" tone="mint" size="sm" class="mt-0.5"/>
                <div>
                    <strong class="text-ink-950">مجاناً تماماً</strong>
                    <p class="text-[11px] text-ink-500">مفيش عمولة على الشغلانات. هتفضل دايماً مجاناً.</p>
                </div>
            </li>
            <li class="flex items-start gap-2.5">
                <x-icon-tile icon="whatsapp" tone="mint" size="sm" class="mt-0.5"/>
                <div>
                    <strong class="text-ink-950">تواصل مباشر بالواتساب</strong>
                    <p class="text-[11px] text-ink-500">العميل يكلّمك على رقمك بدون وسيط.</p>
                </div>
            </li>
            <li class="flex items-start gap-2.5">
                <x-icon-tile icon="bell" tone="mint" size="sm" class="mt-0.5"/>
                <div>
                    <strong class="text-ink-950">إشعارات بالشغلانات الجديدة</strong>
                    <p class="text-[11px] text-ink-500">أي طلب في تخصصك ومنطقتك بييجيلك notification.</p>
                </div>
            </li>
            <li class="flex items-start gap-2.5">
                <x-icon-tile icon="star" tone="honey" size="sm" class="mt-0.5"/>
                <div>
                    <strong class="text-ink-950">شارة "موثّق" مدفوعة (اختياري)</strong>
                    <p class="text-[11px] text-ink-500">شارة موثّق رسمية + الظهور أول في النتايج. ادفع مرة، تظهر كنشاط موثّق.</p>
                </div>
            </li>
        </ul>
    </x-card>

    {{-- ───── BIG CTA ───── --}}
    <x-card class="mb-6 text-center">
        <x-icon-tile icon="briefcase" intensity="strong" size="lg" class="mx-auto mb-3"/>
        <h3 class="text-base font-black text-ink-950 mb-2">عندك صنايعة وعاوز تجيب شغل؟</h3>
        <p class="text-xs text-ink-500 mb-4 leading-relaxed max-w-sm mx-auto">
            سجّل نشاطك مجاناً، هتلاقي شغل في تخصصك ومنطقتك يوصلك تلقائياً.
        </p>
        <x-button :href="route('craftsmen.signup')" size="lg" icon="arrow-left" iconEnd>ابدأ التسجيل مجاناً</x-button>
    </x-card>
</div>
@endsection
