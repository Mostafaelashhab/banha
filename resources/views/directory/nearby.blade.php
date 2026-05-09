@extends('layouts.app', ['title' => 'حواليّا · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950">📍 حواليّا</h1>
    </div>

    @if(! $lat)
        <div class="card-light p-8 text-center" data-geo-prompt>
            <div class="icon-tile mx-auto mb-4 text-coral-600 w-16 h-16">
                <x-icon name="map-pin" class="w-7 h-7"/>
            </div>
            <h3 class="text-lg font-extrabold text-ink-950 mb-1">شغّل الموقع</h3>
            <p class="text-ink-500 text-sm mb-4">عشان نطلعلك أقرب نشاطات ليك في بنها.</p>
            <button type="button" class="btn-primary mx-auto" data-geo-go>
                <x-icon name="map-pin" class="w-4 h-4"/>
                دوّر حواليّا
            </button>
            <p class="text-[10px] text-ink-400 mt-3">المتصفح هيسألك تأكّد. مش بنحفظ موقعك.</p>
        </div>
    @else
        <div class="card-light p-3 mb-3 text-xs text-ink-500 flex items-center justify-between">
            <span>📍 موقعك: {{ number_format($lat, 4) }}, {{ number_format($lng, 4) }}</span>
            <a href="{{ route('directory.nearby') }}" class="text-coral-600 font-bold">إعادة</a>
        </div>

        {{-- Category filter --}}
        <div class="flex gap-2 mb-3 overflow-x-auto scrollbar-hide -mx-4 px-4">
            <a href="{{ route('directory.nearby', ['lat' => $lat, 'lng' => $lng]) }}"
               class="chip {{ ! $category ? 'chip-active' : '' }} shrink-0">الكل</a>
            @foreach($categories as $key => $cm)
                <a href="{{ route('directory.nearby', ['lat' => $lat, 'lng' => $lng, 'category' => $key]) }}"
                   class="chip {{ $category === $key ? 'chip-active' : '' }} shrink-0">{{ $cm['label'] }}</a>
            @endforeach
        </div>

        @if($businesses->isEmpty())
            <div class="card-light p-8 text-center text-ink-500 text-sm">مفيش نشاطات قريبة في النطاق ده.</div>
        @else
            <div class="space-y-2">
                @foreach($businesses as $b)
                    <a href="{{ route('directory.show', $b) }}" class="card-light p-3 flex items-center gap-3 hover:bg-cream-100 transition">
                        <x-business-cover :business="$b" class="w-16 h-16 rounded-xl shrink-0" size="sm"/>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-extrabold text-ink-950 truncate">{{ $b->name }}</h3>
                            <div class="text-[11px] text-ink-500 truncate">{{ $b->displayType() }}</div>
                            @if($b->ratings_count > 0)
                                <div class="text-[11px] mt-0.5">
                                    <span class="text-coral-500">★</span>
                                    <span class="font-bold">{{ $b->rating_avg }}</span>
                                    <span class="text-ink-400">({{ $b->ratings_count }})</span>
                                </div>
                            @endif
                        </div>
                        <span class="text-[11px] font-extrabold text-coral-600 pill-coral px-2 py-1 rounded-full shrink-0">
                            {{ $b->distance_km < 1 ? round($b->distance_km * 1000).' م' : round($b->distance_km, 1).' كم' }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</div>

@push('scripts')
<script>
document.querySelector('[data-geo-go]')?.addEventListener('click', () => {
    if (!navigator.geolocation) { alert('المتصفح ده مش داعم تحديد الموقع.'); return; }
    const btn = document.querySelector('[data-geo-go]');
    btn.disabled = true; btn.textContent = 'جاري التحديد…';
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const u = new URL(window.location.href);
            u.searchParams.set('lat', pos.coords.latitude.toFixed(6));
            u.searchParams.set('lng', pos.coords.longitude.toFixed(6));
            window.location.href = u.toString();
        },
        () => { btn.disabled = false; btn.textContent = 'دوّر حواليّا'; alert('مينفعش نوصل لموقعك. تأكّد إن الإذن مفعّل.'); },
        { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 }
    );
});
</script>
@endpush
@endsection
