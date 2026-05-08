@extends('layouts.app', ['title' => 'دليل بنها · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="card-orange p-5 mb-4 relative overflow-hidden">
        <div class="absolute -top-10 -end-10 w-44 h-44 rounded-full bg-white/15 blur-3xl"></div>
        <div class="relative">
            <h1 class="text-2xl md:text-3xl font-black text-white">دليل بنها</h1>
            <p class="text-white/90 text-sm mt-1">صنايعية، مطاعم، دكاترة، صيدليات، ومحلات — كل اللي تحتاجه في مدينتك.</p>
            <div class="mt-3 inline-flex items-center gap-2 text-[11px] font-bold text-white/95 bg-ink-950/30 px-3 py-1.5 rounded-full border border-white/15">
                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                {{ array_sum($counts) }} شغل موثّق
            </div>
        </div>
    </div>

    {{-- Categories grid --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        @foreach($categories as $key => $meta)
            @php $count = $counts[$key] ?? 0; @endphp
            <a href="{{ route('directory.category', $key) }}"
               class="card-light p-5 hover:-translate-y-0.5 hover:shadow-lg transition group relative overflow-hidden">
                <div class="absolute -top-4 -end-4 w-20 h-20 rounded-full opacity-20 group-hover:opacity-40 transition"
                     style="background: {{ $meta['color'] }}"></div>
                <div class="relative">
                    <div class="text-3xl mb-2">{{ $meta['emoji'] }}</div>
                    <h3 class="text-base font-extrabold text-ink-950">{{ $meta['label'] }}</h3>
                    <div class="text-xs text-ink-500 mt-1">{{ $count }} نشاط</div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- 24/7 strip --}}
    @if($is24h->isNotEmpty())
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-mint-500 animate-pulse"></span>
                مفتوحين دلوقتي ٢٤ ساعة
            </h3>
            <span class="text-ink-400 text-xs">{{ $is24h->count() }}</span>
        </div>
        <div class="overflow-x-auto scrollbar-hide -mx-4 mb-6">
            <div class="flex gap-3 px-4 w-max">
                @foreach($is24h as $b)
                    @include('directory.partials.business-mini', ['business' => $b])
                @endforeach
            </div>
        </div>
    @endif

    {{-- Featured (verified) --}}
    @if($featured->isNotEmpty())
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-2">
                <x-icon name="check" class="w-4 h-4 text-mint-700"/>
                موثّقين من بنهاوي
            </h3>
        </div>
        <div class="space-y-2">
            @foreach($featured as $b)
                @include('directory.partials.business-row', ['business' => $b])
            @endforeach
        </div>
    @endif
</div>
@endsection
