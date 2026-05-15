@props([
    'href',
    'variant'   => 'map',  // map | menu | add | ad | custom | image
    'tag'       => null,
    'title'     => null,
    'desc'      => null,
    'cta'       => null,
    'image'     => null,
    'bgFrom'    => null,
    'bgTo'      => null,
    'imageOnly' => false,  // Full-bleed image render, no overlay
    'alt'       => null,
])

@php
    // Image-only mode: skip all the variant SVG art / overlay text and just
    // render the uploaded image filling the card. Used for admin banners that
    // are linked to a business — the image carries all the messaging.
    $renderImageOnly = $imageOnly && $image;
    $isCustom = ! $renderImageOnly && ($variant === 'custom' || $image || $bgFrom);
    $style = '';
    if ($isCustom && $bgFrom) {
        $style = "background: ".e($bgFrom).";";
    }
@endphp

@if($renderImageOnly)
    <a href="{{ $href }}" class="promo-card promo-card--image"
       aria-label="{{ $alt ?: ($title ?: 'بانر بنهاوي') }}"
       style="background: #F4F5F8;">
        <img src="{{ $image }}" alt="{{ $alt ?: ($title ?: 'بانر بنهاوي') }}"
             loading="lazy" decoding="async"
             class="block w-full h-full object-cover">
    </a>
@else

<a href="{{ $href }}" class="promo-card promo-card--{{ $variant }} group" @if($style) style="{{ $style }}" @endif>
    @unless($isCustom)
        <span class="promo-card-glow"></span>
    @endunless

    @if(! $isCustom)
    {{-- Decorative illustration per variant --}}
   <svg class="promo-card-art" viewBox="0 0 160 160" fill="none" aria-hidden="true">
    <defs>
        <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#DCEEFF"/>
            <stop offset="100%" stop-color="#F8FBFF"/>
        </linearGradient>

        <linearGradient id="road" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#CBD5E1"/>
            <stop offset="100%" stop-color="#94A3B8"/>
        </linearGradient>

        <linearGradient id="phoneBody" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#2B2B2B"/>
            <stop offset="100%" stop-color="#111111"/>
        </linearGradient>

        <linearGradient id="screen" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#F8FAFC"/>
            <stop offset="100%" stop-color="#E2E8F0"/>
        </linearGradient>

        <filter id="shadow" x="-50%" y="-50%" width="200%" height="200%">
            <feDropShadow dx="0" dy="10" stdDeviation="10" flood-opacity=".18"/>
        </filter>

        <filter id="blur">
            <feGaussianBlur stdDeviation="10"/>
        </filter>
    </defs>

    @switch($variant)

        {{-- ================= MAP ================= --}}
        @case('map')

            {{-- background glow --}}
            <circle cx="120" cy="34" r="28"
                fill="rgba(255,255,255,.45)"
                filter="url(#blur)"/>

            {{-- realistic map --}}
            <g filter="url(#shadow)">
                <rect x="18" y="24" width="124" height="112" rx="16"
                    fill="url(#sky)"/>

                {{-- roads --}}
                <path d="M10 78C40 70 58 72 82 90C108 110 126 112 154 98"
                    stroke="url(#road)"
                    stroke-width="12"
                    stroke-linecap="round"/>

                <path d="M52 18C60 42 66 66 72 140"
                    stroke="#D1D5DB"
                    stroke-width="8"
                    stroke-linecap="round"/>

                <path d="M18 52C54 56 94 54 140 42"
                    stroke="#E2E8F0"
                    stroke-width="6"
                    stroke-linecap="round"/>

                {{-- blocks / buildings --}}
                <rect x="28" y="34" width="22" height="16" rx="4"
                    fill="#BFE3C0"/>

                <rect x="92" y="58" width="34" height="22" rx="5"
                    fill="#C7D2FE"/>

                <rect x="102" y="92" width="26" height="18" rx="5"
                    fill="#FCD34D"/>

                <rect x="24" y="94" width="38" height="20" rx="5"
                    fill="#FBCFE8"/>
            </g>

            {{-- realistic pin --}}
            <g transform="translate(84 66)" filter="url(#shadow)">
                <path d="M0 28C-14 16-20 8-20-4A20 20 0 1 1 20-4C20 8 14 16 0 28Z"
                    fill="#FF5A36"/>

                <circle cx="0" cy="-2" r="8" fill="#fff"/>

                {{-- glossy reflection --}}
                <path d="M-6 -10C-2 -16 4 -16 8 -10"
                    stroke="rgba(255,255,255,.5)"
                    stroke-width="2"
                    stroke-linecap="round"/>
            </g>

            @break



        {{-- ================= QR ================= --}}
        @case('menu')

            {{-- phone shadow --}}
            <g filter="url(#shadow)">
                <rect x="42" y="12" width="76" height="136" rx="22"
                    fill="url(#phoneBody)"/>

                {{-- screen --}}
                <rect x="48" y="20" width="64" height="120" rx="16"
                    fill="url(#screen)"/>
            </g>

            {{-- dynamic island --}}
            <rect x="68" y="28" width="24" height="6" rx="3"
                fill="#0F172A"/>

            {{-- qr white card --}}
            <g filter="url(#shadow)">
                <rect x="58" y="48" width="44" height="44" rx="8"
                    fill="#fff"/>
            </g>

            {{-- realistic qr --}}
            <g fill="#111827">
                {{-- corners --}}
                <rect x="64" y="54" width="10" height="10" rx="2"/>
                <rect x="86" y="54" width="10" height="10" rx="2"/>
                <rect x="64" y="76" width="10" height="10" rx="2"/>

                {{-- center bits --}}
                <rect x="78" y="68" width="4" height="4"/>
                <rect x="84" y="68" width="4" height="4"/>
                <rect x="90" y="68" width="4" height="4"/>

                <rect x="78" y="74" width="4" height="4"/>
                <rect x="90" y="74" width="4" height="4"/>

                <rect x="78" y="80" width="4" height="4"/>
                <rect x="84" y="80" width="4" height="4"/>
                <rect x="90" y="80" width="4" height="4"/>
            </g>

            {{-- scan glow --}}
            <line x1="60" y1="70" x2="100" y2="70"
                stroke="#22D3EE"
                stroke-width="2"
                stroke-linecap="round">

                <animate attributeName="y1"
                    values="52;88;52"
                    dur="2s"
                    repeatCount="indefinite"/>

                <animate attributeName="y2"
                    values="52;88;52"
                    dur="2s"
                    repeatCount="indefinite"/>
            </line>

            {{-- reflection --}}
            <path d="M54 26 L74 26 L58 136 L54 136 Z"
                fill="rgba(255,255,255,.12)"/>

            @break
            {{-- ================= STORE ================= --}}
