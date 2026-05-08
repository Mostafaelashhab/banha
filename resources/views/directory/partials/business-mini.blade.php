@php
    /** @var \App\Models\Business $business */
    $cm = $business->categoryMeta();
    $sm = $business->subTypeMeta();
@endphp

<a href="{{ route('directory.show', $business) }}" class="card-light p-3 w-44 shrink-0 hover:-translate-y-0.5 hover:shadow-lg transition">
    <div class="flex items-center justify-between mb-2">
        <span class="w-10 h-10 rounded-xl grid place-items-center text-xl"
              style="background: {{ $cm['color'] }}20; border: 1px solid {{ $cm['color'] }}50">
            {{ ($business->emoji && $business->emoji !== '🔥📦') ? $business->emoji : $sm['emoji'] }}
        </span>
        @if($business->is_24h)
            <span class="pill-mint text-[10px] font-bold px-2 py-0.5 rounded-full">٢٤س</span>
        @endif
    </div>
    <div class="text-sm font-extrabold text-ink-950 truncate leading-tight">{{ $business->name }}</div>
    <div class="text-[11px] text-ink-500 mt-0.5">{{ $sm['label'] }}</div>
    @if($business->zone)
        <div class="text-[10px] text-ink-400 mt-1 inline-flex items-center gap-1">
            <x-icon name="map-pin" class="w-2.5 h-2.5"/>
            {{ $business->zone->name }}
        </div>
    @endif
</a>
