@extends('layouts.app', [
    'title'       => 'منيو ' . $business->name . ' · ' . ($business->zone->name ?? 'بنها') . ' · بنهاوي',
    'description' => 'منيو وأسعار ' . $business->name . ' في ' . ($business->zone->name ?? 'بنها') . '. شوف كل الأصناف والأسعار وكلّم المطعم مباشرة عبر بنهاوي.',
    'ogImage'     => $business->photo_url,
    'canonical'   => route('menu.public', $business),
])

@push('head')
{{-- JSON-LD: Restaurant + Menu (rich snippets for Google) --}}
<script type="application/ld+json">
@php
    $menuSections = [];
    foreach ($business->menuCategories as $cat) {
        $menuItems = [];
        foreach ($cat->items as $it) {
            $entry = [
                '@type' => 'MenuItem',
                'name'  => $it->name,
            ];
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
            '@type'       => 'AggregateRating',
            'ratingValue' => (float) $business->rating_avg,
            'reviewCount' => (int) $business->ratings_count,
        ];
    }
    if (! empty($menuSections)) {
        $ld['hasMenu'] = [
            '@type'        => 'Menu',
            'name'         => 'منيو ' . $business->name,
            'hasMenuSection' => $menuSections,
        ];
    }
    $ld = array_filter($ld);
@endphp
{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="max-w-2xl mx-auto">
    {{-- Hero --}}
    <div class="relative -mx-4 mb-4">
        @if($business->photo_url)
            <img src="{{ $business->photo_url }}" alt="{{ $business->name }}" class="w-full h-48 md:h-64 object-cover">
        @else
            <div class="w-full h-48 md:h-64 brand-bg"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
        <div class="absolute bottom-0 inset-x-0 p-4">
            <h1 class="text-2xl md:text-3xl font-black text-white drop-shadow inline-flex items-center gap-2 flex-wrap">
                {{ $business->name }}
                @if($business->is_verified)
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-mint-500 text-white">
                        <x-icon name="check" class="w-3 h-3"/> موثّق
                    </span>
                @endif
            </h1>
            <p class="text-white/90 text-sm mt-1">
                @if($business->zone) 📍 {{ $business->zone->name }} @endif
                @if($business->ratings_count > 0)
                    · ⭐ {{ $business->rating_avg }} ({{ $business->ratings_count }})
                @endif
            </p>
        </div>
    </div>

    {{-- Quick contact --}}
    <div class="grid grid-cols-2 gap-2 mb-4">
        @if($business->phone)
            <a href="tel:{{ $business->phone }}" data-track-click="phone" data-business="{{ $business->id }}" class="btn-primary justify-center !py-3 text-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                اتصل
            </a>
        @endif
        @if($business->whatsapp)
            <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}" target="_blank"
               data-track-click="whatsapp" data-business="{{ $business->id }}"
               class="inline-flex items-center justify-center gap-2 py-3 rounded-full font-bold text-white text-sm transition"
               style="background: linear-gradient(135deg, #25D366, #128C7E)">
                <x-icon name="whatsapp" class="w-4 h-4"/> اطلب على واتساب
            </a>
        @endif
    </div>

    {{-- Menu sections --}}
    @if($business->menuCategories->isEmpty() && $looseItems->isEmpty())
        <div class="card-light p-10 text-center">
            <div class="text-5xl mb-3">🍽</div>
            <h3 class="font-extrabold text-ink-950 mb-1">المنيو لسه فاضي</h3>
            <p class="text-ink-500 text-sm">هيتحدّث قريب!</p>
        </div>
    @else
        @foreach($business->menuCategories as $cat)
            @if($cat->items->isNotEmpty())
                <h2 class="text-xl font-extrabold text-ink-950 mt-5 mb-2 inline-flex items-center gap-2">
                    <span class="w-1 h-6 bg-coral-500 rounded-full"></span>
                    {{ $cat->name }}
                </h2>
                <div class="card-light p-2 mb-3 divide-y divide-ink-950/5">
                    @foreach($cat->items as $it)
                        @include('menu.partials.public-item', ['item' => $it, 'currency' => $business->menu_currency ?? 'EGP'])
                    @endforeach
                </div>
            @endif
        @endforeach

        @if($looseItems->isNotEmpty())
            <h2 class="text-xl font-extrabold text-ink-950 mt-5 mb-2">أصناف تانية</h2>
            <div class="card-light p-2 mb-3 divide-y divide-ink-950/5">
                @foreach($looseItems as $it)
                    @include('menu.partials.public-item', ['item' => $it, 'currency' => $business->menu_currency ?? 'EGP'])
                @endforeach
            </div>
        @endif
    @endif

    {{-- Footer attribution --}}
    <div class="text-center my-8">
        <p class="text-xs text-ink-400">
            منيو رقمي مدعوم من
            <a href="{{ route('home') }}" class="font-bold text-coral-600 hover:underline">بنهاوي</a>
        </p>
    </div>
</div>
@endsection
