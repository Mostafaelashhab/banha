{{-- Banhawy x-button — canonical button. See DESIGN-SYSTEM.md §4.3. --}}
{{-- Usage examples (literal angle brackets so Blade doesn't compile them as nested components): --}}
{{--   <x-button>حفظ</x-button>                                 — primary, md (defaults) --}}
{{--   <x-button variant="secondary">إلغاء</x-button> --}}
{{--   <x-button variant="danger" icon="trash" size="sm">حذف</x-button> --}}
{{--   <x-button variant="outline" icon="filter" iconEnd>فلتر</x-button> --}}
{{--   <x-button variant="whatsapp" icon="whatsapp" href="https://wa.me/...">اتصل</x-button> --}}
{{--   <x-button :loading="$saving" type="submit" block>احفظ التعديلات</x-button> --}}
{{--   <x-button pill size="sm" icon="map-pin">على الخريطة</x-button>    — rounded-full --}}
{{-- Pass any HTML attribute through (data-*, aria-*, onclick, class — class merges correctly). --}}
@props([
    'variant' => 'primary',     // primary | secondary | outline | ghost | danger | whatsapp
    'size'    => 'md',          // sm | md | lg
    'icon'    => null,          // x-icon name string
    'iconEnd' => false,         // place icon AFTER the label
    'href'    => null,          // if set, renders <a> instead of <button>
    'loading' => false,         // shows spinner, disables button
    'block'   => false,         // w-full
    'pill'    => false,         // rounded-full instead of rounded-lg/xl
    'type'    => 'button',      // submit | button | reset
])

@php
    $variantClasses = match($variant) {
        'primary'   => 'bg-coral-500 hover:bg-coral-600 active:bg-coral-700 text-white shadow-sm hover:shadow',
        'secondary' => 'bg-cream-100 hover:bg-cream-200 text-ink-950',
        'outline'   => 'bg-white border-2 border-coral-500 text-coral-600 hover:bg-coral-50',
        'ghost'     => 'bg-transparent hover:bg-cream-100 text-ink-700',
        'danger'    => 'bg-blush-500 hover:bg-blush-600 active:bg-blush-600 text-white',
        'whatsapp'  => 'bg-[#25D366] hover:bg-[#1FAE52] text-white',
        default     => 'bg-coral-500 hover:bg-coral-600 text-white',
    };

    $radiusClass = $pill ? 'rounded-full' : match($size) {
        'sm' => 'rounded-lg',
        default => 'rounded-xl',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs font-extrabold gap-1.5',
        'lg' => 'px-5 py-3.5 text-sm font-black gap-2',
        default => 'px-4 py-2.5 text-sm font-extrabold gap-1.5',
    };
    $sizeClasses .= ' ' . $radiusClass;

    $iconSize = $size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4';

    $base = 'inline-flex items-center justify-center transition disabled:opacity-60 disabled:cursor-not-allowed disabled:pointer-events-none';
    $allClasses = trim($base . ' ' . $variantClasses . ' ' . $sizeClasses . ($block ? ' w-full' : ''));
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $allClasses]) }}>
        @if($icon && ! $iconEnd)<x-icon :name="$icon" :class="$iconSize"/>@endif
        <span>{{ $slot }}</span>
        @if($icon && $iconEnd)<x-icon :name="$icon" :class="$iconSize"/>@endif
    </a>
@else
    <button type="{{ $type }}" @disabled($loading) {{ $attributes->merge(['class' => $allClasses]) }}>
        @if($loading)
            <svg class="{{ $iconSize }} animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            <span>{{ $slot }}</span>
        @else
            @if($icon && ! $iconEnd)<x-icon :name="$icon" :class="$iconSize"/>@endif
            <span>{{ $slot }}</span>
            @if($icon && $iconEnd)<x-icon :name="$icon" :class="$iconSize"/>@endif
        @endif
    </button>
@endif
