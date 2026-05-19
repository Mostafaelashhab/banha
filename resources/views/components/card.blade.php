{{-- Banhawy x-card — canonical white card. See DESIGN-SYSTEM.md §4.1. --}}
{{-- Wraps `.card-light` (app.css) so we get the canonical white surface, 22px radius, --}}
{{-- soft border and shadow-card consistently — no more ad-hoc `bg-white rounded-2xl border…` clones. --}}
{{-- Usage examples (literal angle brackets so Blade doesn't compile them as nested components): --}}
{{--   <x-card>… content …</x-card>                     — default p-4 --}}
{{--   <x-card padding="lg">…</x-card>                  — p-6 --}}
{{--   <x-card padding="none">…</x-card>                — caller controls inner padding --}}
{{--   <x-card tier="gold">…</x-card>                   — verified-gold strip --}}
{{--   <x-card variant="sponsored">…</x-card>           — honey-accent sponsored post --}}
{{--   <x-card as="a" href="…">… clickable card …</x-card> --}}
@props([
    'padding' => 'md',            // none | sm | md | lg
    'tier'    => null,            // silver | gold (verified-post strips)
    'variant' => null,            // sponsored | announcement | dark
    'as'      => 'div',           // div | a | section | article
    'href'    => null,            // used when as=a
])

@php
    $padClass = match($padding) {
        'none' => '',
        'sm'   => 'p-3',
        'lg'   => 'p-6',
        default => 'p-4',
    };

    $tierClass = match($tier) {
        'silver' => 'tier-silver',
        'gold'   => 'tier-gold relative',
        default  => '',
    };

    $variantClass = match($variant) {
        'sponsored'    => 'post-sponsored',
        'announcement' => 'post-announcement',
        default        => '',
    };

    $baseClass = $variant === 'dark' ? 'card-dark' : 'card-light';
    $classes = trim("$baseClass $padClass $tierClass $variantClass");
    $linkA11y = 'focus:outline-none focus-visible:ring-4 focus-visible:ring-coral-500/30 transition';
@endphp

@if($as === 'a')
    <a @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => "$classes $linkA11y"]) }}>{{ $slot }}</a>
@elseif($as === 'section')
    <section {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</section>
@elseif($as === 'article')
    <article {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</article>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</div>
@endif