@case('add')

    {{-- background glow --}}
    <circle cx="122" cy="34" r="28"
        fill="rgba(255,255,255,.38)"
        filter="url(#blur)"/>

    {{-- realistic storefront --}}
    <g filter="url(#shadow)">
        {{-- building --}}
        <rect x="26" y="48" width="108" height="88" rx="12"
            fill="#ffffff"/>

        {{-- awning top --}}
        <path d="M22 54L34 30H126L138 54Z"
            fill="#F97316"/>

        {{-- awning stripes --}}
        <path d="M34 30H48L42 54H28Z" fill="#FDBA74"/>
        <path d="M56 30H70L64 54H50Z" fill="#FED7AA"/>
        <path d="M78 30H92L86 54H72Z" fill="#FDBA74"/>
        <path d="M100 30H114L108 54H94Z" fill="#FED7AA"/>

        {{-- glass windows --}}
        <rect x="38" y="72" width="28" height="34" rx="5"
            fill="#D6EEFF"/>

        <rect x="94" y="72" width="28" height="34" rx="5"
            fill="#D6EEFF"/>

        {{-- reflections --}}
        <path d="M42 76L52 76L44 102L38 102Z"
            fill="rgba(255,255,255,.35)"/>

        <path d="M98 76L108 76L100 102L94 102Z"
            fill="rgba(255,255,255,.35)"/>

        {{-- door --}}
        <rect x="70" y="66" width="20" height="58" rx="6"
            fill="#374151"/>

        <circle cx="84" cy="96" r="2"
            fill="#D1D5DB"/>
    </g>

    {{-- floating add badge --}}
    <g filter="url(#shadow)">
        <circle cx="118" cy="48" r="14"
            fill="#14B8A6"/>

        <path d="M118 41V55"
            stroke="#fff"
            stroke-width="3"
            stroke-linecap="round"/>

        <path d="M111 48H125"
            stroke="#fff"
            stroke-width="3"
            stroke-linecap="round"/>
    </g>

    @break




