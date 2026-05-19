{{-- Banhawy x-skeleton — shimmer placeholder. See DESIGN-SYSTEM.md §7.1. --}}
{{-- Use these instead of spinners on initial page load. Match the final layout's shape. --}}
{{-- Usage examples (literal angle brackets so Blade doesn't compile them as nested components): --}}
{{--   <x-skeleton class="h-4 w-32"/>                   — text line --}}
{{--   <x-skeleton variant="circle" class="w-12 h-12"/> — avatar --}}
{{--   <x-skeleton variant="text" lines="3"/>           — 3 text lines (last is 60% width) --}}
{{--   <x-skeleton class="aspect-[16/10] w-full"/>      — card photo --}}
{{-- The `shimmer` keyframe is already defined in app.css. --}}
@props([
    'variant' => 'block',     // block | circle | text
    'lines'   => 1,           // for variant=text
])

@php
    $baseClass = 'block bg-cream-200 shimmer';
    $shapeClass = match($variant) {
        'circle' => 'rounded-full',
        'text'   => 'rounded h-3',
        default  => 'rounded-xl',
    };
@endphp

@if($variant === 'text' && $lines > 1)
    <div {{ $attributes->merge(['class' => 'space-y-2']) }} aria-hidden="true">
        @for($i = 0; $i < $lines; $i++)
            <span class="{{ $baseClass }} {{ $shapeClass }}" style="width: {{ $i === $lines - 1 ? '60%' : '100%' }};"></span>
        @endfor
    </div>
@else
    <span aria-hidden="true" {{ $attributes->merge(['class' => "$baseClass $shapeClass"]) }}></span>
@endif
