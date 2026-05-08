@props(['business', 'class' => 'aspect-[16/9]', 'size' => 'lg'])

@php
    /** @var \App\Models\Business $business */
    $cm = $business->categoryMeta();
    $sm = $business->subTypeMeta();
    $emoji = ($business->emoji && $business->emoji !== '🔥📦') ? $business->emoji : $sm['emoji'];

    // Strip broken Firebase image URLs (expired SSL on d-innova.com)
    $userPhoto = ($business->photo_url && ! str_contains($business->photo_url, 'd-innova.com'))
        ? $business->photo_url
        : null;

    // Auto-pick a curated cover from Unsplash based on category + business id (deterministic)
    $defaultCover = \App\Support\BusinessCovers::pick($business->category, $business->id);
    $finalSrc     = $userPhoto ?: $defaultCover;
@endphp

<div {{ $attributes->merge(['class' => "biz-cover {$class} relative overflow-hidden"]) }}
     style="--cover-color: {{ $cm['color'] }};">
    <img src="{{ $finalSrc }}" alt="{{ $business->name }}"
         class="absolute inset-0 w-full h-full object-cover z-10 transition-opacity"
         loading="lazy"
         onerror="this.parentElement.classList.add('is-fallback'); this.style.opacity=0">

    {{-- Gradient overlay for legibility (only when used as a hero/large) --}}
    @if($size === 'lg')
        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-ink-950/70 to-transparent z-20 pointer-events-none"></div>
    @endif

    {{-- Fallback layer (only visible when image fails) --}}
    <div class="biz-cover-fallback absolute inset-0 grid place-items-center"
         style="opacity: 0; pointer-events: none;">
        <span class="biz-cover-emoji {{ $size === 'sm' ? 'text-2xl' : ($size === 'md' ? 'text-3xl' : 'text-6xl md:text-7xl') }}">{{ $emoji }}</span>
    </div>
</div>
