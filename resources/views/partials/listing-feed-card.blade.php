@php
    /** @var \App\Models\Listing $listing */
    $km = $listing->kindMeta();
    $cm = $listing->categoryMeta();
@endphp

<a href="{{ route('marketplace.show', $listing) }}" class="card-light p-0 mb-3 overflow-hidden block hover:scale-[1.005] transition relative">
    {{-- Top banner --}}
    <div class="px-4 py-2 flex items-center justify-between text-xs font-bold pill-{{ $km['tone'] }}">
        <span class="inline-flex items-center gap-1.5">
            <x-icon :name="$km['icon']" class="w-3.5 h-3.5"/>
            من السوق · {{ $km['label'] }}
        </span>
        @if($listing->isFeatured())
            <span class="inline-flex items-center gap-1 bg-honey-500 text-ink-950 px-2 py-0.5 rounded-full text-[10px]">
                ⭐ مميّز
            </span>
        @endif
    </div>

    @if($listing->photo_url)
        <img src="{{ $listing->photo_url }}" alt="" loading="lazy" class="w-full max-h-[400px] object-cover">
    @endif

    <div class="p-4">
        <div class="flex items-start justify-between gap-3 mb-1">
            <h3 class="text-base font-extrabold text-ink-950 leading-tight flex-1 min-w-0 line-clamp-2">{{ $listing->title }}</h3>
            @if(in_array($listing->kind, ['sale','buy'], true))
                <span class="text-coral-600 font-black text-base shrink-0">{{ $listing->priceLabel() }}</span>
            @endif
        </div>

        <div class="text-[12px] text-ink-500 inline-flex items-center gap-2">
            <x-icon :name="$cm['icon']" class="w-3 h-3"/>
            {{ $cm['label'] }}
            @if($listing->zone) · {{ $listing->zone->name }} @endif
            · {{ $listing->created_at->diffForHumans() }}
        </div>

        @if($listing->description)
            <p class="text-sm text-ink-500 mt-2 line-clamp-2 leading-relaxed">{{ $listing->description }}</p>
        @endif
    </div>
</a>
