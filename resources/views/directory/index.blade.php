@extends('layouts.app', ['title' => 'دليل بنها · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="card-orange p-5 mb-4 relative overflow-hidden">
        <div class="absolute -top-10 -end-10 w-44 h-44 rounded-full bg-white/15 blur-3xl"></div>
        <div class="relative flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-2xl md:text-3xl font-black text-white">دليل بنها</h1>
                <p class="text-white/90 text-sm mt-1">صنايعية، مطاعم، دكاترة، صيدليات، ومحلات.</p>
                <div class="mt-3 inline-flex items-center gap-2 text-[11px] font-bold text-white/95 bg-ink-950/30 px-3 py-1.5 rounded-full border border-white/15">
                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                    {{ array_sum($counts) }} نشاط
                </div>
            </div>
            <a href="{{ route('directory.create') }}" class="btn-dark !py-2 !px-4 text-sm shrink-0">
                <x-icon name="plus" class="w-4 h-4"/>
                ضيف نشاطك
            </a>
        </div>
    </div>

    {{-- "Are you a craftsman/owner?" CTA strip --}}
    <a href="{{ route('directory.create') }}"
       class="card-light p-4 mb-3 flex items-center gap-3 hover:-translate-y-0.5 hover:shadow-lg transition border-2 border-coral-500/15 bg-coral-50">
        <span class="w-11 h-11 rounded-2xl pill-coral grid place-items-center shrink-0">
            <x-icon name="bag" class="w-5 h-5"/>
        </span>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-extrabold text-ink-950">إنت صنايعي أو صاحب نشاط؟</div>
            <div class="text-[11px] text-ink-500 mt-0.5">سجّل نشاطك مجاناً وخلّي بنها كلها تعرفك</div>
        </div>
        <x-icon name="arrow-left" class="w-4 h-4 text-coral-600 shrink-0"/>
    </a>

    {{-- Map CTA --}}
    <a href="{{ route('directory.map') }}"
       class="block mb-5 p-4 rounded-2xl text-white relative overflow-hidden hover:scale-[1.01] transition shadow-lg"
       style="background: linear-gradient(135deg, #1FA857 0%, #10B981 50%, #059669 100%);">
        <div class="absolute -top-4 -end-4 w-32 h-32 rounded-full bg-white/10 blur-2xl"></div>
        <div class="relative flex items-center gap-3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7 shrink-0">
                <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                <line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/>
            </svg>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-extrabold">خريطة بنها</div>
                <div class="text-[11px] text-white/85 mt-0.5">كل النشاطات على خريطة واحدة</div>
            </div>
        </div>
    </a>

    {{-- Categories — clean minimal grid (color tint, not solid) --}}
    <div class="grid grid-cols-3 gap-2 mb-6">
        @foreach($categories as $key => $meta)
            @php $count = $counts[$key] ?? 0; @endphp
            <a href="{{ route('directory.category', $key) }}"
               class="bg-white hover:bg-cream-100 rounded-2xl p-3 transition text-center">
                <span class="w-12 h-12 rounded-2xl mx-auto grid place-items-center mb-2"
                      style="background: {{ $meta['color'] }}14; color: {{ $meta['color'] }};">
                    <x-icon :name="$meta['icon'] ?? 'bag'" class="w-6 h-6"/>
                </span>
                <h3 class="text-[12px] font-extrabold text-ink-950 leading-tight">{{ $meta['label'] }}</h3>
                <div class="text-[10px] text-ink-400 mt-0.5">{{ $count }} نشاط</div>
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
        <div class="space-y-3">
            @foreach($featured as $b)
                @include('directory.partials.business-row', ['business' => $b])
            @endforeach
        </div>
    @endif
</div>
@endsection
