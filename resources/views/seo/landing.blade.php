@php
    // Pulled from the controller spec.
    $schemaType = $spec['schema'] ?? 'LocalBusiness';
@endphp

@extends('layouts.app', [
    'title'       => $spec['title'],
    'description' => $spec['desc'],
    'keywords'    => $spec['kw'],
    'canonical'   => $canonical,
    'ogType'      => 'website',
])

@push('json-ld')
@php
    // ── BreadcrumbList ──
    $breadcrumb = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [],
    ];
    foreach ($crumbs as $i => $c) {
        $breadcrumb['itemListElement'][] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $c['name'],
            'item'     => $c['url'],
        ];
    }

    // ── FAQPage ──
    $faqLd = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(fn ($faq) => [
            '@type'          => 'Question',
            'name'           => $faq[0],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $faq[1],
            ],
        ], $spec['faqs']),
    ];

    // ── ItemList of businesses (helps Google understand it's a list page) ──
    $itemList = [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => $spec['h1'],
        'itemListElement' => [],
    ];
    foreach ($businesses->take(20) as $i => $b) {
        $itemList['itemListElement'][] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'url'      => route('directory.show', $b),
            'name'     => $b->name,
        ];
    }

    // ── CollectionPage wrapping it all ──
    $collection = [
        '@context'    => 'https://schema.org',
        '@type'       => 'CollectionPage',
        'name'        => $spec['h1'],
        'description' => $spec['desc'],
        'url'         => $canonical,
        'inLanguage'  => 'ar-EG',
        'isPartOf'    => [
            '@type' => 'WebSite',
            'name'  => 'بنهاوي',
            'url'   => url('/'),
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($breadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($itemList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($collection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ─── Breadcrumb (visible + semantic) ─── --}}
    <nav aria-label="مسار التصفّح" class="mb-3 text-[11px] text-ink-500 flex items-center gap-1.5 flex-wrap">
        @foreach($crumbs as $i => $c)
            @if($i === array_key_last($crumbs))
                <span class="text-ink-950 font-bold truncate max-w-[60vw]">{{ $c['name'] }}</span>
            @else
                <a href="{{ $c['url'] }}" class="hover:text-coral-600 transition">{{ $c['name'] }}</a>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-2.5 h-2.5 rtl:rotate-180 text-ink-400">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            @endif
        @endforeach
    </nav>

    {{-- ─── H1 + intro ─── --}}
    <header class="mb-6 rise rise-1">
        <h1 class="text-2xl md:text-3xl font-black text-ink-950 leading-tight mb-3">
            {{ $spec['h1'] }}
        </h1>
        <p class="text-sm text-ink-700 leading-relaxed">
            {{ $spec['intro'] }}
        </p>
    </header>

    {{-- ─── Businesses list ─── --}}
    @if($businesses->isNotEmpty())
        <section class="mb-8 rise rise-2">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950">القائمة الكاملة</h2>
                <span class="text-[10px] font-bold text-ink-500">{{ $businesses->count() }} نشاط</span>
            </div>

            <ol class="space-y-2" itemscope itemtype="https://schema.org/ItemList">
                @foreach($businesses as $i => $b)
                    @php
                        $cm = $b->categoryMeta();
                        $sm = $b->subTypeMeta();
                        $cover = optional($b->relationLoaded('photos') ? $b->photos->first() : null)->url ?? $b->photo_url ?? null;
                    @endphp
                    <li class="bg-white rounded-2xl ring-1 ring-ink-950/8 overflow-hidden hover:ring-coral-500/40 transition"
                        itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <meta itemprop="position" content="{{ $i + 1 }}">
                        <a href="{{ route('directory.show', $b) }}"
                           class="flex items-stretch"
                           itemprop="url">
                            <div class="w-24 sm:w-32 shrink-0 bg-cream-100 relative">
                                @if($cover)
                                    <img src="{{ $cover }}" alt="{{ $b->name }}" loading="lazy"
                                         class="absolute inset-0 w-full h-full object-cover">
                                @else
                                    <div class="absolute inset-0 grid place-items-center text-ink-300">
                                        <x-icon :name="$cm['icon'] ?? 'bag'" class="w-7 h-7"/>
                                    </div>
                                @endif
                                <span class="absolute top-1.5 start-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-white/90 backdrop-blur text-coral-600 text-[10px] font-black">
                                    {{ $i + 1 }}
                                </span>
                            </div>
                            <div class="flex-1 p-3 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap mb-0.5">
                                    <h3 class="text-sm font-extrabold text-ink-950 truncate" itemprop="name">
                                        {{ $b->name }}
                                    </h3>
                                    @if($b->is_verified)
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-coral-500" aria-label="موثّق">
                                            <path d="M12 2 4 6v6c0 5 3.4 9.6 8 11 4.6-1.4 8-6 8-11V6Zm-1 13-3.5-3.5L9 10l2 2 5-5 1.5 1.5Z"/>
                                        </svg>
                                    @endif
                                    @if($b->is_24h)
                                        <span class="inline-flex items-center text-[9px] font-extrabold text-mint-700 bg-mint-100 px-1.5 py-0.5 rounded-full">٢٤ ساعة</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-ink-500 inline-flex items-center gap-2 flex-wrap">
                                    <span>{{ $sm['label'] ?? $cm['label'] }}</span>
                                    @if($b->zone)
                                        <span>·</span>
                                        <span>{{ $b->zone->name }}</span>
                                    @endif
                                    @if($b->rating_avg && $b->ratings_count)
                                        <span>·</span>
                                        <span class="inline-flex items-center gap-0.5 text-honey-700 font-bold">
                                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3">
                                                <polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/>
                                            </svg>
                                            {{ number_format((float) $b->rating_avg, 1) }}
                                        </span>
                                    @endif
                                </div>
                                @if($b->address)
                                    <div class="text-[11px] text-ink-500 mt-1 truncate">
                                        {{ $b->address }}
                                    </div>
                                @endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ol>
        </section>
    @else
        <div class="card-light p-10 text-center mb-8">
            <h3 class="text-base font-extrabold text-ink-950 mb-1">لسه بنجمّع البيانات</h3>
            <p class="text-[12px] text-ink-500 max-w-xs mx-auto leading-relaxed mb-4">
                لو عندك نشاط ضمن الفئة دي، ضيفه على بنهاوي وكمّلنا الدليل.
            </p>
            <a href="{{ route('marketing.claim') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-coral-500 text-white text-[12px] font-extrabold hover:bg-coral-600 transition">
                ضيف نشاطك مجاناً
            </a>
        </div>
    @endif

    {{-- ─── Related links (internal linking boost) ─── --}}
    <section class="mb-8 rise rise-3">
        <h2 class="text-base font-black text-ink-950 mb-3">دلائل تانية ممكن تهمك</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @php
                $related = [
                    ['best-restaurants-in-banha', 'مطاعم بنها'],
                    ['cafes-in-banha',            'كافيهات بنها'],
                    ['doctors-in-banha',          'دكاترة بنها'],
                    ['pharmacies-in-banha',       'صيدليات بنها'],
                    ['places-to-go-in-banha',     'أماكن خروج في بنها'],
                    ['24-hour-pharmacies-in-banha','صيدليات نوبتجية ٢٤ ساعة'],
                ];
            @endphp
            @foreach($related as [$slug2, $label])
                @if($slug2 !== $slug)
                    <a href="{{ url('/' . $slug2) }}"
                       class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 hover:ring-coral-500/40 transition flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-coral-50 text-coral-600 grid place-items-center shrink-0">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-3.5 h-3.5 rtl:rotate-180">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </span>
                        <span class="text-sm font-extrabold text-ink-950">{{ $label }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </section>

    {{-- ─── FAQ section (matches FAQPage schema) ─── --}}
    @if(! empty($spec['faqs']))
        <section class="mb-8 rise rise-3" itemscope itemtype="https://schema.org/FAQPage">
            <h2 class="text-base font-black text-ink-950 mb-3">أسئلة شائعة</h2>
            <div class="space-y-2">
                @foreach($spec['faqs'] as [$q, $a])
                    <details class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-4 group"
                             itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                        <summary class="text-sm font-extrabold text-ink-950 cursor-pointer list-none flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-4 h-4 text-coral-600 transition group-open:rotate-90 rtl:rotate-180 rtl:group-open:rotate-90">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                            <span class="flex-1" itemprop="name">{{ $q }}</span>
                        </summary>
                        <div class="mt-2 ps-6" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                            <p class="text-[12px] text-ink-500 leading-relaxed" itemprop="text">
                                {{ $a }}
                            </p>
                        </div>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Final CTA ─── --}}
    <section class="rise rise-4 mb-6">
        <div class="rounded-3xl p-5 ring-1 ring-coral-500/20 bg-coral-50">
            <div class="flex items-start gap-3">
                <span class="w-12 h-12 rounded-2xl bg-coral-500 text-white grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                        <path d="M3 9h18l-1.5-5h-15z"/>
                        <path d="M4 9v11a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V9"/>
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-extrabold text-ink-950 mb-1">عندك نشاط مش موجود في الدليل؟</div>
                    <p class="text-[12px] text-ink-500 leading-relaxed mb-2">
                        ضيف نشاطك مجاناً على بنهاوي — يظهر في كل صفحات بنها زي دي ويوصل لآلاف الزوار.
                    </p>
                    <a href="{{ route('marketing.claim') }}"
                       class="inline-flex items-center gap-1.5 text-sm font-extrabold text-coral-600 hover:underline">
                        ابدأ مجاناً
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3.5 h-3.5 rtl:rotate-180">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
