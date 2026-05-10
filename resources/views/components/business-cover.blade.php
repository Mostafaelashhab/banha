@props(['business', 'class' => 'aspect-[16/9]', 'size' => 'lg'])

@php
    /** @var \App\Models\Business $business */
    $cm = $business->categoryMeta();
    $sm = $business->subTypeMeta();

    // Strip broken Firebase image URLs (expired SSL on d-innova.com)
    $userPhoto = ($business->photo_url && ! str_contains($business->photo_url, 'd-innova.com'))
        ? $business->photo_url
        : null;

    // Auto-pick a curated cover from Unsplash (deterministic per business)
    $defaultCover = \App\Support\BusinessCovers::pick($business->category, $business->id);
    $finalSrc     = $userPhoto ?: $defaultCover;

    // For the fallback (when image fails) — clean colored block with business initial
    $initial = mb_substr(trim($business->name ?: '?'), 0, 1);
    // Two-tone gradient based on category color (looks like a designed placeholder)
    $color = $cm['color'] ?? '#FF7A4D';
@endphp

<div {{ $attributes->merge(['class' => "biz-cover {$class} relative overflow-hidden bg-cream-100"]) }}
     style="--cover-color: {{ $color }};">

    {{-- Designed fallback (visible by default; hidden once image loads) --}}
    <div class="absolute inset-0 flex items-center justify-center"
         style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}aa);">
        <span class="text-white font-black opacity-95 select-none {{ $size === 'sm' ? 'text-3xl' : ($size === 'md' ? 'text-5xl' : 'text-7xl') }}">
            {{ $initial }}
        </span>
        <span class="absolute bottom-2 end-2 text-white/80 {{ $size === 'sm' ? 'opacity-0' : '' }}">
            <x-icon :name="$sm['icon'] ?? 'bag'" class="w-5 h-5"/>
        </span>
    </div>

    {{-- Real image (loads on top of fallback; if it fails, fallback stays visible) --}}
    <img src="{{ $finalSrc }}" alt="{{ $business->name }}"
         class="absolute inset-0 w-full h-full object-cover z-10 transition-opacity duration-300"
         style="opacity: 0;"
         loading="lazy"
         onerror="this.style.display='none'"
         onload="this.style.opacity='1'">

    {{-- Optional gradient overlay for hero-size covers (legibility for overlay text) --}}
    @if($size === 'lg')
        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-ink-950/70 to-transparent z-20 pointer-events-none"></div>
    @endif
</div>
