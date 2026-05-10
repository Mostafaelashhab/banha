@props(['business', 'class' => 'aspect-[16/9]', 'size' => 'lg'])

@php
    /** @var \App\Models\Business $business */
    $cm = $business->categoryMeta();
    $sm = $business->subTypeMeta();

    // Strip broken Firebase image URLs (expired SSL on d-innova.com)
    $userPhoto = ($business->photo_url && ! str_contains($business->photo_url, 'd-innova.com'))
        ? $business->photo_url
        : null;

    $initial = mb_substr(trim($business->name ?: '?'), 0, 1);
    $color   = $cm['color'] ?? '#FF7A4D';
@endphp

<div {{ $attributes->merge(['class' => "biz-cover {$class} relative overflow-hidden"]) }}
     style="--cover-color: {{ $color }};">

    {{-- Branded Banhawy fallback: gradient + business initial + category icon + small ب mark --}}
    <div class="absolute inset-0 flex items-center justify-center"
         style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}cc 60%, {{ $color }}88);">
        {{-- Subtle dotted pattern for texture --}}
        <svg class="absolute inset-0 w-full h-full opacity-15" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
            <defs>
                <pattern id="biz-dots-{{ $business->id }}" x="0" y="0" width="22" height="22" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1.4" fill="white"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#biz-dots-{{ $business->id }})"/>
        </svg>

        {{-- Business name initial (the centerpiece) --}}
        <span class="relative text-white font-black opacity-95 select-none drop-shadow-md {{ $size === 'sm' ? 'text-3xl' : ($size === 'md' ? 'text-5xl' : 'text-7xl') }}">
            {{ $initial }}
        </span>

        {{-- Banhawy mark (top-start) --}}
        @if($size !== 'sm')
            <span class="absolute top-2 start-2 inline-flex items-center gap-1 bg-white/15 backdrop-blur-sm rounded-full px-2 py-0.5 text-white text-[10px] font-extrabold">
                <span class="w-3.5 h-3.5 rounded-md bg-white text-[9px] grid place-items-center font-black" style="color: {{ $color }};">ب</span>
                بنهاوي
            </span>
        @endif

        {{-- Category icon (bottom-end) --}}
        <span class="absolute bottom-2 end-2 text-white/85">
            <x-icon :name="$sm['icon'] ?? 'bag'" class="{{ $size === 'sm' ? 'w-4 h-4' : 'w-6 h-6' }}"/>
        </span>
    </div>

    {{-- User-uploaded image (if any) overlays the branded fallback --}}
    @if($userPhoto)
        <img src="{{ $userPhoto }}" alt="{{ $business->name }}"
             class="absolute inset-0 w-full h-full object-cover z-10 transition-opacity duration-300"
             style="opacity: 0;"
             loading="lazy"
             onerror="this.style.display='none'"
             onload="this.style.opacity='1'">
    @endif

    {{-- Hero gradient overlay for legibility --}}
    @if($size === 'lg')
        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-ink-950/70 to-transparent z-20 pointer-events-none"></div>
    @endif
</div>
