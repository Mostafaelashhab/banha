@php
    /** @var \App\Models\Alert $alert */
    $meta = $alert->typeMeta();
    $isOfficialOutage = $alert->is_verified && in_array($alert->type, ['electricity', 'water'], true);
@endphp

<a href="{{ route('alerts.show', $alert) }}"
   class="card-light p-0 mb-3 overflow-hidden block hover:-translate-y-0.5 hover:shadow-lg transition relative {{ $isOfficialOutage ? 'ring-2 ring-coral-500/30' : '' }}">
    {{-- Top banner --}}
    <div class="px-4 py-2 flex items-center justify-between text-white text-xs font-bold pill-{{ $meta['tone'] }}"
         style="background: linear-gradient(90deg, {{ $meta['tone'] === 'blush' ? '#E64646, #2D5BFF' : ($meta['tone'] === 'mint' ? '#1FA857, #0D8A3F' : '#2D5BFF, #FFD440') }})">
        <span class="inline-flex items-center gap-1.5 text-white">
            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
            <x-icon :name="$meta['icon']" class="w-3.5 h-3.5"/>
            {{ $isOfficialOutage ? 'انقطاع' : 'تنبيه لحظي' }} · {{ $meta['label'] }}
        </span>
        @if($alert->is_verified)
            <span class="bg-white/20 backdrop-blur px-2 py-0.5 rounded-full text-[10px] inline-flex items-center gap-1">
                <x-icon name="check" class="w-3 h-3"/> {{ $isOfficialOutage ? 'رسمي' : 'موثّق' }}
            </span>
        @endif
    </div>

    {{-- Body --}}
    <div class="p-4">
        <p class="text-ink-950 text-[15px] font-bold leading-relaxed">{{ $alert->description }}</p>
        <div class="flex items-center flex-wrap gap-3 mt-3 text-[11px] text-ink-500">
            @if($alert->zone)
                <span class="inline-flex items-center gap-1">
                    <x-icon name="map-pin" class="w-3 h-3"/> {{ $alert->zone->name }}
                </span>
            @endif
            <span>·</span>
            <span class="inline-flex items-center gap-1 text-mint-700 font-bold">
                <x-icon name="check" class="w-3 h-3"/> {{ $alert->confirmations }} تأكيد
            </span>
            <span>·</span>
            <span>{{ $alert->created_at->diffForHumans(short: true) }}</span>
        </div>
    </div>
</a>
