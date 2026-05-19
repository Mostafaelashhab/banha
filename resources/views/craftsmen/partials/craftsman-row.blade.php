@php
    $sm = $craftsman->subTypeMeta();
    $cm = $craftsman->categoryMeta();
@endphp
<a href="{{ route('directory.show', $craftsman) }}" class="card-light p-3 flex items-center gap-3 hover:bg-cream-100 transition group">
    @if($craftsman->photo_url)
        <img src="{{ $craftsman->photo_url }}" alt="" loading="lazy"
             class="w-14 h-14 rounded-2xl object-cover shrink-0">
    @else
        <span class="w-14 h-14 rounded-2xl bg-coral-50 text-coral-600 grid place-items-center shrink-0 text-2xl">
            {{ $sm['emoji'] ?? '🔧' }}
        </span>
    @endif

    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-sm font-extrabold text-ink-950 truncate">{{ $craftsman->name }}</span>
            @if($craftsman->hasPaidVerified())
                <span title="موثّق" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-honey-500 text-white text-[9px] font-black">★</span>
            @elseif($craftsman->is_verified)
                <span title="موثّق رسمي" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-mint-500 text-white text-[9px] font-black">✓</span>
            @endif
            @if($craftsman->accepts_emergency || $craftsman->is_24h)
                <span class="text-[10px] font-extrabold bg-blush-100 text-blush-600 px-1.5 py-0.5 rounded-full">⚡ طوارئ</span>
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
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                    {{ $craftsman->rating_avg }}
                    <span class="text-ink-400 font-normal">({{ $craftsman->ratings_count }})</span>
                </span>
            @endif
            @if($craftsman->jobs_completed > 0)
                <span class="text-mint-700 font-bold">✓ {{ $craftsman->jobs_completed }} شغلانة</span>
            @endif
            @if($craftsman->min_callout_fee)
                <span class="text-ink-500 font-bold">معاينة من {{ number_format($craftsman->min_callout_fee) }} ج</span>
            @endif
        </div>
    </div>

    @if($craftsman->whatsapp)
        <span class="shrink-0 w-10 h-10 grid place-items-center rounded-full text-white"
              style="background: linear-gradient(135deg, #25D366, #128C7E)" title="واتساب">
            <x-icon name="whatsapp" class="w-4 h-4"/>
        </span>
    @elseif($craftsman->phone)
        <span class="shrink-0 w-10 h-10 grid place-items-center rounded-full bg-coral-500 text-white" title="اتصل">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
        </span>
    @endif
</a>
