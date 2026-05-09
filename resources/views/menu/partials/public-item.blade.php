<div class="flex items-center gap-3 p-2.5">
    @if($item->photo_url)
        <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" loading="lazy" class="w-16 h-16 rounded-xl object-cover shrink-0">
    @endif
    <div class="flex-1 min-w-0">
        <div class="flex items-baseline justify-between gap-2">
            <h3 class="text-sm font-extrabold text-ink-950">{{ $item->name }}</h3>
            @if($item->price)
                <span class="text-base font-black text-coral-600 shrink-0" dir="ltr">
                    {{ rtrim(rtrim(number_format($item->price, 2), '0'), '.') }} <span class="text-[10px] text-ink-500">{{ $currency }}</span>
                </span>
            @endif
        </div>
        @if($item->description)
            <p class="text-xs text-ink-500 leading-relaxed mt-0.5">{{ $item->description }}</p>
        @endif
    </div>
</div>