{{-- ================= MARKETPLACE ================= --}}
@case('ad')

    {{-- glow --}}
    <circle cx="124" cy="36" r="30"
        fill="rgba(255,255,255,.35)"
        filter="url(#blur)"/>

    {{-- marketplace cards --}}
    <g filter="url(#shadow)">

        {{-- back card --}}
        <rect x="34" y="42"
            width="72"
            height="90"
            rx="14"
            transform="rotate(-6 34 42)"
            fill="#E2E8F0"/>

        {{-- front product card --}}
        <rect x="52" y="28"
            width="76"
            height="104"
            rx="16"
            fill="#ffffff"/>

        {{-- image area --}}
        <rect x="62" y="40"
            width="56"
            height="42"
            rx="10"
            fill="#DBEAFE"/>

        {{-- fake product image --}}
        <circle cx="90" cy="58" r="12"
            fill="#60A5FA"/>

        <path d="M82 66C86 60 94 60 98 66"
            stroke="#fff"
            stroke-width="3"
            stroke-linecap="round"/>

        {{-- title lines --}}
        <rect x="64" y="92"
            width="40"
            height="6"
            rx="3"
            fill="#CBD5E1"/>

        <rect x="64" y="104"
            width="28"
            height="6"
            rx="3"
            fill="#E2E8F0"/>

        {{-- price --}}
        <rect x="64" y="116"
            width="22"
            height="10"
            rx="5"
            fill="#14B8A6"/>

        {{-- favorite button --}}
        <circle cx="112" cy="116" r="10"
            fill="#FEE2E2"/>

        <path d="M112 120
                L106 114
                A4 4 0 0 1 112 108
                A4 4 0 0 1 118 114Z"
            fill="#EF4444"/>
    </g>

    {{-- floating marketplace dots --}}
    <g fill="rgba(255,255,255,.7)">
        <circle cx="26" cy="54" r="4"/>
        <circle cx="136" cy="82" r="5"/>
        <circle cx="42" cy="126" r="3"/>
    </g>

    @break

    @endswitch
</svg>
    @endif

    @if($isCustom)
        {{-- Decorative wavy shape, lighter tint of the bg color, sits behind text --}}
        <svg class="promo-card-wave" viewBox="0 0 320 200" preserveAspectRatio="none" fill="rgba(255,255,255,.12)" aria-hidden="true">
            <path d="M0 50 C 80 10, 160 90, 320 30 L 320 0 L 0 0 Z"/>
            <path d="M0 170 C 100 130, 220 200, 320 150 L 320 200 L 0 200 Z" opacity=".6"/>
        </svg>

        {{-- Cover image on the right side (when provided) --}}
        @if($image)
            <div class="promo-card-image">
                <img src="{{ $image }}" alt="{{ $title }}" loading="lazy"/>
            </div>
        @endif

        <div class="promo-card-body {{ $image ? 'with-image' : '' }}">
            @if($tag)
                <span class="promo-card-tag">{{ $tag }}</span>
            @endif
            <div class="promo-card-title">{{ $title }}</div>
            @if($desc)
                <p class="promo-card-desc">{{ $desc }}</p>
            @endif
            @if($cta)
                <span class="promo-card-cta">
                    {{ $cta }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3.5 h-3.5">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </span>
            @endif
        </div>
    @else
        {{-- Default static variants (map/menu/add/ad) keep the old layout --}}
        <div class="relative max-w-[60%]">
            @if($tag)
                <span class="promo-card-tag">
                    <span class="promo-card-tag-dot"></span>
                    {{ $tag }}
                </span>
            @endif
            <div class="font-black text-lg leading-tight mt-2">{{ $title }}</div>
            @if($desc)
                <p class="text-white/90 text-[12px] mt-1 leading-snug font-bold">{{ $desc }}</p>
            @endif
        </div>

        @if($cta)
            <span class="promo-card-cta">
                {{ $cta }}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3.5 h-3.5">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </span>
        @endif
    @endif
</a>
@endif {{-- /imageOnly --}}
