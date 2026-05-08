@php
    /** @var \App\Models\Business $business */
    $cm    = $business->categoryMeta();
    $sm    = $business->subTypeMeta();
@endphp

<a href="{{ route('directory.show', $business) }}" class="card-light p-3 flex items-center gap-3 hover:-translate-y-0.5 hover:shadow-lg transition">
    @if($business->photo_url)
        <img src="{{ $business->photo_url }}" alt="" class="w-12 h-12 rounded-2xl object-cover shrink-0">
    @else
        <span class="w-12 h-12 rounded-2xl grid place-items-center text-2xl shrink-0"
              style="background: {{ $cm['color'] }}20; border: 1px solid {{ $cm['color'] }}50">
            {{ $business->emoji ?: $sm['emoji'] }}
        </span>
    @endif
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-1.5">
            <h4 class="font-extrabold text-ink-950 text-sm truncate">{{ $business->name }}</h4>
            @if($business->is_verified)
                <span class="text-mint-700 shrink-0" title="موثّق">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5"><path d="M12 2 14.5 6.5 19.5 7l-3.5 3.5L17 16l-5-2.5L7 16l1-5.5L4.5 7 9.5 6.5z"/></svg>
                </span>
            @endif
        </div>
        <div class="text-[11px] text-ink-500">
            {{ $sm['label'] }}
            @if($business->zone) · {{ $business->zone->name }} @endif
            @if($business->is_24h) · <span class="text-mint-700 font-bold">٢٤ ساعة</span> @endif
        </div>
    </div>
    <div class="flex items-center gap-1.5 shrink-0">
        @if($business->phone)
            <a href="tel:{{ $business->phone }}" onclick="event.stopPropagation()"
               class="w-9 h-9 rounded-full bg-coral-100 text-coral-700 grid place-items-center hover:bg-coral-500 hover:text-white transition" title="اتصال">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
            </a>
        @endif
        @if($business->whatsapp)
            <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}" onclick="event.stopPropagation()" target="_blank"
               class="w-9 h-9 rounded-full bg-mint-100 text-mint-700 grid place-items-center hover:bg-mint-500 hover:text-white transition" title="واتساب">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9z"/>
                </svg>
            </a>
        @endif
    </div>
</a>
