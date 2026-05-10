@props([
    'lat' => null,
    'lng' => null,
    'name' => 'location', // hidden field prefix → produces lat & lng
    'label' => 'مكان النشاط على الخريطة',
    'help' => 'لو مش هتحدد مكان، نشاطك مش هيظهر على الخريطة. ممكن تعدّل في أي وقت.',
])

@once
@push('head')
<link rel="preconnect" href="https://unpkg.com" crossorigin>
<link rel="preconnect" href="https://tile.openstreetmap.org" crossorigin>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<link rel="preload" as="script" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin>
<style>
    .map-picker-wrap { position: relative; }
    .map-picker {
        height: 240px;
        border-radius: 18px;
        background: #FFF7F1;
        z-index: 0;
        overflow: hidden;
    }
    .map-picker .leaflet-tile {
        filter: saturate(.75) brightness(1.04) contrast(.95);
    }
    .map-picker.is-empty::before {
        content: 'دوس على الخريطة لتحديد المكان';
        position: absolute;
        inset: 0;
        z-index: 400;
        pointer-events: none;
        background: rgba(11, 11, 12, .35);
        color: #fff;
        font-weight: 800;
        font-size: 13px;
        display: grid;
        place-items: center;
        border-radius: 18px;
    }
    .map-picker-status {
        position: absolute; top: 8px; inset-inline-end: 8px;
        z-index: 401;
        background: #fff;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 10px;
        font-weight: 800;
        box-shadow: 0 4px 10px -2px rgba(11,11,12,.2);
        display: inline-flex; align-items: center; gap: 4px;
    }
    .map-picker-status.is-set { background: #1FA857; color: #fff; }
    .map-picker-actions {
        display: flex; gap: 6px; margin-top: 8px;
    }
    .map-picker-btn {
        flex: 1;
        padding: 8px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 800;
        background: #FFF7F1;
        border: 1px solid rgba(11,11,12,.08);
        color: #0B0B0C;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }
    .map-picker-btn:hover { background: #FFE9D6; }
    .map-picker-btn.danger { color: #DC2626; }
</style>
@endpush
@endonce

@php
    $hasInitial = $lat !== null && $lng !== null && $lat !== '' && $lng !== '';
    $oldLat = old($name.'_lat', $lat);
    $oldLng = old($name.'_lng', $lng);
    $hasValue = $oldLat !== null && $oldLng !== null && $oldLat !== '' && $oldLng !== '';
@endphp

<div class="map-picker-wrap" data-map-picker
     data-init-lat="{{ $oldLat }}"
     data-init-lng="{{ $oldLng }}">
    <label class="text-xs font-bold text-ink-500 mb-1.5 block">{{ $label }}</label>

    <div id="{{ $name }}-map" class="map-picker {{ $hasValue ? '' : 'is-empty' }}"></div>

    <span class="map-picker-status {{ $hasValue ? 'is-set' : '' }}" data-status>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px;">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
        </svg>
        <span data-status-text>{{ $hasValue ? 'المكان محدّد' : 'مفيش مكان' }}</span>
    </span>

    <input type="hidden" name="lat" value="{{ $oldLat }}" data-lat>
    <input type="hidden" name="lng" value="{{ $oldLng }}" data-lng>

    <div class="map-picker-actions">
        <button type="button" class="map-picker-btn" data-locate>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;">
                <circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
            </svg>
            استخدم موقعي
        </button>
        <button type="button" class="map-picker-btn danger" data-clear style="{{ $hasValue ? '' : 'display:none' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="width:13px;height:13px;">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            مسح
        </button>
    </div>

    <p class="text-[10px] text-ink-400 mt-1.5 leading-relaxed">{{ $help }}</p>
</div>

@once
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    function initPicker(wrap) {
        const mapEl = wrap.querySelector('.map-picker');
        if (!mapEl) return;
        const latInput  = wrap.querySelector('[data-lat]');
        const lngInput  = wrap.querySelector('[data-lng]');
        const status    = wrap.querySelector('[data-status]');
        const statusTxt = wrap.querySelector('[data-status-text]');
        const clearBtn  = wrap.querySelector('[data-clear]');
        const locateBtn = wrap.querySelector('[data-locate]');

        const initLat = parseFloat(wrap.dataset.initLat);
        const initLng = parseFloat(wrap.dataset.initLng);
        const hasInit = !isNaN(initLat) && !isNaN(initLng);

        const BANHA = [30.4582, 31.1797];
        const map = L.map(mapEl, {
            center: hasInit ? [initLat, initLng] : BANHA,
            zoom: hasInit ? 16 : 13,
            scrollWheelZoom: true,
            attributionControl: false,
        });
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            crossOrigin: true,
            keepBuffer: 4,
            updateWhenIdle: true,
        }).addTo(map);

        // Pin icon (coral teardrop)
        const pinIcon = L.divIcon({
            html: '<div style="width:32px;height:32px;background:#FF7A4D;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 12px -2px rgba(255,122,77,.6);border:3px solid #fff;display:grid;place-items:center;"><div style="width:10px;height:10px;background:#fff;border-radius:50%;transform:rotate(45deg);"></div></div>',
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 30],
        });

        let marker = null;
        function setMarker(lat, lng, fly = false) {
            if (marker) marker.setLatLng([lat, lng]);
            else marker = L.marker([lat, lng], { icon: pinIcon, draggable: true }).addTo(map);
            marker.on('dragend', () => {
                const ll = marker.getLatLng();
                writeValues(ll.lat, ll.lng);
            });
            writeValues(lat, lng);
            if (fly) map.flyTo([lat, lng], 16, { duration: 0.6 });
        }
        function writeValues(lat, lng) {
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);
            mapEl.classList.remove('is-empty');
            status.classList.add('is-set');
            statusTxt.textContent = 'المكان محدّد';
            clearBtn.style.display = '';
        }
        function clear() {
            if (marker) { map.removeLayer(marker); marker = null; }
            latInput.value = '';
            lngInput.value = '';
            mapEl.classList.add('is-empty');
            status.classList.remove('is-set');
            statusTxt.textContent = 'مفيش مكان';
            clearBtn.style.display = 'none';
        }

        if (hasInit) setMarker(initLat, initLng);

        map.on('click', (e) => setMarker(e.latlng.lat, e.latlng.lng));
        clearBtn?.addEventListener('click', clear);
        locateBtn?.addEventListener('click', () => {
            if (!navigator.geolocation) return;
            locateBtn.disabled = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    setMarker(pos.coords.latitude, pos.coords.longitude, true);
                    locateBtn.disabled = false;
                },
                () => { locateBtn.disabled = false; },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    }

    function bootAll() {
        document.querySelectorAll('[data-map-picker]').forEach(initPicker);
    }
    if (typeof L !== 'undefined') bootAll();
    else window.addEventListener('load', bootAll);
})();
</script>
@endpush
@endonce
