@extends('layouts.app', ['title' => 'المناطق · بنهاوي'])

@php
    $mapZones = $zones->map(function ($z) use ($stats) {
        $s     = $stats[$z->id] ?? null;
        $count = $s->posts_count ?? 0;
        return [
            'id'     => $z->id,
            'name'   => $z->name,
            'slug'   => $z->slug,
            'lat'    => (float) $z->lat,
            'lng'    => (float) $z->lng,
            'count'  => $count,
            'url'    => route('zone.show', $z->slug),
            'color'  => \App\Support\AnonSeed::avatarColor($z->name),
        ];
    })->filter(fn ($z) => $z['lat'] && $z['lng'])->values();
@endphp

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        (function () {
            const zones = @json($mapZones);
            if (!zones.length || !window.L) return;

            const map = L.map('zones-map', {
                zoomControl: true,
                scrollWheelZoom: false,
                attributionControl: true,
            }).setView([30.30, 31.25], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            const bounds = L.latLngBounds([]);
            zones.forEach((z) => {
                const isHot = z.count >= 5;
                const html = `
                    <div class="zone-pin" style="--c: ${z.color}">
                        <span class="zone-pin-bubble">
                            <span class="zone-pin-letter">${z.name.charAt(0)}</span>
                            ${z.count > 0 ? `<span class="zone-pin-count">${z.count}</span>` : ''}
                        </span>
                        <span class="zone-pin-tail"></span>
                    </div>`;
                const icon = L.divIcon({
                    className: 'zone-pin-wrap',
                    html,
                    iconSize: [56, 70],
                    iconAnchor: [28, 64],
                    popupAnchor: [0, -60],
                });
                const marker = L.marker([z.lat, z.lng], { icon }).addTo(map);
                marker.bindPopup(`
                    <div style="text-align:center; font-family: Cairo, sans-serif; min-width: 140px;">
                        <div style="font-weight:900; font-size:16px; color:#0B0B0C">${z.name}</div>
                        <div style="font-size:11px; color:#5C5C66; margin: 2px 0 8px">${z.count} بوست النهاردة</div>
                        <a href="${z.url}" style="display:inline-block; background: linear-gradient(135deg,#2D5BFF,#FFD440); color:#fff; padding: 6px 14px; border-radius: 999px; font-size:12px; font-weight:700; text-decoration:none">شوف الفيد ←</a>
                    </div>
                `);
                bounds.extend([z.lat, z.lng]);
            });

            // Fit map to all pins
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 11 });
        })();
    </script>
@endpush

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Compact header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-black text-ink-950 inline-flex items-center gap-2">
                <span class="w-9 h-9 rounded-xl brand-bg grid place-items-center text-white">
                    <x-icon name="map-pin" class="w-4 h-4"/>
                </span>
                المناطق
            </h1>
            <p class="text-ink-500 text-sm mt-1">{{ $zones->count() }} منطقة في القليوبية</p>
        </div>
        <a href="{{ route('posts.create') }}" class="btn-primary !py-2 !px-4 text-sm">
            <x-icon name="plus" class="w-4 h-4"/>
            بوست
        </a>
    </div>

    {{-- Interactive map --}}
    <div class="card-light overflow-hidden mb-4 relative">
        <div id="zones-map" class="w-full" style="height: 320px;"></div>
        <div class="absolute top-3 end-3 z-[400] bg-white/95 backdrop-blur rounded-full px-3 py-1.5 text-[11px] font-bold text-ink-950 shadow border border-ink-950/8">
            <span class="w-2 h-2 rounded-full bg-mint-500 inline-block animate-pulse me-1"></span>
            {{ $zones->count() }} منطقة شغّالة
        </div>
    </div>

    {{-- Zones grid --}}
    <div class="grid sm:grid-cols-2 gap-3">
        @foreach($zones as $zone)
            @php
                $s         = $stats[$zone->id] ?? null;
                $count     = $s->posts_count ?? 0;
                $lastAt    = $s && $s->last_post_at
                    ? \Illuminate\Support\Carbon::parse($s->last_post_at)
                    : null;
                $isLive    = $count > 0 && $lastAt && $lastAt->gt(now()->subHours(3));
                $zoneHot   = $hottest[$zone->id] ?? collect();
                $topPost   = $zoneHot->first();
                $color     = \App\Support\AnonSeed::avatarColor($zone->name);
                $initial   = \App\Support\AnonSeed::initial($zone->name);
            @endphp

            <a href="{{ route('feed', ['zone' => $zone->id]) }}"
               class="card-light p-4 group hover:-translate-y-0.5 hover:shadow-lg transition flex flex-col gap-3 min-h-[10rem]">

                {{-- Header row --}}
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 rounded-xl grid place-items-center text-white font-black text-lg shrink-0"
                          style="background: {{ $color }}">{{ $initial }}</span>

                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-extrabold text-ink-950 truncate leading-tight">{{ $zone->name }}</h3>
                        <div class="text-[11px] text-ink-400 mt-0.5">{{ $zone->governorate }}</div>
                    </div>

                    @if($isLive)
                        <span class="pill-blush text-[10px] font-bold px-2 py-1 rounded-full inline-flex items-center gap-1 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-blush-500 animate-pulse"></span>
                            LIVE
                        </span>
                    @endif
                </div>

                {{-- Stats row --}}
                <div class="flex items-center gap-4 text-xs pb-3 border-b border-ink-950/5">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-lg font-black text-ink-950 leading-none">{{ $count }}</span>
                        <span class="text-ink-500">بوست</span>
                    </div>
                    @if($lastAt)
                        <div class="flex items-center gap-1.5 text-ink-500">
                            <x-icon name="bell" class="w-3 h-3"/>
                            <span>{{ $lastAt->diffForHumans(short: true) }}</span>
                        </div>
                    @endif
                </div>

                {{-- Body: top post or empty CTA --}}
                <div class="flex-1 text-xs text-ink-500 leading-relaxed">
                    @if($topPost)
                        <div class="inline-flex items-start gap-1.5">
                            <x-icon name="flame" class="w-3.5 h-3.5 text-coral-500 shrink-0 mt-0.5"/>
                            <span class="line-clamp-2">{{ \Illuminate\Support\Str::limit($topPost->body, 100) }}</span>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-1.5 text-ink-400">
                            <x-icon name="plus" class="w-3.5 h-3.5"/>
                            <span>كن أول واحد ينشر هنا</span>
                        </div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
