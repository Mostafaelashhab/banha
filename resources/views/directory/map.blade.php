@extends('layouts.app', [
    'title'       => 'خريطة بنها · بنهاوي',
    'description' => 'كل النشاطات في بنها على خريطة واحدة — مطاعم، صنايعية، دكاترة، حكومة، مواصلات، طوارئ.',
])

@push('head')
{{-- Preconnect: cuts TLS+DNS handshake time on mobile networks --}}
<link rel="preconnect" href="https://unpkg.com" crossorigin>
<link rel="preconnect" href="https://basemaps.cartocdn.com" crossorigin>
<link rel="dns-prefetch" href="https://a.basemaps.cartocdn.com">
<link rel="dns-prefetch" href="https://b.basemaps.cartocdn.com">
<link rel="dns-prefetch" href="https://c.basemaps.cartocdn.com">
<link rel="dns-prefetch" href="https://d.basemaps.cartocdn.com">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" crossorigin="">
{{-- Start fetching Leaflet JS immediately while CSS + page parse --}}
<link rel="preload" as="script" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin>
<link rel="preload" as="script" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js" crossorigin>
{{-- Start fetching map data in parallel with Leaflet — saves a roundtrip --}}
<link rel="preload" as="fetch" href="{{ route('directory.map.data') }}" crossorigin="use-credentials">
<style>
    #banha-map {
        height: calc(100vh - 130px);
        min-height: 560px;
        border-radius: 28px;
        z-index: 0;
        background: #EEF2FA;
        box-shadow: 0 12px 40px -10px rgba(11,11,12,.18);
    }

    /* ── Skeleton: faux-map texture + loader, fades out when ready ── */
    .map-skeleton {
        position: absolute;
        inset: 0;
        border-radius: 28px;
        overflow: hidden;
        z-index: 350;
        pointer-events: none;
        transition: opacity .45s ease;
        background-color: #EEF2FA;
        background-image:
            radial-gradient(circle at 28% 38%, rgba(45,91,255,.10) 0, transparent 18%),
            radial-gradient(circle at 72% 60%, rgba(45,91,255,.08) 0, transparent 22%),
            radial-gradient(circle at 50% 50%, rgba(255,255,255,.5) 0, transparent 45%),
            linear-gradient(110deg, transparent 44%, rgba(11,11,12,.05) 46%, transparent 49%),
            linear-gradient(20deg,  transparent 44%, rgba(11,11,12,.05) 46%, transparent 49%),
            linear-gradient(70deg,  transparent 44%, rgba(11,11,12,.05) 46%, transparent 49%);
        background-size: 100% 100%, 100% 100%, 100% 100%, 240px 240px, 320px 320px, 200px 200px;
    }
    .map-skeleton::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(110deg, transparent 35%, rgba(255,255,255,.55) 50%, transparent 65%);
        animation: map-shimmer 1.8s ease-in-out infinite;
    }
    .map-skeleton .ghost-pin {
        position: absolute;
        width: 16px; height: 20px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        background: rgba(45,91,255,.18);
        box-shadow: 0 3px 6px -1px rgba(45,91,255,.15);
        animation: map-pin-pulse 2s ease-in-out infinite;
    }
    .map-skeleton .ghost-pin:nth-child(1) { top: 32%; left: 38%; animation-delay: .0s; }
    .map-skeleton .ghost-pin:nth-child(2) { top: 48%; left: 56%; animation-delay: .3s; }
    .map-skeleton .ghost-pin:nth-child(3) { top: 62%; left: 30%; animation-delay: .6s; }
    .map-skeleton .ghost-pin:nth-child(4) { top: 40%; left: 70%; animation-delay: .9s; }

    .map-skeleton .map-skeleton-loader {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 18px;
        border-radius: 999px;
        background: #fff;
        box-shadow: 0 10px 28px -8px rgba(11,11,12,.18);
        color: #2D5BFF;
        font-weight: 800;
        font-size: 13px;
        white-space: nowrap;
    }
    .map-skeleton .map-skeleton-loader::before {
        content: '';
        width: 14px; height: 14px;
        border-radius: 999px;
        border: 2.5px solid currentColor;
        border-inline-end-color: transparent;
        animation: map-spin .75s linear infinite;
    }
    .map-skeleton.is-hidden { opacity: 0; }

    @keyframes map-shimmer {
        0%   { transform: translateX(-30%); }
        100% { transform: translateX(30%);  }
    }
    @keyframes map-pin-pulse {
        0%, 100% { opacity: .55; transform: rotate(-45deg) scale(1); }
        50%      { opacity: .95; transform: rotate(-45deg) scale(1.15); }
    }
    @keyframes map-spin { to { transform: rotate(360deg); } }
    /* CARTO Positron is already minimal — just a hair of blue brand-warmth so
       the map feels part of the app, not a generic third-party widget. */
    #banha-map .leaflet-tile {
        filter: saturate(.92) brightness(1.02) hue-rotate(-4deg);
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
        background: #2D5BFF !important;
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
    /* Hide labels when zoomed out — too many overlap and tank mobile FPS.
       Show only when user zooms in (>=15). Promoted labels stay visible. */
    #banha-map.zoomed-out .biz-pin-label:not(.is-promoted-label) { display: none; }

    /* Cluster icon (only fires on overlapping pins thanks to maxClusterRadius=22) */
    .biz-cluster {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: #2D5BFF;
        color: #fff;
        font-weight: 900;
        font-size: 13px;
        display: grid;
        place-items: center;
        box-shadow: 0 6px 14px -2px rgba(255, 122, 77, .55);
        border: 3px solid #fff;
        cursor: pointer;
        transition: transform .15s;
    }
    .biz-cluster:hover { transform: scale(1.08); }
    .leaflet-cluster-anim .leaflet-marker-icon, .leaflet-cluster-anim .leaflet-marker-shadow {
        transition: transform .25s ease, opacity .25s ease;
    }
    /* Promoted label: bigger, bolder, gold border */
    .biz-pin-label.is-promoted-label {
        font-size: 11px;
        font-weight: 800;
        padding: 3px 9px;
        max-width: 140px;
        border: 1.5px solid #FFD440;
        background: #fff;
        box-shadow: 0 4px 12px -2px rgba(255, 159, 45, .35);
    }
    /* Event label: mint-tinted */
    .biz-pin-label.is-event-label {
        background: rgba(255, 255, 255, .96);
        color: #166534;
        border: 1px solid #1FA85740;
    }

    /* ── Listing pin (rotated rounded square + tag color per kind) ── */
    .listing-pin-wrap {
        width: 38px;
        height: 38px;
        position: relative;
        filter: drop-shadow(0 6px 10px rgba(0, 0, 0, .22));
    }
    .listing-pin {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        transform: rotate(45deg);
        border: 3px solid #fff;
        display: grid;
        place-items: center;
        background: #2D5BFF;
    }
    .listing-pin svg {
        width: 16px; height: 16px; color: #fff;
        transform: rotate(-45deg);
    }
    .listing-pin.is-featured {
        box-shadow: 0 0 0 3px rgba(255, 184, 92, .45);
    }
    .biz-pin-label.is-listing-label {
        color: #1736B0;
        background: #fff;
        border: 1px solid #FFD3B8;
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
        background: #1FA857;
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
        background: var(--cat-color-soft, #DCE4FF);
        color: var(--cat-color, #1736B0);
    }
    .pop-rate {
        display: inline-flex; align-items: center; gap: 2px;
        font-size: 12px; font-weight: 800; color: #2D5BFF;
    }
    .pop-name { font-size: 15px; font-weight: 800; color: #0B0B0C; margin-top: 6px; line-height: 1.25; }
    .pop-badges { display: flex; gap: 4px; margin-top: 6px; flex-wrap: wrap; }
    .pop-badge {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 2px 7px; border-radius: 999px;
        font-size: 9px; font-weight: 800;
    }
    .pop-badge.verified { background: #DCFCE7; color: #166534; }
    .pop-badge.menu { background: #DCE4FF; color: #1736B0; }
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
        background: #2D5BFF;
        color: #fff;
        box-shadow: 0 6px 16px -4px rgba(255, 122, 77, .55);
    }
    .pop-btn.call {
        background: #fff;
        color: #2D5BFF;
        border: 1.5px solid #2D5BFF;
        padding: 8px;
        width: 38px;
    }
    .pop-btn:hover { opacity: .92; }

    /* ── Filter chips active state ── */
    .map-cat-chip.is-active {
        color: #fff !important;
        border-color: transparent !important;
        background: var(--cat-color, #2D5BFF) !important;
    }
    .map-filter-chip.is-active {
        color: #fff !important;
        border-color: transparent !important;
        background: #2D5BFF !important;
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
        color: #2D5BFF;
        transition: transform .15s;
    }
    .map-locate-btn:hover { transform: scale(1.08); }
    .map-locate-btn:active { transform: scale(.95); }

    /* Hide default Leaflet zoom on mobile (gestures are enough) */
    @media (max-width: 640px) {
        .leaflet-control-zoom { display: none; }
    }

    /* ── User location avatar marker — feels like "your character" on the map ── */
    .user-loc-icon { background: transparent !important; border: 0 !important; }
    .user-loc-wrap {
        position: relative;
        width: 44px; height: 52px;
        pointer-events: none;
    }
    .user-loc-pulse {
        position: absolute;
        left: 50%; top: 18px;
        width: 36px; height: 36px;
        transform: translate(-50%, -50%);
        border-radius: 50%;
        background: rgba(45, 91, 255, .35);
        animation: user-loc-pulse 2.2s ease-out infinite;
    }
    .user-loc-avatar {
        position: absolute;
        left: 50%; top: 0;
        transform: translateX(-50%);
        width: 36px; height: 36px;
        border-radius: 50%;
        background: #2D5BFF;
        border: 3px solid #fff;
        box-shadow: 0 4px 12px -2px rgba(45, 91, 255, .55);
        display: grid;
        place-items: center;
        color: #fff;
        font-weight: 900;
        font-size: 14px;
        overflow: hidden;
    }
    .user-loc-avatar img {
        width: 100%; height: 100%;
        object-fit: cover;
    }
    .user-loc-tail {
        position: absolute;
        left: 50%; top: 32px;
        transform: translateX(-50%);
        width: 0; height: 0;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
        border-top: 10px solid #fff;
        filter: drop-shadow(0 3px 3px rgba(45, 91, 255, .35));
    }
    .user-loc-tail::after {
        content: '';
        position: absolute;
        left: -5px; top: -10px;
        width: 0; height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 7px solid #2D5BFF;
    }
    @keyframes user-loc-pulse {
        0%   { transform: translate(-50%, -50%) scale(.6); opacity: .8; }
        100% { transform: translate(-50%, -50%) scale(2.4); opacity: 0; }
    }

    /* Spinner state on locate button */
    .map-locate-btn.is-loading { pointer-events: none; opacity: .7; }
    .map-locate-btn.is-loading svg { animation: map-spin .8s linear infinite; }

    /* Toast (used for geolocation errors / low-accuracy warnings) */
    #map-toast {
        position: fixed;
        bottom: 96px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(11,11,12,.92);
        color: #fff;
        padding: 10px 18px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 800;
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity .3s ease, transform .3s ease;
        max-width: 88vw;
        text-align: center;
    }
    #map-toast.show { opacity: 1; transform: translateX(-50%) translateY(-4px); }

</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-2 mb-3">
       
        <h1 class="text-xl font-extrabold text-ink-950 inline-flex items-center gap-2">
            
            خريطة بنها
        </h1>
        <span class="ms-auto text-xs font-bold text-ink-500" id="biz-count">…</span>
    </div>

    {{-- Compact summary bar: category pill + filters button --}}
    <div class="flex items-center gap-2 mb-3">
        <button type="button" id="map-filter-btn"
                class="flex-1 inline-flex items-center gap-2 px-3.5 py-2.5 rounded-full bg-white ring-1 ring-ink-950/8 text-ink-950 transition hover:ring-coral-500/40">
            <span class="w-7 h-7 rounded-full bg-coral-50 text-coral-600 grid place-items-center shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                    <line x1="4" y1="6" x2="20" y2="6"/>
                    <line x1="7" y1="12" x2="17" y2="12"/>
                    <line x1="10" y1="18" x2="14" y2="18"/>
                </svg>
            </span>
            <span class="flex-1 text-start min-w-0">
                <span id="map-filter-summary" class="block text-sm font-extrabold truncate">الكل</span>
                <span id="map-filter-subtitle" class="block text-[10px] text-ink-400 truncate">اضغط لتغيير الفلتر</span>
            </span>
            <span id="map-filter-count" class="hidden min-w-5 h-5 px-1.5 rounded-full bg-coral-500 text-white text-[10px] font-black grid place-items-center">0</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-ink-400">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </button>
    </div>

    <div class="relative">
        <div id="banha-map"></div>
        <div class="map-skeleton" id="map-skeleton" aria-hidden="true">
            <span class="ghost-pin"></span>
            <span class="ghost-pin"></span>
            <span class="ghost-pin"></span>
            <span class="ghost-pin"></span>
            <span class="map-skeleton-loader">جاري تحميل الخريطة...</span>
        </div>
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

{{-- ─── Map filter bottom sheet ───────────────────────────────── --}}
<div id="map-filter-sheet" class="modal-wrap" role="dialog" aria-modal="true" aria-labelledby="map-filter-title">
    <div class="modal-backdrop" data-close></div>
    <div class="modal-sheet">
        <div class="px-5 pt-3 pb-6 max-h-[82vh] overflow-y-auto">
            <div class="modal-drag-handle" data-drag-handle>
                <span class="modal-drag-bar"></span>
            </div>

            <div class="flex items-center justify-between mb-4">
                <h3 id="map-filter-title" class="text-lg font-black text-ink-950 inline-flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-coral-50 text-coral-600 grid place-items-center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-3.5 h-3.5">
                            <line x1="4" y1="6" x2="20" y2="6"/>
                            <line x1="7" y1="12" x2="17" y2="12"/>
                            <line x1="10" y1="18" x2="14" y2="18"/>
                        </svg>
                    </span>
                    فلتر الخريطة
                </h3>
                <button type="button" data-close
                        class="w-9 h-9 rounded-full grid place-items-center text-ink-500 hover:bg-ink-950/5 transition"
                        aria-label="إغلاق">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" class="w-5 h-5">
                        <line x1="18" y1="6" x2="6"  y2="18"/>
                        <line x1="6"  y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            {{-- Category section --}}
            <h4 class="text-xs font-extrabold text-ink-500 uppercase tracking-wider mb-3">اعرض</h4>
            <div class="flex flex-wrap gap-2 mb-6">
                <button type="button" data-cat=""
                        class="map-cat-chip is-active inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-extrabold bg-white ring-1 ring-ink-950/8 transition"
                        style="--cat-color: #0B0B0C;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                        <circle cx="12" cy="12" r="10"/>
                    </svg>
                    الكل
                </button>
                <button type="button" data-cat="__events__"
                        class="map-cat-chip inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold bg-white ring-1 ring-ink-950/8 text-ink-700 transition"
                        style="--cat-color: #1FA857;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-mint-700">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    أحداث
                </button>
                <button type="button" data-cat="__listings__"
                        class="map-cat-chip inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold bg-white ring-1 ring-ink-950/8 text-ink-700 transition"
                        style="--cat-color: #2D5BFF;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5" style="color:#2D5BFF">
                        <path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                    إعلانات
                </button>
                @foreach($categories as $key => $cat)
                    <button type="button" data-cat="{{ $key }}"
                            class="map-cat-chip inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold bg-white ring-1 ring-ink-950/8 text-ink-700 transition"
                            style="--cat-color: {{ $cat['color'] }};">
                        <span class="inline-flex" style="color: {{ $cat['color'] }}">
                            <x-icon :name="$cat['icon'] ?? 'bag'" class="w-3.5 h-3.5"/>
                        </span>
                        {{ $cat['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Boolean filter section --}}
            <h4 class="text-xs font-extrabold text-ink-500 uppercase tracking-wider mb-3">حالة</h4>
            <div class="flex flex-wrap gap-2 mb-6">
                <button type="button" data-mfilter="open_now"
                        class="map-filter-chip inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-extrabold bg-white ring-1 ring-ink-950/8 text-ink-700 transition">
                    <span class="w-1.5 h-1.5 rounded-full bg-mint-500"></span>
                    مفتوح دلوقتي
                </button>
                <button type="button" data-mfilter="verified"
                        class="map-filter-chip inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-extrabold bg-white ring-1 ring-ink-950/8 text-ink-700 transition">
                    <x-icon name="check" class="w-3 h-3"/>
                    موثّق
                </button>
                <button type="button" data-mfilter="open24"
                        class="map-filter-chip inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-extrabold bg-white ring-1 ring-ink-950/8 text-ink-700 transition">
                    ٢٤ ساعة
                </button>
                <button type="button" data-mfilter="has_menu"
                        class="map-filter-chip inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-extrabold bg-white ring-1 ring-ink-950/8 text-ink-700 transition">
                    عنده منيو
                </button>
            </div>

            {{-- Footer actions --}}
            <div class="flex items-center gap-2 pt-2 border-t border-ink-950/6">
                <button type="button" id="map-filter-reset"
                        class="flex-1 py-2.5 rounded-full bg-white ring-1 ring-ink-950/8 text-ink-700 text-sm font-extrabold hover:bg-ink-950/5 transition">
                    مسح الكل
                </button>
                <button type="button" data-close
                        class="flex-1 btn-primary !py-2.5 text-sm">
                    عرض النتائج
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js" crossorigin=""></script>
<script>
(async function () {
    const BANHA = [30.4582, 31.1797];

    @php
        $authUser = null;
        if (Auth::check()) {
            $u = Auth::user();
            $authUser = [
                'initial' => \App\Support\AnonSeed::initial($u->username),
                'color'   => \App\Support\AnonSeed::avatarColor($u->username),
                'photo'   => $u->avatar_url ?? null,
            ];
        }
    @endphp
    // Auth user → render them as a "character" on the map (avatar + tail).
    // Falls back to plain dot for guests.
    const AUTH_USER = {!! json_encode($authUser) !!};

    const map = L.map('banha-map', {
        center: BANHA,
        zoom: 13,
        scrollWheelZoom: true,
        zoomControl: window.matchMedia('(min-width: 641px)').matches,
        attributionControl: false,
        preferCanvas: true,
    });

    // CARTO Positron — minimal light-gray basemap (Uber/Airbnb aesthetic).
    // Free, no API key, 4 subdomains for parallel tile fetches.
    // {r} resolves to '@2x' on retina screens.
    const tileLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        subdomains: 'abcd',
        maxZoom: 19,
        crossOrigin: true,
        attribution: '© OpenStreetMap · © CARTO',
        keepBuffer: 4,
        updateWhenIdle: true,
        updateWhenZooming: false,
    });
    tileLayer.addTo(map);

    // ── Skeleton fade-out when map is ready ────────────────
    // Hide once first tile set has loaded AND first render() finished.
    // Safety: hide after 6s no matter what (slow network / offline).
    const skeletonEl = document.getElementById('map-skeleton');
    let tilesReady = false;
    let dataReady = false;
    function maybeHideSkeleton() {
        if (! skeletonEl) return;
        if (tilesReady && dataReady) {
            skeletonEl.classList.add('is-hidden');
            setTimeout(() => skeletonEl.remove(), 500);
        }
    }
    tileLayer.once('load', () => { tilesReady = true; maybeHideSkeleton(); });
    setTimeout(() => {
        if (skeletonEl && skeletonEl.isConnected) {
            tilesReady = true; dataReady = true; maybeHideSkeleton();
        }
    }, 6000);

    // Toggle a class on the map container so CSS can hide labels at low zoom.
    // 500 overlapping labels at zoom 13 kill mobile FPS — only show when zoomed in.
    const mapEl = document.getElementById('banha-map');
    function syncZoomClass() {
        mapEl.classList.toggle('zoomed-out', map.getZoom() < 15);
    }
    map.on('zoomend', syncZoomClass);
    syncZoomClass();

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
        const color = meta?.color || '#2D5BFF';
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

    // Marketplace listing pin — rotated rounded-square + kind color tint
    const KIND_COLORS = {
        sale:  ['#2D5BFF', '#FFD440'],
        buy:   ['#1FA857', '#10B981'],
        lost:  ['#E64646', '#F87171'],
        found: ['#FFD440', '#F59E0B'],
    };
    function makeListingPin(l) {
        const tagSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>';
        const titleSafe = escapeName(l?.title);
        const [c1, c2] = KIND_COLORS[l.kind] || KIND_COLORS.sale;
        const cls = ['listing-pin'];
        if (l.is_featured) cls.push('is-featured');
        return L.divIcon({
            html:
                '<div class="listing-pin-wrap">' +
                '  <div class="' + cls.join(' ') + '" style="background:linear-gradient(135deg,' + c1 + ',' + c2 + ');">' + tagSvg + '</div>' +
                '  <span class="biz-pin-label is-listing-label">' + titleSafe + '</span>' +
                '</div>',
            className: '',
            iconSize: [38, 60],
            iconAnchor: [19, 38],
            popupAnchor: [0, -34],
        });
    }

    let currentCat = '';
    // Tight clustering: only group pins that are literally on top of each other.
    // maxClusterRadius: 22px → roughly "overlapping pin width". Bigger pins won't cluster.
    // disableClusteringAtZoom: 17 → at street level (zoom 17+) every pin shows alone.
    const layerGroup = L.markerClusterGroup({
        maxClusterRadius: 22,
        disableClusteringAtZoom: 17,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        animate: true,
        iconCreateFunction(cluster) {
            const count = cluster.getChildCount();
            return L.divIcon({
                html: '<div class="biz-cluster"><span>' + count + '</span></div>',
                className: '',
                iconSize: [38, 38],
                iconAnchor: [19, 19],
            });
        },
    }).addTo(map);
    const cache = {};
    const activeFilters = new Set();   // 'open_now' | 'verified' | 'open24' | 'has_menu'

    async function loadCategory(cat) {
        // Cache key includes both category AND active filters
        const filterTag = [...activeFilters].sort().join(',') || 'none';
        const key = (cat || 'all') + '|' + filterTag;
        if (cache[key]) return cache[key];

        const params = new URLSearchParams();
        if (cat) params.set('category', cat);
        activeFilters.forEach(f => params.set(f, '1'));
        const url = '{{ route('directory.map.data') }}' + (params.toString() ? '?' + params : '');

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
        const color = meta.color || '#2D5BFF';
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

    function buildListingPopup(l, kinds) {
        const meta = (kinds && kinds[l.kind]) || { label: '' };
        const [c1] = KIND_COLORS[l.kind] || KIND_COLORS.sale;
        const priceLabel = (l.kind === 'sale' || l.kind === 'buy')
            ? (l.price ? Number(l.price).toLocaleString('ar-EG') + ' ج' : 'بسعر مفاوض')
            : '';
        const photo = l.photo_url
            ? '<div style="margin:-12px -14px 10px;height:120px;background:#F5F0EA url(' + escapeHtml(l.photo_url) + ') center/cover no-repeat;"></div>'
            : '';
        return ''
            + '<div class="pop-card" style="--cat-color:' + c1 + ';--cat-color-soft:' + c1 + '22;">'
            +      photo
            + '  <div class="pop-row">'
            + '    <span class="pop-cat-pill">' + escapeHtml(meta.label || 'إعلان') + '</span>'
            + (priceLabel ? '<span class="pop-rate" style="color:' + c1 + '">' + escapeHtml(priceLabel) + '</span>' : '')
            + '  </div>'
            + '  <div class="pop-name">' + escapeHtml(l.title) + '</div>'
            + (l.zone ? '<div style="font-size:11px;color:#5C5C66;margin-top:4px;">📍 ' + escapeHtml(l.zone) + '</div>' : '')
            + '  <div class="pop-actions">'
            + '    <a href="/market/' + l.id + '" class="pop-btn primary">شوف الإعلان</a>'
            + '  </div>'
            + '</div>';
    }

    // Add markers in chunks. markercluster's addLayers() is much faster than
    // addLayer() in a loop — batch them so the main thread doesn't freeze.
    let renderToken = 0;
    function addMarkersChunked(makers, token) {
        const CHUNK = 100;
        let i = 0;
        function step() {
            if (token !== renderToken) return; // a new render() call superseded us
            const end = Math.min(i + CHUNK, makers.length);
            const batch = [];
            for (; i < end; i++) {
                const m = makers[i]();
                if (m) batch.push(m);
            }
            if (batch.length) {
                if (typeof layerGroup.addLayers === 'function') layerGroup.addLayers(batch);
                else batch.forEach(m => layerGroup.addLayer(m));
            }
            if (i < makers.length) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    async function render(cat) {
        const myToken = ++renderToken;
        currentCat = cat;
        layerGroup.clearLayers();
        document.getElementById('biz-count').textContent = '…';

        const isEventsView   = cat === '__events__';
        const isListingsView = cat === '__listings__';
        const isOverlay = isEventsView || isListingsView;
        const data = await loadCategory(isOverlay ? '' : cat);
        if (myToken !== renderToken) return; // user clicked another category
        const list     = Array.isArray(data.businesses) ? data.businesses : Object.values(data.businesses || {});
        const events   = Array.isArray(data.events) ? data.events : Object.values(data.events || {});
        const listings = Array.isArray(data.listings) ? data.listings : Object.values(data.listings || {});
        const cats     = data.categories || {};
        const kinds    = data.kinds || {};

        const bounds = [];
        const makers = [];

        if (isEventsView) {
            events.forEach((e) => {
                const lat = parseFloat(e.lat); const lng = parseFloat(e.lng);
                if (isNaN(lat) || isNaN(lng)) return;
                bounds.push([lat, lng]);
                makers.push(() => L.marker([lat, lng], { icon: makeEventPin(e) }).bindPopup(buildEventPopup(e), { closeButton: true, offset: [0, 4] }));
            });
            document.getElementById('biz-count').textContent = events.length + ' حدث';
        } else if (isListingsView) {
            listings.forEach((l) => {
                const lat = parseFloat(l.lat); const lng = parseFloat(l.lng);
                if (isNaN(lat) || isNaN(lng)) return;
                bounds.push([lat, lng]);
                makers.push(() => L.marker([lat, lng], { icon: makeListingPin(l), zIndexOffset: l.is_featured ? 800 : 200 })
                    .bindPopup(buildListingPopup(l, kinds), { closeButton: true, offset: [0, 4] }));
            });
            document.getElementById('biz-count').textContent = listings.length + ' إعلان';
        } else {
            // Businesses — promoted last so they paint on top
            const ordered = list.slice().sort((a, b) => (a.is_promoted ? 1 : 0) - (b.is_promoted ? 1 : 0));
            ordered.forEach((b) => {
                const meta = cats[b.category] || {};
                const lat = parseFloat(b.lat); const lng = parseFloat(b.lng);
                if (isNaN(lat) || isNaN(lng)) return;
                bounds.push([lat, lng]);
                makers.push(() => L.marker([lat, lng], { icon: makePin(b, meta), zIndexOffset: b.is_promoted ? 1000 : 0 })
                    .bindPopup(buildPopup(b, meta), { closeButton: true, offset: [0, 4] }));
            });

            if (! cat) {
                events.forEach((e) => {
                    const lat = parseFloat(e.lat); const lng = parseFloat(e.lng);
                    if (isNaN(lat) || isNaN(lng)) return;
                    makers.push(() => L.marker([lat, lng], { icon: makeEventPin(e), zIndexOffset: 500 }).bindPopup(buildEventPopup(e), { closeButton: true, offset: [0, 4] }));
                });
                listings.forEach((l) => {
                    const lat = parseFloat(l.lat); const lng = parseFloat(l.lng);
                    if (isNaN(lat) || isNaN(lng)) return;
                    makers.push(() => L.marker([lat, lng], { icon: makeListingPin(l), zIndexOffset: l.is_featured ? 800 : 200 })
                        .bindPopup(buildListingPopup(l, kinds), { closeButton: true, offset: [0, 4] }));
                });
            }

            const extras = [];
            if (events.length && ! cat)   extras.push(events.length + ' حدث');
            if (listings.length && ! cat) extras.push(listings.length + ' إعلان');
            document.getElementById('biz-count').textContent = list.length + ' نشاط' + (extras.length ? ' · ' + extras.join(' · ') : '');
        }

        // Fit bounds first (so paint focuses on the right area), then add markers chunked
        if (bounds.length > 1 && cat !== currentCat) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
        }
        addMarkersChunked(makers, myToken);
    }

    // ── Filter summary bar (reflects active selection) ──────
    const summaryEl  = document.getElementById('map-filter-summary');
    const subtitleEl = document.getElementById('map-filter-subtitle');
    const countEl    = document.getElementById('map-filter-count');

    const FILTER_LABELS = {
        open_now: 'مفتوح',
        verified: 'موثّق',
        open24:   '٢٤ ساعة',
        has_menu: 'منيو',
    };

    function updateSummary() {
        const activeCat = document.querySelector('[data-cat].is-active');
        const catLabel  = activeCat ? activeCat.textContent.trim() : 'الكل';
        if (summaryEl) summaryEl.textContent = catLabel;

        const fNames = [...activeFilters].map(k => FILTER_LABELS[k] || k);
        if (subtitleEl) {
            subtitleEl.textContent = fNames.length
                ? fNames.join(' · ')
                : 'اضغط لتغيير الفلتر';
        }
        if (countEl) {
            const total = fNames.length + (activeCat && activeCat.dataset.cat ? 1 : 0);
            countEl.textContent = total;
            countEl.classList.toggle('hidden', total === 0);
        }
    }

    // Wire category chips
    document.querySelectorAll('[data-cat]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-cat]').forEach(x => x.classList.remove('is-active'));
            btn.classList.add('is-active');
            render(btn.dataset.cat || '');
            updateSummary();
        });
    });

    // Wire boolean filter chips (open-now / verified / 24h / has_menu)
    document.querySelectorAll('[data-mfilter]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const key = btn.dataset.mfilter;
            const wasActive = activeFilters.has(key);
            wasActive ? activeFilters.delete(key) : activeFilters.add(key);
            btn.classList.toggle('is-active', !wasActive);
            render(currentCat);
            updateSummary();
        });
    });

    // ── Bottom sheet open/close + drag-to-dismiss ──────────
    const filterBtn   = document.getElementById('map-filter-btn');
    const filterSheet = document.getElementById('map-filter-sheet');
    if (filterBtn && filterSheet) {
        const sheetEl = filterSheet.querySelector('.modal-sheet');
        const handle  = filterSheet.querySelector('[data-drag-handle]');

        const openSheet  = () => { filterSheet.classList.add('open'); document.body.style.overflow = 'hidden'; };
        const closeSheet = () => {
            filterSheet.classList.remove('open');
            document.body.style.overflow = '';
            if (sheetEl) sheetEl.style.transform = '';
        };

        filterBtn.addEventListener('click', openSheet);
        filterSheet.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', closeSheet));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && filterSheet.classList.contains('open')) closeSheet();
        });

        // Reset button
        const resetBtn = document.getElementById('map-filter-reset');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                activeFilters.clear();
                document.querySelectorAll('[data-mfilter]').forEach(x => x.classList.remove('is-active'));
                document.querySelectorAll('[data-cat]').forEach(x => x.classList.remove('is-active'));
                const allBtn = document.querySelector('[data-cat=""]');
                if (allBtn) allBtn.classList.add('is-active');
                render('');
                updateSummary();
            });
        }

        if (handle && sheetEl) {
            let startY = 0, dy = 0, startT = 0, dragging = false;
            const onDown = (e) => {
                if (! filterSheet.classList.contains('open')) return;
                dragging = true;
                startY = e.clientY ?? (e.touches && e.touches[0].clientY) ?? 0;
                startT = performance.now();
                dy = 0;
                sheetEl.classList.add('is-dragging');
                handle.setPointerCapture?.(e.pointerId);
            };
            const onMove = (e) => {
                if (! dragging) return;
                const y = e.clientY ?? (e.touches && e.touches[0].clientY) ?? 0;
                dy = Math.max(0, y - startY);
                sheetEl.style.transform = `translateY(${dy}px)`;
            };
            const onUp = () => {
                if (! dragging) return;
                dragging = false;
                sheetEl.classList.remove('is-dragging');
                const elapsed  = performance.now() - startT;
                const velocity = dy / Math.max(elapsed, 1);
                const sheetH   = sheetEl.offsetHeight || 400;
                if (velocity > 0.6 || dy > sheetH * 0.30) {
                    sheetEl.style.transform = `translateY(${sheetH + 40}px)`;
                    setTimeout(closeSheet, 180);
                } else {
                    sheetEl.style.transform = '';
                }
            };
            handle.addEventListener('pointerdown', onDown);
            handle.addEventListener('pointermove', onMove);
            handle.addEventListener('pointerup',   onUp);
            handle.addEventListener('pointercancel', onUp);
        }
    }

    updateSummary();

    // ── "My location" — GPS-precise + avatar character on the map ─────
    let userMarker = null;
    let accuracyCircle = null;
    let isLocating = false;
    const locateBtn = document.getElementById('locate-me');
    const ORIGINAL_LOCATE_HTML = locateBtn.innerHTML;
    const SPINNER_HTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-5 h-5"><path d="M21 12a9 9 0 0 1-9 9"/><path d="M3 12a9 9 0 0 1 9-9"/></svg>';

    function toast(msg) {
        let t = document.getElementById('map-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'map-toast';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(t._timer);
        t._timer = setTimeout(() => t.classList.remove('show'), 3800);
    }

    function buildUserIcon() {
        let inner;
        if (AUTH_USER) {
            const bg = AUTH_USER.color || '#2D5BFF';
            inner = AUTH_USER.photo
                ? `<div class="user-loc-avatar"><img src="${AUTH_USER.photo}" alt=""></div>`
                : `<div class="user-loc-avatar" style="background:${bg}">${AUTH_USER.initial}</div>`;
        } else {
            inner = `<div class="user-loc-avatar"></div>`;
        }
        return L.divIcon({
            html: `<div class="user-loc-wrap"><span class="user-loc-pulse"></span>${inner}<span class="user-loc-tail"></span></div>`,
            className: 'user-loc-icon',
            iconSize: [44, 52],
            iconAnchor: [22, 48],   // tail tip = real location
        });
    }

    locateBtn.addEventListener('click', () => {
        if (isLocating) return;
        if (!navigator.geolocation) {
            toast('متصفحك مش بيدعم تحديد الموقع');
            return;
        }

        isLocating = true;
        locateBtn.classList.add('is-loading');
        locateBtn.innerHTML = SPINNER_HTML;

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                isLocating = false;
                locateBtn.classList.remove('is-loading');
                locateBtn.innerHTML = ORIGINAL_LOCATE_HTML;

                const c = [pos.coords.latitude, pos.coords.longitude];
                const accuracy = pos.coords.accuracy || 0;

                // Reject IP-based "country-level" fixes (>5km accuracy is useless).
                if (accuracy > 5000) {
                    toast('دقة GPS ضعيفة. شغّل الموقع من إعدادات الموبايل وحاول تاني.');
                    return;
                }

                // Replace any prior marker + accuracy halo
                if (userMarker)    map.removeLayer(userMarker);
                if (accuracyCircle) map.removeLayer(accuracyCircle);

                // Faint accuracy halo (so user understands the precision)
                accuracyCircle = L.circle(c, {
                    radius: accuracy,
                    color: '#2D5BFF',
                    weight: 1,
                    opacity: .25,
                    fillColor: '#2D5BFF',
                    fillOpacity: .08,
                }).addTo(map);

                // Avatar marker (their "character" on the map)
                userMarker = L.marker(c, {
                    icon: buildUserIcon(),
                    zIndexOffset: 2000,
                    interactive: false,
                }).addTo(map);

                // Zoom in based on accuracy — closer for precise GPS, wider for rough
                const targetZoom = accuracy < 50 ? 17 : accuracy < 200 ? 16 : 15;
                map.flyTo(c, targetZoom, { animate: true, duration: .9 });
            },
            (err) => {
                isLocating = false;
                locateBtn.classList.remove('is-loading');
                locateBtn.innerHTML = ORIGINAL_LOCATE_HTML;
                const msgs = {
                    1: 'لازم تسمح بتحديد الموقع من إعدادات المتصفح',
                    2: 'مش قادر يجيب موقعك. اتأكد إن GPS شغّال.',
                    3: 'الطلب اتأخر. حاول تاني.',
                };
                toast(msgs[err.code] || 'حصل خطأ في تحديد الموقع');
            },
            // High-accuracy + fresh fix. maximumAge=0 means no cached old fix.
            { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
        );
    });

    render('').then(() => { dataReady = true; maybeHideSkeleton(); });
})();
</script>
@endpush
@endsection
