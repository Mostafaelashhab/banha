@extends('layouts.app', [
    'title' => 'أرقام طوارئ بنها والقليوبية · إسعاف، مطافي، شرطة، مستشفيات · بنهاوي',
    'description' => 'كل أرقام الطوارئ في بنها والقليوبية في صفحة واحدة — إسعاف، شرطة، مطافي، مستشفيات، صيدليات نوبتجية، كهربا، مياه، غاز، وسباك/كهربائي طوارئ.',
    'keywords' => 'طوارئ بنها, أرقام طوارئ القليوبية, إسعاف بنها, مستشفيات بنها, صيدلية ٢٤ ساعة, سباك طوارئ بنها',
])

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- ─── Header ─── --}}
    <div class="mb-5 rise rise-1">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blush-100 text-blush-600 text-[11px] font-extrabold mb-2">
            <x-icon name="bolt" class="w-3 h-3"/>
            طوارئ
        </div>
        <h1 class="text-2xl font-black text-ink-950 leading-tight mb-1">أرقام طوارئ بنها والقليوبية</h1>
        <p class="text-[12px] text-ink-500 leading-relaxed">
            احفظ الصفحة دي على الموبايل — كل رقم بزر اتصال واحد.
        </p>
    </div>

    {{-- ─── National hotlines (always shown) ─── --}}
    <section class="mb-6 rise rise-2">
        <h2 class="text-base font-black text-ink-950 mb-3 px-1">أرقام الطوارئ القومية</h2>
        <div class="grid grid-cols-2 gap-2">
            @php
                $toneMap = [
                    'coral' => 'bg-coral-50 text-coral-600',
                    'mint'  => 'bg-mint-100 text-mint-700',
                    'blush' => 'bg-blush-100 text-blush-600',
                    'honey' => 'bg-honey-100 text-honey-700',
                ];
            @endphp
            @foreach($hotlines as $h)
                <a href="tel:{{ $h['phone'] }}"
                   class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 flex items-center gap-3 hover:ring-coral-500/40 transition">
                    <span class="w-11 h-11 rounded-2xl {{ $toneMap[$h['tone']] ?? 'bg-cream-100' }} grid place-items-center text-xl shrink-0">
                        {{ $h['emoji'] }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-bold text-ink-500">{{ $h['label'] }}</div>
                        <div class="text-lg font-black text-ink-950 leading-none mt-0.5" dir="ltr">{{ $h['phone'] }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ─── Local emergency businesses ─── --}}
    @if($emergency->isNotEmpty())
        <section class="mb-6 rise rise-3">
            <h2 class="text-base font-black text-ink-950 mb-3 px-1">طوارئ محلية في بنها والقليوبية</h2>
            <div class="space-y-2">
                @foreach($emergency as $b)
                    @include('emergency.partials.row', ['business' => $b])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── 24h pharmacies ─── --}}
    @if($pharmacies24h->isNotEmpty())
        <section class="mb-6 rise rise-3">
            <h2 class="text-base font-black text-ink-950 mb-3 px-1 inline-flex items-center gap-2">
                صيدليات ٢٤ ساعة
                <span class="text-[10px] font-extrabold text-mint-700 bg-mint-100 px-2 py-0.5 rounded-full">شغّالة دلوقتي</span>
            </h2>
            <div class="space-y-2">
                @foreach($pharmacies24h as $b)
                    @include('emergency.partials.row', ['business' => $b])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Emergency craftsmen (grouped by sub_type) ─── --}}
    @if($emergencyCraftsmen->isNotEmpty())
        <section class="mb-6 rise rise-4">
            <h2 class="text-base font-black text-ink-950 mb-3 px-1">صنايعية طوارئ</h2>
            @foreach($emergencyCraftsmen as $sub => $rows)
                @php
                    $meta = \App\Models\Business::SUB_TYPES[$sub] ?? ['label' => $sub, 'emoji' => '🔧'];
                @endphp
                <div class="mb-3">
                    <div class="text-[11px] font-extrabold text-ink-500 mb-2 px-1 inline-flex items-center gap-1.5">
                        <span>{{ $meta['emoji'] }}</span>
                        <span>{{ $meta['label'] }}</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($rows as $b)
                            @include('emergency.partials.row', ['business' => $b])
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>
    @endif

    {{-- ─── Empty fallback (no local data yet) ─── --}}
    @if($emergency->isEmpty() && $pharmacies24h->isEmpty() && $emergencyCraftsmen->isEmpty())
        <div class="card-light p-6 text-center mb-4 rise rise-4">
            <p class="text-[12px] text-ink-500 leading-relaxed mb-3">
                لسه مفيش طوارئ محلية مسجّلة في الدليل — اعتمد على الأرقام القومية فوق.
            </p>
            <a href="{{ route('directory.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-coral-50 text-coral-600 text-[12px] font-extrabold hover:bg-coral-100 transition">
                ساعدنا — ضيف رقم طوارئ
            </a>
        </div>
    @endif

    {{-- ─── Disclaimer ─── --}}
    <p class="text-[11px] text-ink-400 text-center leading-relaxed mt-4 mb-2 px-4">
        في حالات الخطر الحقيقي، اتصل فوراً بـ <a href="tel:123" class="font-extrabold text-blush-600">123</a>
        (إسعاف) أو <a href="tel:122" class="font-extrabold text-blush-600">122</a> (شرطة).
    </p>

</div>
@endsection
