<div class="flex items-start gap-3 py-3 first:pt-1 last:pb-1">
    @if($item->photo_url)
        <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" loading="lazy" class="w-20 h-20 rounded-xl object-cover shrink-0">
    @endif
    <div class="flex-1 min-w-0">
        <div class="flex items-baseline gap-2">
            <h3 class="text-base font-extrabold text-ink-950">{{ $item->name }}</h3>
            <span class="menu-leader"></span>
            @if($item->price)
                <span class="font-black text-coral-600 shrink-0 inline-flex items-baseline gap-1" dir="ltr">
                    {{ rtrim(rtrim(number_format($item->price, 2), '0'), '.') }}
                    <span class="text-[10px] text-ink-400 font-bold">{{ $currency }}</span>
                </span>
            @else
                <span class="text-xs text-ink-400 shrink-0">السعر بالاتصال</span>
            @endif
        </div>
        @if($item->description)
            <p class="text-xs text-ink-500 leading-relaxed mt-1">{{ $item->description }}</p>
        @endif
    </div>
</div>
