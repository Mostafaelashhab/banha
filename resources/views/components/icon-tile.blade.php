{{-- Banhawy x-icon-tile — soft-tinted icon container. See DESIGN-SYSTEM.md §4.6. --}}
{{-- The most-repeated UI primitive in the app (~77 instances across views). --}}
{{-- Pairs an SVG icon with a colored square/circle backdrop — used for section --}}
{{-- markers, list-item leading icons, tab headers, action hints. --}}
{{-- Usage examples (literal angle brackets so Blade doesn't compile them as nested components): --}}
{{--   <x-icon-tile icon="map-pin"/>                                — default coral, md (40px), rounded square --}}
{{--   <x-icon-tile icon="bell" tone="honey" size="sm"/>            — 24px honey-tinted tile --}}
{{--   <x-icon-tile icon="check" tone="mint" shape="circle"/>       — round mint tile --}}
{{--   <x-icon-tile icon="bolt" intensity="strong" size="lg"/>      — solid brand 48px --}}
{{--   <x-icon-tile icon="search" href="/search"/>                  — clickable (hover lifts a step) --}}
@props([
    'icon',                     /* required: x-icon name string */
    'tone'      => 'coral',     /* coral | mint | honey | blush | cream */
    'size'      => 'md',        /* sm (24) | md (40) | lg (48) | xl (56) */
    'shape'     => 'square',    /* square | circle */
    'intensity' => 'soft',      /* soft (bg-100/text-600) | strong (bg-500/text-white) */
    'href'      => null,        /* if set, renders <a> with hover lift */
])

@php
    /* Container size + inner icon size */
    [$boxClass, $iconClass] = match($size) {
        'sm' => ['w-6 h-6',   'w-3 h-3'],
        'lg' => ['w-12 h-12', 'w-5 h-5'],
        'xl' => ['w-14 h-14', 'w-6 h-6'],
        default => ['w-10 h-10', 'w-4 h-4'],
    };

    /* Shape — circle overrides; otherwise radius scales gently with size */
    $shapeClass = $shape === 'circle'
        ? 'rounded-full'
        : ($size === 'sm' ? 'rounded-md' : ($size === 'xl' || $size === 'lg' ? 'rounded-2xl' : 'rounded-xl'));

    /* Tone + intensity → bg / text */
    $colorClass = match([$tone, $intensity]) {
        ['coral','soft']    => 'bg-coral-100 text-coral-600',
        ['coral','strong']  => 'bg-coral-500 text-white',
        ['mint','soft']     => 'bg-mint-100 text-mint-700',
        ['mint','strong']   => 'bg-mint-500 text-white',
        ['honey','soft']    => 'bg-honey-100 text-honey-700',
        ['honey','strong']  => 'bg-honey-500 text-ink-950',
        ['blush','soft']    => 'bg-blush-100 text-blush-500',
        ['blush','strong']  => 'bg-blush-500 text-white',
        ['cream','soft']    => 'bg-cream-100 text-ink-500',
        ['cream','strong']  => 'bg-cream-200 text-ink-950',
        default             => 'bg-coral-100 text-coral-600',
    };

    $base = "grid place-items-center shrink-0 $boxClass $shapeClass $colorClass";
    $hoverClass = $href ? ' hover:opacity-90 transition' : '';
    $classes = trim($base . $hoverClass);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <x-icon :name="$icon" :class="$iconClass"/>
    </a>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>
        <x-icon :name="$icon" :class="$iconClass"/>
    </span>
@endif
