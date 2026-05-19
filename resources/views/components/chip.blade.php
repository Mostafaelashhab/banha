{{-- Banhawy x-chip — filter pill / category jumper / inline tag. See DESIGN-SYSTEM.md §4.2. --}}
{{-- Wraps `.chip` + `.chip-active` (app.css). Use this for ALL pill-style filters/selectors. --}}
{{-- Usage examples (literal angle brackets so Blade doesn't compile them as nested components): --}}
{{--   <x-chip>كل المطاعم</x-chip> --}}
{{--   <x-chip active>قهاوي</x-chip> --}}
{{--   <x-chip icon="filter">فلتر</x-chip> --}}
{{--   <x-chip href="..." :count="$cat->items->count()">{{ '{{ $cat->name }}' }}</x-chip> --}}
@props([
    'active' => false,
    'icon'   => null,
    'href'   => null,
    'count'  => null,
])

@php
    $classes = 'chip' . ($active ? ' chip-active' : '');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)<x-icon :name="$icon" class="w-3.5 h-3.5"/>@endif
        <span>{{ $slot }}</span>
        @if($count !== null)
            <span class="text-[10px] {{ $active ? 'opacity-80' : 'opacity-60' }}">({{ $count }})</span>
        @endif
    </a>
@else
    <span {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)<x-icon :name="$icon" class="w-3.5 h-3.5"/>@endif
        <span>{{ $slot }}</span>
        @if($count !== null)
            <span class="text-[10px] {{ $active ? 'opacity-80' : 'opacity-60' }}">({{ $count }})</span>
        @endif
    </span>
@endif
