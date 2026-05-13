@foreach($listings as $listing)
    @php
        $km = $listing->kindMeta();
        $cm = $listing->categoryMeta();
        $colorByTone = ['coral' => '#2D5BFF', 'mint' => '#1FA857', 'blush' => '#E64646', 'honey' => '#FFD440'];
        $kindColor = $colorByTone[$km['tone']] ?? '#2D5BFF';
        $hasPrice = in_array($listing->kind, ['sale', 'buy'], true);
    @endphp
    <a href="{{ route('marketplace.show', $listing) }}"
       class="card-light p-0 overflow-hidden block hover:-translate-y-0.5 hover:shadow-lg transition relative {{ $listing->isFeatured() ? 'ring-2 ring-honey-500/40' : '' }}">

        {{-- Image (or branded fallback) --}}
        <div class="relative aspect-[4/3] overflow-hidden">
            @if($listing->photo_url)
                <img src="{{ $listing->photo_url }}" alt="" loading="lazy" class="w-full h-full object-cover">
            @else
                <div class="absolute inset-0 grid place-items-center"
                     style="background: linear-gradient(135deg, {{ $kindColor }}, {{ $kindColor }}cc);">
                    <x-icon :name="$cm['icon']" class="w-10 h-10 text-white/85"/>
                </div>
            @endif

            {{-- Top-end: kind badge --}}
            <span class="absolute top-2 end-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold text-white shadow"
                  style="background: {{ $kindColor }};">
                <x-icon :name="$km['icon']" class="w-2.5 h-2.5"/>
                {{ $km['label'] }}
            </span>

            {{-- Top-start: featured badge --}}
            @if($listing->isFeatured())
                <span class="absolute top-2 start-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-honey-500 text-ink-950 text-[10px] font-extrabold shadow">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-2.5 h-2.5"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                    مميّز
                </span>
            @endif

            {{-- Bottom dark gradient (for legibility of price overlay) --}}
            @if($hasPrice && $listing->price)
                <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/70 to-transparent"></div>
                <div class="absolute bottom-1.5 start-2 text-white font-black text-sm drop-shadow">
                    {{ $listing->priceLabel() }}
                </div>
            @endif
        </div>

        {{-- Body --}}
        <div class="p-2.5">
            <h3 class="text-[13px] font-extrabold text-ink-950 line-clamp-2 leading-snug min-h-[2.4em]">{{ $listing->title }}</h3>
            <div class="flex items-center gap-1.5 text-[10px] text-ink-400 mt-1.5 truncate">
                @if($listing->zone)
                    <x-icon name="map-pin" class="w-2.5 h-2.5 shrink-0"/>
                    <span class="truncate">{{ $listing->zone->name }}</span>
                    <span class="text-ink-300">·</span>
                @endif
                <span>{{ $listing->created_at->diffForHumans(['short' => true]) }}</span>
            </div>
        </div>
    </a>
@endforeach

<div data-feed-end
     data-next-url="{{ $listings->hasMorePages() ? $listings->nextPageUrl() : '' }}"
     data-has-more="{{ $listings->hasMorePages() ? '1' : '0' }}"></div>
