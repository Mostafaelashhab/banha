@extends('layouts.app', [
    'title'       => 'خريطة بنها · بنهاوي',
    'description' => 'كل النشاطات في بنها على خريطة واحدة — مطاعم، صنايعية، دكاترة، حكومة، مواصلات، طوارئ.',
])

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    #banha-map {
        height: calc(100vh - 180px);
        min-height: 520px;
        border-radius: 28px;
        z-index: 0;
        background: #FFF7F1;
        box-shadow: 0 12px 40px -10px rgba(11,11,12,.18);
    }
    /* Soften OSM tiles + slight warm shift to match cream palette */
    #banha-map .leaflet-tile {
        filter: saturate(.75) brightness(1.04) contrast(.95);
    }

    /* ── Custom pin (modern teardrop with subtle inner highlight) ── */
    .biz-pin-wrap {
        width: 36px;
        height: 44px;
        position: relative;
        filter: drop-shadow(0 6px 10px rgba(0, 0, 0, .25));
        transition: transform .15s ease;
    }
    .biz-pin-wrap:hover { transform: translateY(-3px) scale(1.08); }
    .biz-pin {
        width: 36px;
        height: 36px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        border: 3px solid #fff;
        display: grid;
        place-items: center;
        position: relative;
    }
    .biz-pin::before {
        content: '';
        position: absolute;
        inset: 4px;
        border-radius: 50% 50% 50% 0;
        background: linear-gradient(135deg, rgba(255,255,255,.35), transparent 50%);
        pointer-events: none;
    }
    .biz-pin svg {
        transform: rotate(45deg);
        color: #fff;
        width: 16px;
        height: 16px;
        position: relative;
        z-index: 1;
    }
    /* Verified ring around pin */
    .biz-pin.is-verified::after {
        content: '';
        position: absolute;
        inset: -5px;
        border-radius: 50% 50% 50% 0;
        border: 2px solid #1FA857;
        pointer-events: none;
    }

    /* ── Promoted pin: bigger, gold gradient, pulsing halo ── */
    .biz-pin-wrap.is-promoted {
        width: 48px;
        height: 60px;
    }
    .biz-pin-wrap.is-promoted .biz-pin {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #FFB85C 0%, #FF9F2D 100%) !important;
        box-shadow: 0 0 0 4px rgba(255, 184, 92, .35), 0 8px 18px -4px rgba(255, 159, 45, .55);
    }
    .biz-pin-wrap.is-promoted .biz-pin svg {
        width: 22px;
        height: 22px;
    }
    .biz-pin-wrap.is-promoted::before {
        content: '';
        position: absolute;
        inset: 4px 4px auto 4px;
        height: 48px;
        border-radius: 50%;
        background: rgba(255, 184, 92, .35);
        animation: bnh-pulse 2s ease-out infinite;
        z-index: -1;
    }
    @keyframes bnh-pulse {
        0%   { transform: scale(1);    opacity: .55; }
        100% { transform: scale(1.6);  opacity: 0;   }
    }
    /* Always-visible name label (every pin gets one) */
    .biz-pin-label {
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translate(-50%, 2px);
        background: rgba(255, 255, 255, .96);
        color: #0B0B0C;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 6px;
        white-space: nowrap;
        max-width: 110px;
        overflow: hidden;
        text-overflow: ellipsis;
        box-shadow: 0 2px 6px -1px rgba(11, 11, 12, .25);
        pointer-events: none;
        backdrop-filter: blur(2px);
    }
    /* Promoted label: bigger, bolder, gold border */
    .biz-pin-label.is-promoted-label {
        font-size: 11px;
        font-weight: 800;
        padding: 3px 9px;
        max-width: 140px;
        border: 1.5px solid #FFB85C;
        background: #fff;
        box-shadow: 0 4px 12px -2px rgba(255, 159, 45, .35);
    }
    /* Event label: mint-tinted */
    .biz-pin-label.is-event-label {
        background: rgba(255, 255, 255, .96);
        color: #166534;
        border: 1px solid #1FA85740;
    }

    /* ── Event pin (different shape: square rounded, mint color) ── */
    .event-pin-wrap {
        width: 38px;
        height: 38px;
        position: relative;
        filter: drop-shadow(0 6px 10px rgba(0, 0, 0, .22));
    }
    .event-pin {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: linear-gradient(135deg, #1FA857, #10B981);
        border: 3px solid #fff;
        display: grid;
        place-items: center;
        position: relative;
    }
    .event-pin svg { width: 18px; height: 18px; color: #fff; }

    /* ── Compact, modern popup ── */
    .leaflet-popup-content-wrapper {
        border-radius: 16px;
        font-family: inherit;
        padding: 0;
        box-shadow: 0 12px 40px -8px rgba(11,11,12,.25);
        border: 1px solid rgba(11,11,12,.06);
        overflow: hidden;
    }
    .leaflet-popup-content {
        margin: 0;
        text-align: right;
        width: 240px !important;
    }
    .leaflet-popup-tip { box-shadow: 0 4px 12px rgba(11,11,12,.1); }
    .leaflet-popup-close-button {
        top: 6px !important; left: 6px !important; right: auto !important;
        font-size: 18px !important; color: #84848E !important;
    }
    .pop-card { padding: 12px 14px 12px; }
    .pop-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .pop-cat-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 999px;
        font-size: 10px; font-weight: 800;
        background: var(--cat-color-soft, #FFEDD5);
        color: var(--cat-color, #9A3412);
    }
    .pop-rate {
        display: inline-flex; align-items: center; gap: 2px;
        font-size: 12px; font-weight: 800; color: #FF7A4D;
    }
    .pop-name { font-size: 15px; font-weight: 800; color: #0B0B0C; margin-top: 6px; line-height: 1.25; }
    .pop-badges { display: flex; gap: 4px; margin-top: 6px; flex-wrap: wrap; }
    .pop-badge {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 2px 7px; border-radius: 999px;
        font-size: 9px; font-weight: 800;
    }
    .pop-badge.verified { background: #DCFCE7; color: #166534; }
    .pop-badge.menu { background: #FFEDD5; color: #9A3412; }
    .pop-actions { display: grid; grid-template-columns: 1fr auto; gap: 6px; margin-top: 10px; }
    .pop-btn {
        padding: 8px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        text-align: center;
        transition: opacity .15s, transform .15s;
        white-space: nowrap;
        display: inline-flex; align-items: center; justify-content: center; gap: 4px;
    }
    .pop-btn:active { transform: scale(.97); }
    .pop-btn.primary {
        background: linear-gradient(135deg, #FF7A4D, #FFB85C);
        color: #fff;
        box-shadow: 0 6px 16px -4px rgba(255, 122, 77, .55);
    }
    .pop-btn.call {
        background: #fff;
        color: #FF7A4D;
        border: 1.5px solid #FF7A4D;
        padding: 8px;
        width: 38px;
    }
    .pop-btn:hover { opacity: .92; }

    /* ── Filter chips active state ── */
    .map-cat-chip.is-active {
        color: #fff !important;
        border-color: transparent !important;
        background: var(--cat-color, #FF7A4D) !important;
    }

    /* ── "My location" floating button — sits well above bottom-nav ── */
    .map-locate-btn {
        position: absolute;
        top: 14px;
        inset-inline-start: 14px;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid rgba(11,11,12,.08);
        box-shadow: 0 8px 24px -6px rgba(11,11,12,.25);
        display: grid;
        place-items: center;
        z-index: 400;
        cursor: pointer;
        color: #FF7A4D;
        transition: transform .15s;
    }
    .map-locate-btn:hover { transform: scale(1.08); }
    .map-locate-btn:active { transform: scale(.95); }

    /* Hide default Leaflet zoom on mobile (gestures are enough) */
    @media (max-width: 640px) {
        .leaflet-control-zoom { display: none; }
    }

</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('directory.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950 inline-flex items-center gap-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-mint-700">
                <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                <line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/>
            </svg>
            خريطة بنها
        </h1>
        <span class="ms-auto text-xs font-bold text-ink-500" id="biz-count">…</span>
    </div>

    {{-- Category filter chips (with category SVG icon) --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-3">
        <div class="flex gap-2 px-4 w-max">
            <button type="button" data-cat=""
                    class="map-cat-chip is-active shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-extrabold bg-white border border-ink-950/8 transition"
                    style="--cat-color: #0B0B0C;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                    <circle cx="12" cy="12" r="10"/>
                </svg>
                الكل
            </button>
            <button type="button" data-cat="__events__"
                    class="map-cat-chip shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-white border border-ink-950/8 text-ink-700 transition"
                    style="--cat-color: #1FA857;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-mint-700">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                أحداث
            </button>
            @foreach($categories as $key => $cat)
                <button type="button" data-cat="{{ $key }}"
                        class="map-cat-chip shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-white border border-ink-950/8 text-ink-700 transition"
                        style="--cat-color: {{ $cat['color'] }};">
                    <span class="inline-flex" style="color: {{ $cat['color'] }}">
                        <x-icon :name="$cat['icon'] ?? 'bag'" class="w-3.5 h-3.5"/>
                    </span>
                    {{ $cat['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="relative">
        <div id="banha-map"></div>
        <button type="button" id="locate-me" class="map-locate-btn" aria-label="موقعي">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                <circle cx="12" cy="12" r="3"/>
                <line x1="12" y1="2"  x2="12" y2="5"/>
                <line x1="12" y1="19" x2="12" y2="22"/>
                <line x1="2"  y1="12" x2="5"  y2="12"/>
                <line x1="19" y1="12" x2="22" y2="12"/>
            </svg>
        </button>
    </div>

    <p class="text-[10px] text-ink-400 text-center mt-3">
        اضغط أي علامة عشان تشوف تفاصيل النشاط · مفيش بيانات بتتسجّل عنك
    </p>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(async function () {
    const BANHA = [30.4582, 31.1797];

    const map = L.map('banha-map', {
        center: BANHA,
        zoom: 13,
        scrollWheelZoom: true,
        zoomControl: window.matchMedia('(min-width: 641px)').matches,
        attributionControl: false,
    });

    // Tile layer with Arabic labels (OpenStreetMap default uses local language)
    // For Egypt this shows Arabic city/street names. Falls back to OSM mirror if main is slow.
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        crossOrigin: true,
        attribution: '© OpenStreetMap',
    }).addTo(map);

    // ── Icon SVG library (mirrors x-icon component) ──
    const SVG = {
        utensils:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>',
        stethoscope: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 2v2"/><path d="M5 2v2"/><path d="M5 3H4a2 2 0 0 0-2 2v4a6 6 0 0 0 12 0V5a2 2 0 0 0-2-2h-1"/><path d="M8 15a6 6 0 0 0 12 0v-3"/><circle cx="20" cy="10" r="2"/></svg>',
        cart:        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
        wrench:      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
        briefcase:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
        graduation:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
        car:         '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18 8h-7L7 11l-3 .9C3.5 12 3 12.4 3 13v3c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>',
        check:       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
        leaf:        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6"/></svg>',
        bolt:        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
        bag:         '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        flame:       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>',
    };

    function escapeName(s) {
        return String(s || '').replace(/[<>"']/g, c => ({'<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function makePin(b, meta) {
        const color = meta?.color || '#FF7A4D';
        const iconKey = meta?.icon || 'bag';
        const svg = SVG[iconKey] || SVG.bag;
        const classes = ['biz-pin'];
        if (b.is_verified) classes.push('is-verified');
        const nameSafe = escapeName(b.name);

        // Promoted: bigger, gold, with prominent name label
        if (b.is_promoted) {
            return L.divIcon({
                html:
                    '<div class="biz-pin-wrap is-promoted">' +
                    '  <div class="' + classes.join(' ') + '">' + svg + '</div>' +
                    '  <span class="biz-pin-label is-promoted-label">' + nameSafe + '</span>' +
                    '</div>',
                className: '',
                iconSize: [48, 80],
                iconAnchor: [24, 60],
                popupAnchor: [0, -56],
            });
        }

        // Regular: small label below
        return L.divIcon({
            html:
                '<div class="biz-pin-wrap">' +
                '  <div class="' + classes.join(' ') + '" style="background:' + color + ';">' + svg + '</div>' +
                '  <span class="biz-pin-label">' + nameSafe + '</span>' +
                '</div>',
            className: '',
            iconSize: [36, 64],
            iconAnchor: [18, 44],
            popupAnchor: [0, -40],
        });
    }

    function makeEventPin(e) {
        const calSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
        const titleSafe = escapeName(e?.title);
        return L.divIcon({
            html:
                '<div class="event-pin-wrap">' +
                '  <div class="event-pin">' + calSvg + '</div>' +
                '  <span class="biz-pin-label is-event-label">' + titleSafe + '</span>' +
                '</div>',
            className: '',
            iconSize: [38, 60],
            iconAnchor: [19, 38],
            popupAnchor: [0, -34],
        });
    }

    let currentCat = '';
    // No clustering — every pin shows individually with its name label
    const layerGroup = L.layerGroup().addTo(map);
    const cache = {};

    async function loadCategory(cat) {
        const key = cat || 'all';
        if (cache[key]) return cache[key];
        const url = '{{ route('directory.map.data') }}' + (cat ? '?category=' + encodeURIComponent(cat) : '');
        try {
            const res = await fetch(url, { credentials: 'same-origin' });
            const data = await res.json();
            cache[key] = data;
            return data;
        } catch (err) {
            return { businesses: [], categories: {} };
        }
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function buildPopup(b, meta) {
        const color = meta.color || '#FF7A4D';
        const badges = [];
        if (b.is_verified) badges.push('<span class="pop-badge verified">✓ موثّق</span>');
        if (b.has_menu)    badges.push('<span class="pop-badge menu">📋 منيو</span>');

        const rating = b.rating_avg > 0
            ? '<span class="pop-rate">' +
              '<svg viewBox="0 0 24 24" fill="currentColor" style="width:12px;height:12px;"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>' +
              Number(b.rating_avg).toFixed(1) +
              '</span>'
            : '';

        const showLink = '/directory/business/' + b.id;
        const phoneSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="width:16px;height:16px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
        const callBtn = b.phone
            ? '<a href="tel:' + escapeHtml(b.phone) + '" class="pop-btn call" aria-label="اتصل">' + phoneSvg + '</a>'
            : '';

        // Convert hex to rgba for soft background
        const softBg = color + '22'; // approximate translucent

        return ''
            + '<div class="pop-card" style="--cat-color:' + color + ';--cat-color-soft:' + softBg + ';">'
            + '  <div class="pop-row">'
            + '    <span class="pop-cat-pill">' + escapeHtml(meta.label || '') + '</span>'
            +      rating
            + '  </div>'
            + '  <div class="pop-name">' + escapeHtml(b.name) + '</div>'
            + (badges.length ? '  <div class="pop-badges">' + badges.join('') + '</div>' : '')
            + '  <div class="pop-actions">'
            + '    <a href="' + showLink + '" class="pop-btn primary">شوف التفاصيل</a>'
            +      callBtn
            + '  </div>'
            + '</div>';
    }

    function buildEventPopup(e) {
        const dt = e.starts_at ? new Date(e.starts_at) : null;
        const dtLabel = dt ? dt.toLocaleString('ar-EG', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '';
        return ''
            + '<div class="pop-card" style="--cat-color:#1FA857;--cat-color-soft:#DCFCE7;">'
            + '  <div class="pop-row">'
            + '    <span class="pop-cat-pill">حدث</span>'
            + (dtLabel ? '<span class="pop-rate" style="color:#1FA857">' + escapeHtml(dtLabel) + '</span>' : '')
            + '  </div>'
            + '  <div class="pop-name">' + escapeHtml(e.title) + '</div>'
            + (e.location ? '<div style="font-size:11px;color:#5C5C66;margin-top:4px;">📍 ' + escapeHtml(e.location) + '</div>' : '')
            + '  <div class="pop-actions">'
            + '    <a href="/events/' + e.id + '" class="pop-btn primary">شوف الحدث</a>'
            + '  </div>'
            + '</div>';
    }

    async function render(cat) {
        currentCat = cat;
        layerGroup.clearLayers();
        document.getElementById('biz-count').textContent = '…';

        const isEventsView = cat === '__events__';
        const data = await loadCategory(isEventsView ? '' : cat);
        const list  = Array.isArray(data.businesses) ? data.businesses : Object.values(data.businesses || {});
        const events = Array.isArray(data.events) ? data.events : Object.values(data.events || {});
        const cats  = data.categories || {};

        const bounds = [];

        if (isEventsView) {
            // Show ONLY events
            events.forEach((e) => {
                const lat = parseFloat(e.lat); const lng = parseFloat(e.lng);
                if (isNaN(lat) || isNaN(lng)) return;
                const m = L.marker([lat, lng], { icon: makeEventPin(e) }).bindPopup(buildEventPopup(e), { closeButton: true, offset: [0, 4] });
                layerGroup.addLayer(m); bounds.push([lat, lng]);
            });
            document.getElementById('biz-count').textContent = events.length + ' حدث';
        } else {
            // Businesses (with promoted on top — Leaflet renders later markers above earlier ones)
            const ordered = list.slice().sort((a, b) => (a.is_promoted ? 1 : 0) - (b.is_promoted ? 1 : 0));
            ordered.forEach((b) => {
                const meta = cats[b.category] || {};
                const lat = parseFloat(b.lat); const lng = parseFloat(b.lng);
                if (isNaN(lat) || isNaN(lng)) return;
                const m = L.marker([lat, lng], { icon: makePin(b, meta), zIndexOffset: b.is_promoted ? 1000 : 0 })
                    .bindPopup(buildPopup(b, meta), { closeButton: true, offset: [0, 4] });
                layerGroup.addLayer(m);
                bounds.push([lat, lng]);
            });

            // ALWAYS overlay events on top of businesses (so users see what's happening today)
            if (! cat) {
                events.forEach((e) => {
                    const lat = parseFloat(e.lat); const lng = parseFloat(e.lng);
                    if (isNaN(lat) || isNaN(lng)) return;
                    const m = L.marker([lat, lng], { icon: makeEventPin(e), zIndexOffset: 500 }).bindPopup(buildEventPopup(e), { closeButton: true, offset: [0, 4] });
                    layerGroup.addLayer(m);
                });
            }

            document.getElementById('biz-count').textContent = list.length + ' نشاط' + (events.length && ! cat ? ' · ' + events.length + ' حدث' : '');
        }

        if (bounds.length > 1 && cat !== currentCat) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
        }
    }

    // Wire filter chips
    document.querySelectorAll('[data-cat]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-cat]').forEach(x => x.classList.remove('is-active'));
            btn.classList.add('is-active');
            render(btn.dataset.cat || '');
        });
    });

    // "My location" button (only fires on click — no silent tracking)
    let userMarker = null;
    document.getElementById('locate-me').addEventListener('click', () => {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const c = [pos.coords.latitude, pos.coords.longitude];
                if (userMarker) map.removeLayer(userMarker);
                userMarker = L.circleMarker(c, {
                    radius: 9, color: '#fff', weight: 3,
                    fillColor: '#1D9BF0', fillOpacity: 1,
                }).addTo(map);
                map.setView(c, 15, { animate: true });
            },
            () => {},
            { enableHighAccuracy: false, timeout: 8000, maximumAge: 60000 }
        );
    });

    render('');
})();
</script>
@endpush
@endsection
