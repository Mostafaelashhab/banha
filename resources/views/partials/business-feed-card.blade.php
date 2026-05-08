@php
    /** @var \App\Models\Business $business */
    $cm = $business->categoryMeta();
    $sm = $business->subTypeMeta();
    $isAd = $isAd ?? false;
@endphp

<div class="card-light p-3 mb-3 flex items-center gap-3 hover:bg-cream-100 transition relative">

    {{-- Compact thumbnail (square) --}}
    <a href="{{ route('directory.show', $business) }}" class="w-20 h-20 rounded-2xl overflow-hidden shrink-0 relative block">
        <x-business-cover :business="$business" class="w-full h-full" size="sm"/>
    </a>

    {{-- Body (full link via stretched-link) --}}
    <a href="{{ route('directory.show', $business) }}" class="flex-1 min-w-0">
        <div class="flex items-center gap-1.5 mb-0.5 flex-wrap">
            <span class="text-[9px] font-bold uppercase tracking-wide" style="color: {{ $cm['color'] }}">
                {{ $isAd ? 'مقترح' : 'دليل بنها' }}
            </span>
            @if($business->is_verified)
                <span class="text-[9px] font-bold text-mint-700 inline-flex items-center gap-0.5">
                    <x-icon name="check" class="w-2.5 h-2.5"/> موثّق
                </span>
            @endif
        </div>

        <h3 class="text-sm font-extrabold text-ink-950 leading-tight truncate">{{ $business->name }}</h3>

        <div class="text-[11px] text-ink-500 truncate mt-0.5">
            {{ $sm['emoji'] }} {{ $sm['label'] }}
            @if($business->zone) · {{ $business->zone->name }} @endif
        </div>

        <div class="flex items-center gap-2 mt-1.5">
            @if($business->ratings_count > 0)
                <span class="inline-flex items-center gap-0.5 text-[11px] font-bold text-ink-950">
                    <span class="text-coral-500">★</span>
                    {{ $business->rating_avg }}
                    <span class="text-ink-400 font-normal">({{ $business->ratings_count }})</span>
                </span>
            @endif
            @if($business->is_24h)
                <span class="text-[10px] font-bold text-mint-700">٢٤ ساعة</span>
            @endif
        </div>
    </a>

    {{-- Single icon-only action (call if available, else arrow) --}}
    @if($business->phone)
        <a href="tel:{{ $business->phone }}"
           class="w-10 h-10 rounded-full bg-coral-100 hover:bg-coral-500 hover:text-white text-coral-600 grid place-items-center shrink-0 transition relative z-10"
           aria-label="اتصل">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
        </a>
    @else
        <a href="{{ route('directory.show', $business) }}" class="w-8 h-8 rounded-full bg-cream-100 text-ink-400 grid place-items-center shrink-0" aria-label="افتح">
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </a>
    @endif
</div>
