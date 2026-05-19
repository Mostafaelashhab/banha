@php
    $sm = $craftsman->subTypeMeta();
    $cm = $craftsman->categoryMeta();
@endphp
<a href="{{ route('directory.show', $craftsman) }}"
   class="card-light p-3 flex items-center gap-3 hover:bg-cream-100 transition group focus:outline-none focus-visible:ring-4 focus-visible:ring-coral-500/30">
    @if($craftsman->photo_url)
        <img src="{{ $craftsman->photo_url }}" alt="{{ $craftsman->name }}" loading="lazy"
             class="w-14 h-14 rounded-2xl object-cover shrink-0">
    @else
        <x-icon-tile :icon="$sm['icon'] ?? 'tools'" size="xl"/>
    @endif

    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-sm font-extrabold text-ink-950 truncate">{{ $craftsman->name }}</span>
            @if($craftsman->hasPaidVerified())
                <span title="موثّق" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-honey-500 text-white shrink-0">
                    <x-icon name="star" class="w-2.5 h-2.5" filled/>
                </span>
            @elseif($craftsman->is_verified)
                <span title="موثّق رسمي" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-mint-500 text-white shrink-0">
                    <x-icon name="check" class="w-2.5 h-2.5"/>
                </span>
            @endif
            @if($craftsman->accepts_emergency || $craftsman->is_24h)
                <span class="text-[10px] font-extrabold bg-blush-100 text-blush-600 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                    <x-icon name="bolt" class="w-2.5 h-2.5"/> طوارئ
                </span>
            @endif
        </div>
        <div class="text-[11px] text-ink-500 truncate">
            {{ $sm['label'] ?? '' }}
            @if($craftsman->zone)
                <span class="text-ink-400">·</span>
                {{ $craftsman->zone->name }}
            @endif
            @if($craftsman->years_experience)
                <span class="text-ink-400">·</span>
                <span class="font-bold text-ink-700">{{ $craftsman->years_experience }} سنة خبرة</span>
            @endif
        </div>
        <div class="flex items-center gap-2 mt-1 text-[10px]">
            @if($craftsman->ratings_count > 0)
                <span class="inline-flex items-center gap-0.5 text-honey-700 font-bold">
                    <x-icon name="star" class="w-3 h-3" filled/>
                    {{ $craftsman->rating_avg }}
                    <span class="text-ink-400 font-normal">({{ $craftsman->ratings_count }})</span>
                </span>
            @endif
            @if($craftsman->jobs_completed > 0)
                <span class="text-mint-700 font-bold inline-flex items-center gap-0.5">
                    <x-icon name="check" class="w-2.5 h-2.5"/>{{ $craftsman->jobs_completed }} شغلانة
                </span>
            @endif
            @if($craftsman->min_callout_fee)
                <span class="text-ink-500 font-bold">معاينة من {{ number_format($craftsman->min_callout_fee) }} ج</span>
            @endif
        </div>
    </div>

    @if($craftsman->whatsapp)
        <span class="shrink-0 w-10 h-10 grid place-items-center rounded-full bg-[#25D366] text-white" title="واتساب" aria-label="واتساب">
            <x-icon name="whatsapp" class="w-4 h-4"/>
        </span>
    @elseif($craftsman->phone)
        <span class="shrink-0 w-10 h-10 grid place-items-center rounded-full bg-coral-500 text-white" title="اتصل" aria-label="اتصل">
            <x-icon name="phone" class="w-4 h-4"/>
        </span>
    @endif
</a>
