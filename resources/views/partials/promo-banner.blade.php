@props([
    'href',
    'variant' => 'map',  // map | menu | add | ad | custom
    'tag',
    'title',
    'desc',
    'cta',
    'image'   => null,
    'bgFrom'  => null,
    'bgTo'    => null,
])

@php
    $isCustom = $variant === 'custom' || $image || $bgFrom;
    $style = '';
    if ($isCustom) {
        if ($image) {
            $style = "background-image: linear-gradient(135deg, rgba(11,11,12,.55), rgba(11,11,12,.25)), url('".e($image)."'); background-size: cover; background-position: center;";
        } elseif ($bgFrom) {
            $to = $bgTo ?: $bgFrom;
            $style = "background: linear-gradient(135deg, ".e($bgFrom).", ".e($to).");";
        }
    }
@endphp

<a href="{{ $href }}" class="promo-card promo-card--{{ $variant }} group" @if($style) style="{{ $style }}" @endif>
    @unless($isCustom)
        <span class="promo-card-glow"></span>
    @endunless

    @if(! $isCustom)
    {{-- Decorative illustration per variant --}}
    <svg class="promo-card-art" viewBox="0 0 160 160" fill="none" aria-hidden="true">
        @switch($variant)
            @case('map')
                {{-- city skyline + floating pin --}}
                <circle cx="120" cy="42" r="32" fill="rgba(255,255,255,.18)"/>
                <circle cx="120" cy="42" r="20" fill="rgba(255,255,255,.28)"/>
                <g fill="rgba(255,255,255,.85)">
                    <rect x="22"  y="78"  width="22" height="64" rx="3"/>
                    <rect x="48"  y="62"  width="26" height="80" rx="3"/>
                    <rect x="78"  y="86"  width="18" height="56" rx="3"/>
                    <rect x="100" y="70"  width="22" height="72" rx="3"/>
                    <rect x="126" y="92"  width="22" height="50" rx="3"/>
                </g>
                <g transform="translate(70 38)">
                    <path d="M0 22c-12-9-18-17-18-26a18 18 0 0 1 36 0c0 9-6 17-18 26Z" fill="rgba(255,255,255,.95)"/>
                    <circle r="6" cx="0" cy="-5" fill="#FF7A4D"/>
                </g>
                @break

            @case('menu')
                {{-- QR code grid + phone --}}
                <rect x="36" y="32" width="88" height="88" rx="12" fill="rgba(255,255,255,.18)"/>
                <g fill="rgba(255,255,255,.95)">
                    <rect x="48" y="44" width="20" height="20" rx="3"/>
                    <rect x="92" y="44" width="20" height="20" rx="3"/>
                    <rect x="48" y="88" width="20" height="20" rx="3"/>
                    <rect x="76" y="76" width="8"  height="8"/>
                    <rect x="88" y="76" width="8"  height="8"/>
                    <rect x="100" y="76" width="8" height="8"/>
                    <rect x="76" y="88" width="8"  height="8"/>
                    <rect x="100" y="88" width="8" height="8"/>
                    <rect x="76" y="100" width="8" height="8"/>
                    <rect x="88" y="100" width="8" height="8"/>
                    <rect x="100" y="100" width="8" height="8"/>
                </g>
                <g fill="rgba(255,255,255,.4)">
                    <rect x="53" y="49" width="10" height="10" rx="1"/>
                    <rect x="97" y="49" width="10" height="10" rx="1"/>
                    <rect x="53" y="93" width="10" height="10" rx="1"/>
                </g>
                @break

            @case('add')
                {{-- storefront/shop --}}
                <g fill="rgba(255,255,255,.95)">
                    <rect x="32" y="64" width="96" height="76" rx="6"/>
                </g>
                <g fill="rgba(255,255,255,.55)">
                    <path d="M28 58 L40 36 L120 36 L132 58 Z"/>
                </g>
                <g fill="rgba(11,11,12,.18)">
                    <rect x="50" y="82" width="22" height="38" rx="3"/>
                    <rect x="88" y="82" width="22" height="20" rx="3"/>
                </g>
                <circle cx="80" cy="48" r="6" fill="#fff"/>
                <text x="80" y="52" font-size="10" font-weight="900" text-anchor="middle" fill="#0F766E">+</text>
                @break

            @case('ad')
                {{-- megaphone / speaker --}}
                <g transform="translate(20 50)">
                    <path d="M0 30 L60 10 L60 70 L0 50 Z" fill="rgba(255,255,255,.95)"/>
                    <path d="M60 5 L120 -10 L120 90 L60 75 Z" fill="rgba(255,255,255,.7)"/>
                    <path d="M0 30 L0 50 L-12 50 L-12 30 Z" fill="rgba(255,255,255,.6)"/>
                </g>
                <g stroke="rgba(255,255,255,.8)" stroke-width="3" stroke-linecap="round" fill="none">
                    <line x1="130" y1="40" x2="142" y2="32"/>
                    <line x1="134" y1="60" x2="148" y2="60"/>
                    <line x1="130" y1="80" x2="142" y2="88"/>
                </g>
                @break
        @endswitch
    </svg>
    @endif

    <div class="relative max-w-[60%]">
        @if($tag)
            <div class="text-[10px] font-extrabold tracking-wider uppercase opacity-80 mb-1.5">{{ $tag }}</div>
        @endif
        <div class="font-black text-lg leading-tight">{{ $title }}</div>
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
</a>
