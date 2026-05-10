@php
    $zoneSeoTitle = 'دليل '.$zone->name.' · مطاعم وأماكن وأرقام في '.$zone->name.' | بنهاوي';
    $zoneSeoDesc  = 'كل أماكن '.$zone->name.' ('.$zone->governorate.') في مكان واحد: مطاعم، صيدليات، صنايعية، محلات، خط ساخن، مواعيد، إحداثيات GPS. '.$totalCount.' نشاط مفعّل.';
@endphp

@extends('layouts.app', [
    'title'       => $zoneSeoTitle,
    'description' => $zoneSeoDesc,
    'canonical'   => route('zone.show', $zone->slug),
    'keywords'    => 'دليل '.$zone->name.', مطاعم '.$zone->name.', أرقام '.$zone->name.', '.$zone->name.' '.$zone->governorate.', خط ساخن '.$zone->name,
])

@push('json-ld')
@php
    $zoneCrumbs = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'بنهاوي',          'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'الزون والمناطق', 'item' => route('zones')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $zone->name,       'item' => route('zone.show', $zone->slug)],
        ],
    ];
    $zonePlace = [
        '@context'  => 'https://schema.org',
        '@type'     => 'Place',
        'name'      => $zone->name,
        'address'   => [
            '@type'           => 'PostalAddress',
            'addressLocality' => $zone->name,
            'addressRegion'   => $zone->governorate,
            'addressCountry'  => 'EG',
        ],
        'geo'       => [
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float) $zone->lat,
            'longitude' => (float) $zone->lng,
        ],
        'url'       => route('zone.show', $zone->slug),
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($zoneCrumbs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode($zonePlace, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('zones') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-2xl font-black text-ink-950">دليل {{ $zone->name }}</h1>
        <span class="ms-auto text-[11px] font-bold text-ink-500">{{ $totalCount }} نشاط</span>
    </div>

    <p class="text-sm text-ink-500 mb-4 leading-relaxed">
        كل الأماكن في <b class="text-ink-950">{{ $zone->name }}</b> — {{ $zone->governorate }}. أرقام، خطوط ساخنة، مواعيد، وعنوان لكل واحد. لو محل تابع لك مش موجود،
        <a href="{{ route('directory.create') }}?zone={{ $zone->id }}" class="text-coral-600 font-bold underline">أضفه دلوقتي</a>.
    </p>

    {{-- Quick zone-scoped category jump links (huge for SEO + UX) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-5">
        @foreach($categoryLabels as $cat)
            @php $count = $byCategory->get($cat['key'])?->count() ?? 0; @endphp
            <a href="{{ route('directory.category', ['category' => $cat['key'], 'zone' => $zone->id]) }}"
               class="card-light p-3 hover:-translate-y-0.5 hover:shadow-lg transition flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl pill-coral grid place-items-center shrink-0">
                    <x-icon :name="$cat['icon']" class="w-4 h-4"/>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-extrabold text-ink-950 truncate">{{ $cat['label'] }}</div>
                    <div class="text-[10px] text-ink-500">{{ $count }} في {{ $zone->name }}</div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Top businesses in this zone, grouped by category for crawler-friendly text --}}
    @foreach($byCategory as $catKey => $items)
        @php $catMeta = \App\Models\Business::CATEGORIES[$catKey] ?? null; @endphp
        @if($catMeta)
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-base font-extrabold text-ink-950">
                        {{ $catMeta['label'] }} في {{ $zone->name }}
                        <span class="text-ink-400 font-bold text-xs">({{ $items->count() }})</span>
                    </h2>
                    <a href="{{ route('directory.category', ['category' => $catKey, 'zone' => $zone->id]) }}"
                       class="text-xs font-bold text-coral-600">شوف الكل ←</a>
                </div>
                <div class="space-y-2">
                    @foreach($items->take(6) as $b)
                        <a href="{{ route('directory.show', $b) }}"
                           class="card-light p-3 flex items-center gap-3 hover:-translate-y-0.5 hover:shadow-lg transition">
                            <span class="w-10 h-10 rounded-xl pill-coral grid place-items-center shrink-0 text-base">
                                {{ $b->emoji ?? '🏷️' }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-extrabold text-ink-950 truncate">{{ $b->name }}</div>
                                <div class="text-[11px] text-ink-500 truncate">
                                    {{ $b->address ?: $zone->name }}
                                    @if($b->phone) · <span dir="ltr">{{ $b->phone }}</span>
                                    @elseif($b->hotline) · <span dir="ltr">خط ساخن {{ $b->hotline }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($b->is_verified)
                                <x-verified-badge class="shrink-0"/>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    @if($byCategory->isEmpty())
        <div class="card-light p-10 text-center text-ink-500">
            لسه مفيش أنشطة مفعّلة في {{ $zone->name }}. كن أول واحد يضيف مكان!
            <div class="mt-4">
                <a href="{{ route('directory.create') }}?zone={{ $zone->id }}" class="btn-primary">
                    أضف نشاط في {{ $zone->name }}
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
