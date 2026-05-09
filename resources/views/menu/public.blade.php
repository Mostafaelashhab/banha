@extends('layouts.app', [
    'title'       => 'منيو ' . $business->name . ' · ' . ($business->zone->name ?? 'بنها') . ' · بنهاوي',
    'description' => 'منيو وأسعار ' . $business->name . ' في ' . ($business->zone->name ?? 'بنها') . '. شوف كل الأصناف والأسعار وكلّم المطعم مباشرة عبر بنهاوي.',
    'ogImage'     => $business->photo_url,
    'canonical'   => route('menu.public', $business),
])

@push('head')
<style>
    .menu-leader {
        flex: 1;
        border-bottom: 1px dotted rgba(11, 11, 12, .25);
        margin: 0 .5rem;
        align-self: end;
        height: 1em;
        margin-bottom: .35em;
    }
    .menu-cat-nav { scroll-snap-type: x mandatory; }
    .menu-cat-nav > a { scroll-snap-align: start; }
    .menu-cat-nav > a.is-active {
        background: var(--color-coral-500, #FF7A4D);
        color: #fff;
        border-color: var(--color-coral-500, #FF7A4D);
    }
    html { scroll-padding-top: 80px; }
</style>

{{-- JSON-LD: Restaurant + Menu (rich snippets for Google) --}}
<script type="application/ld+json">
@php
    $menuSections = [];
    foreach ($business->menuCategories as $cat) {
        $menuItems = [];
        foreach ($cat->items as $it) {
            $entry = ['@type' => 'MenuItem', 'name' => $it->name];
            if ($it->description) $entry['description'] = $it->description;
            if ($it->price) {
                $entry['offers'] = [
                    '@type'         => 'Offer',
                    'price'         => (string) $it->price,
                    'priceCurrency' => $business->menu_currency ?? 'EGP',
                ];
            }
            if ($it->photo_url) $entry['image'] = url($it->photo_url);
            $menuItems[] = $entry;
        }
        if (! empty($menuItems)) {
            $menuSections[] = [
                '@type'        => 'MenuSection',
                'name'         => $cat->name,
                'hasMenuItem'  => $menuItems,
            ];
        }
    }

    $ld = [
        '@context' => 'https://schema.org',
        '@type'    => $business->category === 'food' ? 'Restaurant' : 'LocalBusiness',
        'name'     => $business->name,
        'url'      => route('menu.public', $business),
        'image'    => $business->photo_url ? url($business->photo_url) : null,
        'address'  => [
            '@type'           => 'PostalAddress',
            'addressLocality' => $business->zone->name ?? 'بنها',
            'addressRegion'   => 'القليوبية',
            'addressCountry'  => 'EG',
            'streetAddress'   => $business->address,
        ],
        'telephone' => $business->phone,
    ];
    if ($business->lat && $business->lng) {
        $ld['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) $business->lat, 'longitude' => (float) $business->lng];
    }
    if ($business->ratings_count > 0) {
        $ld['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (float) $business->rating_avg,
            'reviewCount' => (int) $business->ratings_count,
        ];
    }
    if (! empty($menuSections)) {
        $ld['hasMenu'] = [
            '@type' => 'Menu',
            'name'  => 'منيو ' . $business->name,
            'hasMenuSection' => $menuSections,
        ];
    }
    $ld = array_filter($ld);
@endphp
{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@php
    $heroPhoto = ($business->photo_url && ! str_contains($business->photo_url, 'd-innova.com')) ? $business->photo_url : null;
    $heroPhoto = $heroPhoto ?: \App\Support\BusinessCovers::pick($business->category, $business->id);
    $itemsCount = $business->menuItems()->where('is_available', true)->count();

    // Build a pre-filled WhatsApp order message
    $waOrderMsg = $business->whatsapp
        ? 'عاوز أطلب من منيو ' . $business->name . ' — شفت الأصناف على ' . route('menu.public', $business)
        : null;
@endphp

@section('content')
<div class="max-w-2xl mx-auto pb-20">

    {{-- Hero --}}
    <div class="relative -mx-4 mb-3 overflow-hidden aspect-[16/9] bg-gradient-to-br from-coral-500 to-honey-500">
        <img src="{{ $heroPhoto }}" alt="{{ $business->name }}" loading="eager"
             class="absolute inset-0 w-full h-full object-cover"
             onerror="this.style.display='none'">
        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent"></div>

        <div class="absolute top-3 start-3 flex flex-col gap-1.5">
            @if($business->is_verified)
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-mint-500 text-white w-fit">
                    <x-icon name="check" class="w-3 h-3"/> موثّق
                </span>
            @endif
            @if($business->is_24h)
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-honey-500 text-ink-950 w-fit">
                    ٢٤ ساعة
                </span>
            @endif
        </div>

        <div class="absolute bottom-0 inset-x-0 p-4">
            <h1 class="text-2xl md:text-3xl font-black text-white leading-tight drop-shadow-lg">{{ $business->name }}</h1>
            <div class="flex items-center gap-2 mt-1.5 text-white/90 text-sm flex-wrap">
                @if($business->zone)
                    <span class="inline-flex items-center gap-1">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $business->zone->name }}
                    </span>
                @endif
                @if($business->ratings_count > 0)
                    <span class="text-white/60">·</span>
                    <span class="inline-flex items-center gap-0.5">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3 text-honey-400"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                        <span class="font-bold">{{ $business->rating_avg }}</span>
                        <span class="text-white/70 text-xs">({{ $business->ratings_count }})</span>
                    </span>
                @endif
                @if($itemsCount > 0)
                    <span class="text-white/60">·</span>
                    <span>{{ $itemsCount }} صنف</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Top action bar (sticky-feel CTAs) --}}
    <div class="grid grid-cols-{{ ($business->phone && $business->whatsapp) ? '2' : '1' }} gap-2 mb-3">
        @if($business->phone)
            <a href="tel:{{ $business->phone }}" data-track-click="phone" data-business="{{ $business->id }}"
               class="btn-primary justify-center !py-3 text-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                اتصل
            </a>
        @endif
        @if($business->whatsapp)
            <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}?text={{ urlencode($waOrderMsg) }}"
               target="_blank"
               data-track-click="whatsapp" data-business="{{ $business->id }}"
               class="inline-flex items-center justify-center gap-2 py-3 rounded-full font-bold text-white text-sm transition hover:scale-[1.02]"
               style="background: linear-gradient(135deg, #25D366, #128C7E)">
                <x-icon name="whatsapp" class="w-4 h-4"/>
                اطلب على واتساب
            </a>
        @endif
    </div>

    {{-- Sticky category nav (jumps to sections) --}}
    @if($business->menuCategories->where('items', '!=', null)->count() > 0)
        @php
            $visibleCats = $business->menuCategories->filter(fn ($c) => $c->items->isNotEmpty());
        @endphp
        @if($visibleCats->count() > 1)
            <div class="sticky top-14 z-20 -mx-4 px-4 py-2 bg-cream-100/90 backdrop-blur border-b border-ink-950/8">
                <div class="menu-cat-nav flex gap-2 overflow-x-auto scrollbar-hide">
                    @foreach($visibleCats as $cat)
                        <a href="#cat-{{ $cat->id }}"
                           class="chip shrink-0 inline-flex items-center gap-1.5"
                           data-cat="{{ $cat->id }}">
                            {{ $cat->name }}
                            <span class="text-[10px] opacity-60">({{ $cat->items->count() }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- Menu sections --}}
    @if($business->menuCategories->isEmpty() && $looseItems->isEmpty())
        <div class="card-light p-10 text-center mt-3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-16 h-16 mx-auto text-ink-300 mb-3">
                <path d="M3 11h18l-1 9H4z"/><path d="M7 11V8a5 5 0 0 1 10 0v3"/>
            </svg>
            <h3 class="font-extrabold text-ink-950 mb-1">المنيو لسه فاضي</h3>
            <p class="text-ink-500 text-sm">هيتحدّث قريب!</p>
        </div>
    @else
        @foreach($business->menuCategories as $cat)
            @if($cat->items->isNotEmpty())
                <section id="cat-{{ $cat->id }}" class="mt-5 mb-3">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <h2 class="text-xl font-black text-ink-950 inline-flex items-center gap-2">
                            <span class="w-1 h-6 bg-coral-500 rounded-full"></span>
                            {{ $cat->name }}
                        </h2>
                        <span class="text-xs font-bold text-ink-400">{{ $cat->items->count() }} صنف</span>
                    </div>
                    <div class="card-light p-3 divide-y divide-ink-950/5">
                        @foreach($cat->items as $it)
                            @include('menu.partials.public-item', ['item' => $it, 'currency' => $business->menu_currency ?? 'EGP'])
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        @if($looseItems->isNotEmpty())
            <section class="mt-5 mb-3">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h2 class="text-xl font-black text-ink-950 inline-flex items-center gap-2">
                        <span class="w-1 h-6 bg-coral-500 rounded-full"></span>
                        أصناف تانية
                    </h2>
                    <span class="text-xs font-bold text-ink-400">{{ $looseItems->count() }} صنف</span>
                </div>
                <div class="card-light p-3 divide-y divide-ink-950/5">
                    @foreach($looseItems as $it)
                        @include('menu.partials.public-item', ['item' => $it, 'currency' => $business->menu_currency ?? 'EGP'])
                    @endforeach
                </div>
            </section>
        @endif
    @endif

    {{-- Address + hours card --}}
    @if($business->address || $business->hours)
        <div class="card-light p-4 mt-5 space-y-3">
            @if($business->address)
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-xl pill-coral grid place-items-center shrink-0">
                        <x-icon name="map-pin" class="w-4 h-4"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] text-ink-500">العنوان</div>
                        <div class="text-sm font-bold text-ink-950">{{ $business->address }}</div>
                    </div>
                </div>
            @endif
            @if($business->hours && ! $business->is_24h)
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-xl pill-honey grid place-items-center shrink-0">
                        <x-icon name="bell" class="w-4 h-4"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] text-ink-500">المواعيد</div>
                        <div class="text-sm font-bold text-ink-950">{{ $business->hours }}</div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Footer attribution --}}
    <div class="text-center mt-8 mb-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-ink-400 hover:text-coral-600 transition">
            <span class="w-7 h-7 rounded-lg brand-bg grid place-items-center text-white font-black text-xs">ب</span>
            <span class="text-xs font-bold">منيو رقمي مدعوم من بنهاوي</span>
        </a>
    </div>
