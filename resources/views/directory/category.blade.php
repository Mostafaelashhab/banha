@php
    $catSeoTitle = $meta['label'].' في بنها · دليل أرقام ومواعيد | بنهاوي';
    $catSeoDesc  = 'كل '.$meta['label'].' في بنها والقليوبية: مواعيد العمل، الأرقام، الخط الساخن، العنوان، التقييمات. تحديث يومي.';
@endphp
@extends('layouts.app', [
    'title'       => $catSeoTitle,
    'description' => $catSeoDesc,
    'canonical'   => route('directory.category', $category),
    'keywords'    => $meta['label'].' بنها, دليل بنها, '.$meta['label'].' القليوبية, أرقام ومواعيد بنها',
])

@push('json-ld')
@php
    $catCrumbs = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'بنهاوي', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'الدليل', 'item' => route('directory.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $meta['label'], 'item' => route('directory.category', $category)],
        ],
    ];

    $faqAnswers = [
        'food'      => 'هتلاقي مطاعم وكافيهات ومشاوي في كل أحياء بنها. كل مكان بمواعيده، أرقامه، ولو فيه دليفري.',
        'medical'   => 'صيدليات، عيادات، معامل، ومستشفيات بنها مع مواعيد وخط ساخن لكل واحد.',
        'shops'     => 'كل المحلات في بنها — موبايلات، ملابس، سوبر ماركت، أثاث — برقم وعنوان.',
        'craftsmen' => 'سباكين، كهربائيين، نجارين وحدادين في بنها، بتقييمات الزباين.',
        'services'  => 'جيم، حضانة، مغسلة، صبغة، كل الخدمات في بنها برقم ومواعيد.',
    ];
    $catFaq = [
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name'  => 'إيه أحسن '.$meta['label'].' في بنها؟',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faqAnswers[$category] ?? ('بنهاوي بيرتب '.$meta['label'].' في بنها بحسب التقييمات والمصداقية.')],
            ],
            [
                '@type' => 'Question',
                'name'  => 'فين أرقام '.$meta['label'].' في بنها؟',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'كل '.$meta['label'].' في الدليل عندها رقم تليفون أو خط ساخن، وممكن تكلّمهم على واتساب من نفس الصفحة.'],
            ],
            [
                '@type' => 'Question',
                'name'  => 'إزاي أعرف '.$meta['label'].' المفتوحة دلوقتي؟',
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'فيه فلتر "مفتوح دلوقتي" بيوريك المحلات اللي بتشتغل في الوقت ده فقط.'],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($catCrumbs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode($catFaq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@php
    // Build a URL with one filter toggled on/off (preserves other params)
    $toggleUrl = function (string $param, $value, $isActive) use ($category, $activeSubType, $activeZone, $activeFilters, $q) {
        $base = ['category' => $category];
        if ($activeSubType) $base['type'] = $activeSubType;
        if ($activeZone)    $base['zone'] = $activeZone;
        if ($q !== '')      $base['q']    = $q;
        if ($activeFilters['verified']) $base['verified'] = 1;
        if ($activeFilters['open24'])   $base['open24']   = 1;
        if ($activeFilters['has_menu']) $base['has_menu'] = 1;
        if (! empty($activeFilters['extra'])) $base['extra'] = $activeFilters['extra'];

        if ($param === 'extra') {
            $list = (array) ($base['extra'] ?? []);
            $list = $isActive ? array_values(array_diff($list, [$value])) : array_values(array_unique([...$list, $value]));
            if (empty($list)) unset($base['extra']); else $base['extra'] = $list;
        } else {
            if ($isActive) unset($base[$param]); else $base[$param] = 1;
        }
        return route('directory.category', $base);
    };

    $activeFilterCount = ($activeFilters['verified'] ? 1 : 0)
        + ($activeFilters['open24'] ? 1 : 0)
        + ($activeFilters['has_menu'] ? 1 : 0)
        + count($activeFilters['extra'])
        + ($activeZone ? 1 : 0);
@endphp

@section('content')
<div class="max-w-3xl mx-auto pb-8">

    {{-- ─── Header ────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 mb-4 rise rise-1">
        <a href="{{ route('directory.index') }}"
           class="w-10 h-10 rounded-2xl bg-cream-200 grid place-items-center text-ink-950 shrink-0 transition"
           aria-label="رجوع">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-black text-ink-950 inline-flex items-center gap-2">
                <span class="inline-flex" style="color: {{ $meta['color'] }}">
                    <x-icon :name="$meta['icon'] ?? 'bag'" class="w-5 h-5"/>
                </span>
                <span class="truncate">{{ $meta['label'] }}</span>
            </h1>
            <div class="text-xs text-ink-400 mt-0.5">{{ $businesses->total() }} نشاط</div>
        </div>
    </div>

    {{-- ─── Search + Filter ────────────────────────────────────── --}}
    <form id="cat-search-form" method="GET" action="{{ route('directory.category', $category) }}" class="mb-4 rise rise-2">
        @if($activeSubType) <input type="hidden" name="type" value="{{ $activeSubType }}"> @endif
        @if($activeZone)    <input type="hidden" name="zone" value="{{ $activeZone }}"> @endif
        @if($activeFilters['verified']) <input type="hidden" name="verified" value="1"> @endif
        @if($activeFilters['open24'])   <input type="hidden" name="open24"   value="1"> @endif
        @if($activeFilters['has_menu']) <input type="hidden" name="has_menu" value="1"> @endif
        @foreach($activeFilters['extra'] as $k)
            <input type="hidden" name="extra[]" value="{{ $k }}">
        @endforeach

        <div class="flex items-center gap-2">
            <div class="flex-1 relative">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     class="absolute top-1/2 -translate-y-1/2 start-3.5 w-4 h-4 text-ink-400 pointer-events-none">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input id="cat-search" type="search" name="q" value="{{ $q }}" autocomplete="off"
                       placeholder="دوّر باسم النشاط…"
                       class="w-full bg-cream-200 rounded-2xl ps-9 pe-4 py-3 text-sm placeholder-ink-400 focus:outline-none transition" />
            </div>
            <button type="button" id="cat-filter-btn"
                    class="relative w-12 h-12 rounded-2xl bg-cream-200 grid place-items-center text-ink-950 shrink-0 transition"
                    aria-label="فلتر">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-4 h-4">
                    <line x1="4"  y1="6"  x2="20" y2="6"/>
                    <line x1="7"  y1="12" x2="17" y2="12"/>
                    <line x1="10" y1="18" x2="14" y2="18"/>
                </svg>
                @if($activeFilterCount > 0)
                    <span class="absolute -top-1 -end-1 min-w-[18px] h-[18px] px-1 grid place-items-center bg-coral-500 text-white text-[10px] font-extrabold rounded-full">
                        {{ $activeFilterCount }}
                    </span>
                @endif
            </button>
        </div>
    </form>

    {{-- ─── Sub-type chips ─────────────────────────────────────── --}}
    @if($subTypes->isNotEmpty())
        <div class="overflow-x-auto scrollbar-hide -mx-4 mb-4 rise rise-3">
            <div class="flex gap-2 px-4 w-max">
                <a href="{{ route('directory.category', ['category' => $category, 'zone' => $activeZone, 'q' => $q ?: null]) }}"
                   class="chip {{ ! $activeSubType ? 'chip-active' : '' }}">الكل</a>
                @foreach($subTypes as $st)
                    <a href="{{ route('directory.category', ['category' => $category, 'type' => $st['key'], 'zone' => $activeZone, 'q' => $q ?: null]) }}"
                       class="chip inline-flex items-center gap-1.5 {{ $activeSubType === $st['key'] ? 'chip-active' : '' }}">
                        <x-icon :name="$st['icon'] ?? 'bag'" class="w-3.5 h-3.5"/>
                        {{ $st['label'] }}
                        @if(($subTypeCounts[$st['key']] ?? 0) > 0)
                            <span class="opacity-60 text-xs">{{ $subTypeCounts[$st['key']] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ─── Results ─────────────────────────────────────────────── --}}
    <div id="cat-results" class="rise rise-4">
        @if($businesses->isEmpty())
            <div class="bg-white rounded-2xl p-10 text-center">
                <div class="w-14 h-14 rounded-2xl mx-auto mb-3 grid place-items-center"
                     style="background: {{ $meta['color'] }}14; color: {{ $meta['color'] }};">
                    <x-icon :name="$meta['icon'] ?? 'bag'" class="w-7 h-7"/>
                </div>
                <h3 class="font-extrabold text-ink-950 mb-1">مفيش نتيجة</h3>
                <p class="text-ink-500 text-sm">جرّب فلتر تاني أو اطلب من الإدارة تضيف نشاطك.</p>
            </div>
        @else
            <div class="space-y-3" data-infinite-scroll>
                @include('directory.partials.category-page', ['businesses' => $businesses])
            </div>
        @endif
    </div>
</div>

{{-- ─── Filter bottom sheet ─────────────────────────────────── --}}
<div id="cat-filter-sheet" class="modal-wrap" role="dialog" aria-modal="true" aria-labelledby="cat-filter-title">
    <div class="modal-backdrop" data-close></div>
    <div class="modal-sheet">
        <div class="px-5 pt-3 pb-5 max-h-[85vh] overflow-y-auto">
            <div class="mx-auto w-10 h-1 rounded-full bg-ink-950/15 mb-4"></div>

            <div class="flex items-center justify-between mb-2">
                <h3 id="cat-filter-title" class="text-lg font-black text-ink-950">فلتر</h3>
                <div class="flex items-center gap-3">
                    @if($activeFilterCount > 0)
                        <a href="{{ route('directory.category', ['category' => $category, 'q' => $q ?: null]) }}"
                           class="text-xs font-bold text-coral-600">مسح الكل</a>
                    @endif
                    <button type="button" data-close
                            class="w-9 h-9 rounded-full grid place-items-center text-ink-500 hover:bg-ink-950/5"
                            aria-label="إغلاق">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" class="w-5 h-5">
                            <line x1="18" y1="6" x2="6"  y2="18"/>
                            <line x1="6"  y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Sub-type section --}}
            @if($subTypes->isNotEmpty())
                <details class="cat-section" open>
                    <summary class="cat-section-head">
                        <span class="text-sm font-extrabold text-ink-950">النوع</span>
                        <span class="text-xs font-bold text-ink-400 truncate ms-auto me-2">
                            {{ $activeSubType ? ($subTypes->firstWhere('key', $activeSubType)['label'] ?? '—') : 'الكل' }}
                        </span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="cat-section-chev w-4 h-4 text-ink-400 shrink-0">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </summary>
                    <div class="cat-section-body">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('directory.category', ['category' => $category, 'zone' => $activeZone, 'q' => $q ?: null]) }}"
                               class="chip {{ ! $activeSubType ? 'chip-active' : '' }}">الكل</a>
                            @foreach($subTypes as $st)
                                <a href="{{ route('directory.category', ['category' => $category, 'type' => $st['key'], 'zone' => $activeZone, 'q' => $q ?: null]) }}"
                                   class="chip inline-flex items-center gap-1.5 {{ $activeSubType === $st['key'] ? 'chip-active' : '' }}">
                                    <x-icon :name="$st['icon'] ?? 'bag'" class="w-3.5 h-3.5"/>
                                    {{ $st['label'] }}
                                    @if(($subTypeCounts[$st['key']] ?? 0) > 0)
                                        <span class="opacity-60 text-xs">{{ $subTypeCounts[$st['key']] }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endif

            {{-- Zones section --}}
            @if($zones->isNotEmpty())
                <details class="cat-section" @if($activeZone) open @endif>
                    <summary class="cat-section-head">
                        <span class="text-sm font-extrabold text-ink-950">المناطق</span>
                        <span class="text-xs font-bold text-ink-400 truncate ms-auto me-2">
                            {{ $activeZone ? ($zones->firstWhere('id', $activeZone)->name ?? 'منطقة') : 'كل المناطق' }}
                        </span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="cat-section-chev w-4 h-4 text-ink-400 shrink-0">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </summary>
                    <div class="cat-section-body">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('directory.category', ['category' => $category, 'type' => $activeSubType, 'q' => $q ?: null]) }}"
                               class="chip {{ ! $activeZone ? 'chip-active' : '' }}">كل المناطق</a>
                            @foreach($zones as $z)
                                <a href="{{ route('directory.category', ['category' => $category, 'type' => $activeSubType, 'zone' => $z->id, 'q' => $q ?: null]) }}"
                                   class="chip {{ $activeZone === $z->id ? 'chip-active' : '' }}">
                                    {{ $z->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endif

            {{-- Options section --}}
            @php
                $optionsActive = ($activeFilters['verified'] ? 1 : 0) + ($activeFilters['open24'] ? 1 : 0) + ($activeFilters['has_menu'] ? 1 : 0);
            @endphp
            <details class="cat-section" @if($optionsActive) open @endif>
                <summary class="cat-section-head">
                    <span class="text-sm font-extrabold text-ink-950">خيارات</span>
                    <span class="text-xs font-bold text-ink-400 ms-auto me-2">
                        {{ $optionsActive ? $optionsActive.' مفعّل' : '—' }}
                    </span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="cat-section-chev w-4 h-4 text-ink-400 shrink-0">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </summary>
                <div class="cat-section-body">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ $toggleUrl('verified', null, $activeFilters['verified']) }}"
                           class="chip inline-flex items-center gap-1 {{ $activeFilters['verified'] ? 'chip-active' : '' }}">
                            <x-icon name="check" class="w-3 h-3"/>
                            موثّق
                        </a>
                        <a href="{{ $toggleUrl('open24', null, $activeFilters['open24']) }}"
                           class="chip {{ $activeFilters['open24'] ? 'chip-active' : '' }}">
                            ٢٤ ساعة
                        </a>
                        @if($category === 'food')
                            <a href="{{ $toggleUrl('has_menu', null, $activeFilters['has_menu']) }}"
                               class="chip {{ $activeFilters['has_menu'] ? 'chip-active' : '' }}">
                                عنده منيو
                            </a>
                        @endif
                    </div>
                </div>
            </details>

            {{-- Per-category extras --}}
            @if(! empty($checkboxExtras))
                <details class="cat-section" @if(! empty($activeFilters['extra'])) open @endif>
                    <summary class="cat-section-head">
                        <span class="text-sm font-extrabold text-ink-950">إضافات</span>
                        <span class="text-xs font-bold text-ink-400 ms-auto me-2">
                            {{ count($activeFilters['extra']) ? count($activeFilters['extra']).' مفعّل' : '—' }}
                        </span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="cat-section-chev w-4 h-4 text-ink-400 shrink-0">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </summary>
                    <div class="cat-section-body">
                        <div class="flex flex-wrap gap-2">
                            @foreach($checkboxExtras as $key => $def)
                                @php $isActive = in_array($key, $activeFilters['extra'], true); @endphp
                                <a href="{{ $toggleUrl('extra', $key, $isActive) }}"
                                   class="chip {{ $isActive ? 'chip-active' : '' }}">
                                    {{ $def['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    // ── Filter bottom sheet ─────────────────────────────────
    const btn   = document.getElementById('cat-filter-btn');
    const sheet = document.getElementById('cat-filter-sheet');
    if (btn && sheet) {
        const open  = () => { sheet.classList.add('open');    document.body.style.overflow = 'hidden'; };
        const close = () => { sheet.classList.remove('open'); document.body.style.overflow = ''; };
        btn.addEventListener('click', open);
        sheet.querySelectorAll('[data-close]').forEach((el) => el.addEventListener('click', close));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sheet.classList.contains('open')) close();
        });
    }

    // ── Realtime search via partial endpoint ────────────────
    const form    = document.getElementById('cat-search-form');
    const input   = document.getElementById('cat-search');
    const results = document.getElementById('cat-results');
    if (form && input && results) {
        let debounceT, aborter;

        const run = () => {
            if (aborter) aborter.abort();
            aborter = new AbortController();

            const params = new URLSearchParams(new FormData(form));
            params.set('partial', '1');

            fetch(`${form.action}?${params.toString()}`, {
                signal: aborter.signal,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then((r) => r.text())
                .then((html) => {
                    const trimmed = html.trim();
                    // Partial returns business rows + sentinel. If first non-whitespace
                    // is the sentinel, there are no results.
                    const hasRows = trimmed && !trimmed.startsWith('<div data-feed-end');
                    if (hasRows) {
                        results.innerHTML = `<div class="space-y-3" data-infinite-scroll>${html}</div>`;
                    } else {
                        results.innerHTML = `
                            <div class="bg-white rounded-2xl p-10 text-center">
                                <h3 class="font-extrabold text-ink-950 mb-1">مفيش نتيجة</h3>
                                <p class="text-ink-500 text-sm">جرّب كلمة تانية أو غيّر الفلتر.</p>
                            </div>`;
                    }
                    const url = new URL(window.location);
                    if (input.value) url.searchParams.set('q', input.value);
                    else             url.searchParams.delete('q');
                    history.replaceState({}, '', url);
                })
                .catch((err) => { if (err.name !== 'AbortError') console.error(err); });
        };

        input.addEventListener('input', () => {
            clearTimeout(debounceT);
            debounceT = setTimeout(run, 250);
        });
        form.addEventListener('submit', (e) => { e.preventDefault(); run(); });
    }
})();
</script>
@endpush
