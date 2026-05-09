@foreach($listings as $listing)
    @php $km = $listing->kindMeta(); $cm = $listing->categoryMeta(); @endphp
    <a href="{{ route('marketplace.show', $listing) }}" class="card-light p-0 overflow-hidden block hover:scale-[1.01] transition relative">
        @if($listing->isFeatured())
            <span class="absolute top-2 start-2 z-10 px-2 py-0.5 rounded-full bg-honey-500 text-ink-950 text-[10px] font-extrabold inline-flex items-center gap-1">
                ⭐ مميّز
            </span>
        @endif
        <span class="absolute top-2 end-2 z-10 px-1.5 py-0.5 rounded-full pill-{{ $km['tone'] }} text-[9px] font-bold inline-flex items-center gap-0.5">
            <x-icon :name="$km['icon']" class="w-2.5 h-2.5"/>
            {{ $km['label'] }}
        </span>
        @if($listing->photo_url)
            <img src="{{ $listing->photo_url }}" alt="" loading="lazy" class="w-full aspect-[4/3] object-cover">
        @else
            <div class="w-full aspect-[4/3] bg-cream-100 grid place-items-center text-ink-300">
                <x-icon :name="$cm['icon']" class="w-10 h-10"/>
            </div>
        @endif
        <div class="p-2.5">
            <h3 class="text-[13px] font-extrabold text-ink-950 line-clamp-1 leading-tight">{{ $listing->title }}</h3>
            @if(in_array($listing->kind, ['sale','buy'], true))
                <div class="text-coral-600 font-black text-sm mt-0.5">{{ $listing->priceLabel() }}</div>
            @endif
            <div class="text-[10px] text-ink-400 mt-1 truncate">
                @if($listing->zone) <x-icon name="map-pin" class="w-2.5 h-2.5 inline"/> {{ $listing->zone->name }} · @endif
                {{ $listing->created_at->diffForHumans(['short' => true]) }}
            </div>
        </div>
    </a>
@endforeach

<div data-feed-end
     data-next-url="{{ $listings->hasMorePages() ? $listings->nextPageUrl() : '' }}"
     data-has-more="{{ $listings->hasMorePages() ? '1' : '0' }}"></div>