</div>

{{-- Sticky bottom WhatsApp CTA on long menus --}}
@if($business->whatsapp && $itemsCount > 5)
    <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}?text={{ urlencode($waOrderMsg) }}"
       target="_blank"
       data-track-click="whatsapp" data-business="{{ $business->id }}"
       class="fixed bottom-4 inset-x-4 max-w-md mx-auto z-30 inline-flex items-center justify-center gap-2 py-3.5 rounded-full font-extrabold text-white text-sm shadow-2xl"
       style="background: linear-gradient(135deg, #25D366, #128C7E)">
        <x-icon name="whatsapp" class="w-5 h-5"/>
        اطلب الآن على واتساب
    </a>
@endif

@push('scripts')
<script>
// Highlight category in sticky nav as user scrolls through sections
(function () {
    const nav = document.querySelector('.menu-cat-nav');
    if (!nav) return;
    const links = nav.querySelectorAll('a[data-cat]');
    if (!links.length) return;

    const setActive = (id) => {
        links.forEach(a => a.classList.toggle('is-active', a.dataset.cat === String(id)));
    };

    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                const id = e.target.id.replace('cat-', '');
                setActive(id);
            }
        });
    }, { rootMargin: '-30% 0px -50% 0px', threshold: 0 });

    document.querySelectorAll('section[id^="cat-"]').forEach(s => io.observe(s));
})();
</script>
@endpush
@endsection
