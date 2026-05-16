@php
    $gallery = array_values(array_filter(array_merge(
        $item->photo_url ? [$item->photo_url] : [],
        is_array($item->photos) ? $item->photos : []
    )));
    $features = is_array($item->features) ? $item->features : [];
@endphp

<div class="py-3 first:pt-1 last:pb-1" data-menu-item>
    @if(count($gallery) > 1)
        <div class="flex gap-2 overflow-x-auto -mx-1 px-1 mb-2 snap-x snap-mandatory" style="scrollbar-width: thin;">
            @foreach($gallery as $url)
                <img src="{{ $url }}" alt="{{ $item->name }}" loading="lazy"
                     class="w-44 h-32 rounded-xl object-cover shrink-0 snap-start">
            @endforeach
        </div>
    @endif

    <div class="flex items-start gap-3">
        @if(count($gallery) === 1)
            <img src="{{ $gallery[0] }}" alt="{{ $item->name }}" loading="lazy" class="w-20 h-20 rounded-xl object-cover shrink-0">
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

            @if($item->capacity)
                <div class="mt-1 inline-flex items-center gap-1 text-[11px] font-bold text-ink-600">
                    <x-icon name="user" class="w-3 h-3"/>
                    تتسع لـ {{ $item->capacity }} {{ $item->capacity == 1 ? 'شخص' : 'أشخاص' }}
                </div>
            @endif

            @if($item->description)
                <p class="text-xs text-ink-500 leading-relaxed mt-1 whitespace-pre-line">{{ $item->description }}</p>
            @endif

            @if(! empty($features))
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @foreach($features as $f)
                        <span class="inline-flex items-center gap-1 bg-cream-100 text-ink-800 text-[11px] font-bold rounded-full px-2 py-0.5 border border-ink-950/8">
                            <x-icon name="{{ $f['icon'] ?? 'tag' }}" class="w-3 h-3 text-coral-600"/>
                            {{ $f['label'] ?? '' }}
                        </span>
                    @endforeach
                </div>
            @endif

            @if(($cartEnabled ?? false) && $item->price)
                <div class="mt-2"
                     data-cart-item
                     data-item-id="{{ $item->id }}"
                     data-item-name="{{ $item->name }}"
                     data-item-price="{{ (float) $item->price }}">
                    <button type="button"
                            data-cart-add
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-coral-500 text-white text-xs font-extrabold hover:bg-coral-600 transition">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3 h-3">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        أضف
                    </button>
                    <div data-cart-stepper class="hidden items-center gap-1 bg-coral-500 rounded-full p-1 w-fit">
                        <button type="button" data-cart-dec aria-label="ناقص"
                                class="w-7 h-7 rounded-full bg-white text-coral-600 grid place-items-center font-black text-base hover:bg-coral-50 transition">−</button>
                        <span data-cart-qty class="min-w-[28px] text-center text-white text-sm font-extrabold">0</span>
                        <button type="button" data-cart-inc aria-label="زيادة"
                                class="w-7 h-7 rounded-full bg-white text-coral-600 grid place-items-center font-black text-base hover:bg-coral-50 transition">+</button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
