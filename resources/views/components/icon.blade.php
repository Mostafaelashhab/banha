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
@endswitch
</svg>
