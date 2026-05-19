@props(['name', 'filled' => false])

@php
    $base = $filled
        ? ['fill' => 'currentColor']
        : ['fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round'];
@endphp

<svg viewBox="0 0 24 24"
     @foreach($base as $k => $v) {{ $k }}="{{ $v }}" @endforeach
     {{ $attributes->merge(['class' => 'w-5 h-5', 'aria-hidden' => 'true']) }}>
@switch($name)
    @case('bell')
        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
        @break

    @case('search')
        <circle cx="11" cy="11" r="8"/>
        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        @break

    @case('filter')
        <line x1="4"  y1="6"  x2="20" y2="6"/>
        <line x1="7"  y1="12" x2="17" y2="12"/>
        <line x1="10" y1="18" x2="14" y2="18"/>
        @break

    @case('bolt')
        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
        @break

    @case('traffic')
        <path d="M9 4h6l1 4H8z"/>
        <path d="M8 8h8l1 4H7z"/>
        <path d="M7 12h10l1 4H6z"/>
        <path d="M5 16h14"/>
        <path d="M12 4V2"/>
        @break

    @case('flame')
        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
        @break

    @case('home')
        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <polyline points="9 22 9 12 15 12 15 22"/>
        @break

    @case('map-pin')
        <path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/>
        <circle cx="12" cy="10" r="3"/>
        @break

    @case('map')
        <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
        <line x1="9" y1="3" x2="9" y2="18"/>
        <line x1="15" y1="6" x2="15" y2="21"/>
        @break

    @case('user')
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
        @break

    @case('plus')
        <line x1="12" y1="5" x2="12" y2="19"/>
        <line x1="5" y1="12" x2="19" y2="12"/>
        @break

    @case('check')
        <polyline points="20 6 9 17 4 12"/>
        @break

    @case('graduation')
        <path d="M22 10v6"/>
        <path d="M6 12.5V16c0 1.7 2.7 3 6 3s6-1.3 6-3v-3.5"/>
        <path d="m2 10 10-5 10 5-10 5z"/>
        @break

    @case('coffee')
        <path d="M17 8h1a4 4 0 0 1 0 8h-1"/>
        <path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/>
        <line x1="6" y1="2" x2="6" y2="4"/>
        <line x1="10" y1="2" x2="10" y2="4"/>
        <line x1="14" y1="2" x2="14" y2="4"/>
        @break

    @case('utensils')
        <path d="M3 2v7c0 1.1.9 2 2 2h2v11"/>
        <path d="M7 2v20"/>
        <path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Z"/>
        @break

    @case('stethoscope')
        <path d="M11 2v2"/>
        <path d="M5 2v2"/>
        <path d="M5 3H4a2 2 0 0 0-2 2v4a6 6 0 0 0 12 0V5a2 2 0 0 0-2-2h-1"/>
        <path d="M8 15a6 6 0 0 0 12 0v-3"/>
        <circle cx="20" cy="10" r="2"/>
        @break

    @case('cart')
        <circle cx="8" cy="21" r="1"/>
        <circle cx="19" cy="21" r="1"/>
        <path d="M2 2h2.5l3 13.5a2 2 0 0 0 2 1.5h9a2 2 0 0 0 2-1.5l1.7-7.5H6"/>
        @break

    @case('ticket')
        <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/>
        <path d="M13 5v2"/>
        <path d="M13 17v2"/>
        <path d="M13 11v2"/>
        @break

    @case('bag')
        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
        <path d="M3 6h18"/>
        <path d="M16 10a4 4 0 0 1-8 0"/>
        @break

    @case('mask')
        <path d="M2 12c.94 0 2-.5 3-1.5 2 1.94 6 1.94 8 0 1 1 2 1.5 3 1.5s2-.5 3-1.5c0-.5 0-3.5 0-4.5S17 4 12 4 2 5.5 2 6.5s0 4 0 4.5"/>
        <path d="M6 11c1 .8 3 1.2 5 0M13 11c2 1.2 4 .8 5 0"/>
        <path d="M12 13c1 1.5 3 2 5 1M12 13c-1 1.5-3 2-5 1"/>
        @break

    @case('tag')
        <path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
        <line x1="7" y1="7" x2="7.01" y2="7"/>
        @break

    @case('heart')
        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
        @break

    @case('arrow-left')
        <line x1="19" y1="12" x2="5" y2="12"/>
        <polyline points="12 19 5 12 12 5"/>
        @break

    @case('arrow-right')
        <line x1="5" y1="12" x2="19" y2="12"/>
        <polyline points="12 5 19 12 12 19"/>
        @break

    @case('chevron-down')
        <polyline points="6 9 12 15 18 9"/>
        @break

    @case('chevron-left')
        <polyline points="15 18 9 12 15 6"/>
        @break

    @case('chevron-right')
        <polyline points="9 18 15 12 9 6"/>
        @break

    @case('bookmark')
        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
        @break

    @case('lock')
        <rect x="3" y="11" width="18" height="11" rx="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        @break

    @case('edit')
        <path d="M12 20h9"/>
        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z"/>
        @break

    @case('menu')
        <rect x="6" y="3" width="12" height="18" rx="2"/>
        <line x1="9" y1="8" x2="15" y2="8"/>
        <line x1="9" y1="12" x2="15" y2="12"/>
        <line x1="9" y1="16" x2="13" y2="16"/>
        @break

    @case('chart')
        <line x1="18" y1="20" x2="18" y2="10"/>
        <line x1="12" y1="20" x2="12" y2="4"/>
        <line x1="6" y1="20" x2="6" y2="14"/>
        <line x1="3" y1="20" x2="21" y2="20"/>
        @break

    @case('globe')
        <circle cx="12" cy="12" r="10"/>
        <line x1="2" y1="12" x2="22" y2="12"/>
        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        @break

    @case('star')
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        @break

    @case('clipboard')
        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
        @break

    @case('battery')
        <rect x="2" y="7" width="18" height="10" rx="2"/>
        <line x1="22" y1="11" x2="22" y2="13"/>
        <rect x="4" y="9" width="10" height="6" rx="1" fill="currentColor" stroke="none"/>
        @break

    @case('wifi')
        <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
        <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
        <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
        <line x1="12" y1="20" x2="12.01" y2="20"/>
        @break

    @case('signal')
        <line x1="2" y1="20" x2="2" y2="20"/>
        <line x1="6" y1="18" x2="6" y2="20"/>
        <line x1="10" y1="14" x2="10" y2="20"/>
        <line x1="14" y1="10" x2="14" y2="20"/>
        <line x1="18" y1="6" x2="18" y2="20"/>
        @break

    @case('twitter')
        <path d="M14.7 4h2.8l-6.1 7 7.2 9h-5.6l-4.4-5.7L3.5 20H.7l6.5-7.4L0 4h5.7l4 5.3L14.7 4z" fill="currentColor" stroke="none"/>
        @break

    @case('facebook')
        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" fill="currentColor" stroke="none"/>
        @break

    @case('instagram')
        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
        @break

    @case('youtube')
        <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/>
        <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="currentColor" stroke="none"/>
        @break

    @case('whatsapp')
        <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9z"/>
        <path d="M9 10a.5.5 0 0 0 1 0V9a.5.5 0 0 0-1 0v1a5 5 0 0 0 5 5h1a.5.5 0 0 0 0-1h-1a.5.5 0 0 0 0 1"/>
        @break

    @case('tiktok')
        <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/>
        @break

    @case('flag')
        <path d="M4 22V4"/>
        <path d="M4 4h13l-2 4 2 4H4"/>
        @break

    @case('trash')
        <polyline points="3 6 5 6 21 6"/>
        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
        <line x1="10" y1="11" x2="10" y2="17"/>
        <line x1="14" y1="11" x2="14" y2="17"/>
        @break

    @case('more')
        <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
        <circle cx="5" cy="12" r="1.5" fill="currentColor"/>
        <circle cx="19" cy="12" r="1.5" fill="currentColor"/>
        @break

    @case('logout')
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
        @break

    @case('thumbs-up')
        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3z"/>
        <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
        @break

    @case('thumbs-down')
        <path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3z"/>
        <path d="M17 2h2.67A2.31 2.31 0 0 1 22 4v7a2.31 2.31 0 0 1-2.33 2H17"/>
        @break

    @case('comment')
        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
        @break

    @case('share')
        <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
        <line x1="8.6" y1="13.5" x2="15.4" y2="17.5"/><line x1="15.4" y1="6.5" x2="8.6" y2="10.5"/>
        @break

    {{-- ── craftsmen ─────────────────────── --}}
    @case('wrench')
        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        @break

    @case('hammer')
        <path d="m15 12-8.5 8.5a2.12 2.12 0 1 1-3-3L12 9"/>
        <path d="M17.64 15 22 10.64"/><path d="m20.91 11.7-1.25-1.25-2.5-2.5-3.43 3.43 1.25 1.25 2.5 2.5z"/>
        @break

    @case('brush')
        <path d="M9.06 11.9 16.5 4.5a2.83 2.83 0 0 1 4 4L13 16"/>
        <path d="M5 21c0-3 1-5 4-5 1 0 1 2 0 3-2 1-4 0-4 2"/>
        @break

    @case('snowflake')
        <line x1="2" y1="12" x2="22" y2="12"/><line x1="12" y1="2" x2="12" y2="22"/>
        <path d="m20 16-4-4 4-4"/><path d="m4 8 4 4-4 4"/>
        <path d="m16 4-4 4-4-4"/><path d="m8 20 4-4 4 4"/>
        @break

    @case('gear')
        <circle cx="12" cy="12" r="3"/>
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
        @break

    @case('square')
        <rect x="3" y="3" width="18" height="18" rx="2"/>
        @break

    @case('brick')
        <path d="M3 8h18v4H3z"/><path d="M3 12h6v4H3z"/><path d="M9 12h6v4H9z"/><path d="M15 12h6v4h-6z"/>
        <path d="M3 16h18v4H3z"/>
        @break

    @case('grid')
        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        @break

    @case('anvil')
        <path d="M3 10h18l-3 4H6z"/><path d="M9 14v4"/><path d="M15 14v4"/><path d="M5 18h14"/>
        @break

    @case('car')
        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18 8h-7L7 11l-3 .9C3.5 12 3 12.4 3 13v3c0 .6.4 1 1 1h2"/>
        <circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>
        @break

    @case('bike')
        <circle cx="6" cy="17" r="3"/><circle cx="18" cy="17" r="3"/>
        <path d="M6 17 9 7l5 0 4 10"/><path d="M14 7h3"/>
        @break

    @case('key')
        <path d="m21 2-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3"/>
        @break

    @case('layers')
        <polygon points="12 2 2 7 12 12 22 7 12 2"/>
        <polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>
        @break

    @case('truck')
        <rect x="1" y="6" width="13" height="11" rx="1"/>
        <path d="M14 9h4l3 3v5h-7"/><circle cx="6" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>
        @break

    @case('train')
        <rect x="4" y="3" width="16" height="16" rx="2"/>
        <path d="M4 11h16"/>
        <path d="M12 3v8"/>
        <circle cx="8" cy="16" r="1"/>
        <circle cx="16" cy="16" r="1"/>
        <path d="M8 19l-2 3"/>
        <path d="M16 19l2 3"/>
        @break

    @case('tools')
        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        @break

    @case('bug')
        <rect x="8" y="6" width="8" height="14" rx="4"/>
        <path d="M19 7l-3 2"/><path d="M5 7l3 2"/><path d="M19 13h-3"/><path d="M5 13h3"/>
        <path d="M19 19l-3-2"/><path d="M5 19l3-2"/><path d="M12 6V2"/>
        @break

    {{-- ── food extras ───────────────────── --}}
    @case('cake')
        <path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/>
        <path d="M4 16h16"/><path d="M2 21h20"/>
        <path d="M7 8v3"/><path d="M12 8v3"/><path d="M17 8v3"/>
        @break

    @case('bread')
        <path d="M5 8a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v8a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2z"/>
        <path d="M9 12h6"/><path d="M9 16h6"/>
        @break

    @case('cup')
        <path d="M5 5h11v8a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4z"/>
        <path d="M16 8h2a3 3 0 0 1 0 6h-2"/><path d="M5 21h11"/>
        @break

    {{-- ── medical extras ────────────────── --}}
    @case('baby')
        <path d="M9 12h.01"/><path d="M15 12h.01"/>
        <path d="M10 16c.5.3 1.2.5 2 .5s1.5-.2 2-.5"/>
        <path d="M19 6.3a9 9 0 0 1 1.8 3.9 2 2 0 0 1 0 3.6 9 9 0 0 1-17.6 0 2 2 0 0 1 0-3.6A9 9 0 0 1 12 3c2 0 3.5 1.1 3.5 2.5s-.9 2.5-2 2.5c-.8 0-1.5-.4-1.5-1"/>
        @break

    @case('tooth')
        <path d="M12 5.5c-1.74-1-3.41-1.5-4.5-1.5C5 4 3 6 3 9c0 2 .5 4 1 5.5L6 21l3-7c1-.5 2-.5 3 0l3 7 2-6.5c.5-1.5 1-3.5 1-5.5 0-3-2-5-4.5-5-1.09 0-2.76.5-4.5 1.5z"/>
        @break

    @case('pill')
        <path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/>
        <path d="m8.5 8.5 7 7"/>
        @break

    @case('flask')
        <path d="M9 3h6v5l4 9a2 2 0 0 1-1.8 3H6.8A2 2 0 0 1 5 17l4-9z"/>
        <line x1="9" y1="3" x2="15" y2="3"/><line x1="6" y1="14" x2="18" y2="14"/>
        @break

    @case('paw')
        <circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/>
        <circle cx="4" cy="8" r="2"/><circle cx="6" cy="14" r="2"/>
        <path d="M9 14a4 4 0 0 0-4 4 3 3 0 0 0 3 3 5 5 0 0 0 3-1c.5-.4 1-.5 1.5-.5s1 .1 1.5.5a5 5 0 0 0 3 1 3 3 0 0 0 3-3 4 4 0 0 0-4-4 9 9 0 0 1-3-1 9 9 0 0 1-3 1z"/>
        @break

    {{-- ── shops extras ──────────────────── --}}
    @case('meat')
        <path d="M10 4a6 6 0 1 0 0 12c2 0 3-1 4-1 1.5 0 2 1 2.5 2.5 1 2.5 4 2.5 4 0 0-1.5-1-3-2-3.5-1-.5-1-1.5-1-3a6 6 0 0 0-7.5-7z"/>
        @break

    @case('fish')
        <path d="M6.5 12c.94-3.46 4.94-6 8.5-6 3.56 0 6.06 2.54 7 6-.94 3.47-3.44 6-7 6s-7.56-2.53-8.5-6Z"/>
        <path d="M2 12c2-2 4-2 6-1m-1.7 1.5c.3-.3.5-.6.7-1m-1.7 1.5c.3-.4.5-.6.7-1"/>
        <circle cx="15" cy="11" r=".5" fill="currentColor"/>
        @break

    @case('leaf')
        <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
        <path d="M2 21c0-3 1.85-5.36 5.08-6"/>
        @break

    @case('shirt')
        <path d="M20.4 5.4 16 3l-3 3h-2L8 3 3.6 5.4l2 4 2.4-1V20a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V8.4l2.4 1z"/>
        @break

    @case('book')
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
        @break

    @case('phone')
        <rect x="7" y="2" width="10" height="20" rx="2"/><line x1="11" y1="18" x2="13" y2="18"/>
        @break

    @case('tv')
        <rect x="2" y="7" width="20" height="13" rx="2"/><polyline points="17 2 12 7 7 2"/>
        @break

    @case('fuel')
        <line x1="3" y1="22" x2="15" y2="22"/><line x1="4" y1="9" x2="14" y2="9"/>
        <path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"/>
        <path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2 2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"/>
        @break

    @case('gem')
        <polygon points="6 3 18 3 22 9 12 22 2 9"/>
        <line x1="11" y1="3" x2="8" y2="9"/><line x1="13" y1="3" x2="16" y2="9"/>
        <line x1="2" y1="9" x2="22" y2="9"/>
        @break

    @case('gift')
        <polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/>
        <line x1="12" y1="22" x2="12" y2="7"/>
        <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
        <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
        @break

    @case('sofa')
        <path d="M19 9V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v4"/>
        <path d="M2 11a2 2 0 0 1 4 0v3h12v-3a2 2 0 0 1 4 0v5a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2z"/>
        <line x1="6" y1="20" x2="6" y2="22"/><line x1="18" y1="20" x2="18" y2="22"/>
        @break

    {{-- ── services extras ───────────────── --}}
    @case('scissors')
        <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
        <line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/>
        <line x1="8.12" y1="8.12" x2="12" y2="12"/>
        @break

    @case('camera')
        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
        <circle cx="12" cy="13" r="4"/>
        @break

    @case('dumbbell')
        <path d="M14.4 14.4 9.6 9.6"/><path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767 1.768a2 2 0 1 1-2.829-2.829l6.364-6.364a2 2 0 1 1 2.829 2.829l-1.768 1.767a2 2 0 1 1 2.828 2.829z"/>
        <path d="m21.5 21.5-1.4-1.4"/><path d="M3.9 3.9 2.5 2.5"/>
        <path d="M6.404 12.768a2 2 0 1 1-2.829-2.829l1.768-1.767a2 2 0 1 1-2.828-2.829l2.828-2.828a2 2 0 1 1 2.829 2.828l1.767-1.768a2 2 0 1 1 2.829 2.829z"/>
        @break

    @case('spray')
        <path d="M3 3h6v3H3z"/><path d="M5 6v4"/><path d="M3 10h6v8H3z"/>
        <circle cx="14" cy="6" r="1" fill="currentColor"/><circle cx="17" cy="9" r="1" fill="currentColor"/>
        <circle cx="20" cy="6" r="1" fill="currentColor"/><circle cx="14" cy="12" r="1" fill="currentColor"/>
        @break

    @case('printer')
        <polyline points="6 9 6 2 18 2 18 9"/>
        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
        <rect x="6" y="14" width="12" height="8"/>
        @break

    @case('briefcase')
        <rect x="2" y="7" width="20" height="14" rx="2"/>
        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
        @break
@endswitch
</svg>
