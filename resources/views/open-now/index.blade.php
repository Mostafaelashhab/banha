@extends('layouts.app', [
    'title' => 'مفتوح دلوقتي في بنها · مطاعم، صيدليات، محلات، طوارئ · بنهاوي',
    'description' => 'كل النشاطات المفتوحة دلوقتي في بنها والقليوبية بمواعيد عمل مؤكدة — مطاعم، كافيهات، صيدليات، سوبر ماركت، وطوارئ.',
    'keywords' => 'مفتوح دلوقتي بنها, مطاعم مفتوحة بنها, صيدلية ٢٤ ساعة بنها, طوارئ بنها',
])

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- ─── Header ─── --}}
    <div class="mb-4 rise rise-1">
        <h1 class="text-2xl font-black text-ink-950 mb-1 inline-flex items-center gap-2">
            مفتوح دلوقتي في بنها
            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-mint-100 text-mint-700">
                <span class="w-1.5 h-1.5 rounded-full bg-mint-500 pulse-soft"></span> LIVE
            </span>
        </h1>
        <p class="text-[12px] text-ink-500 leading-relaxed">
            بنعرض اللي مواعيدهم مؤكدة فقط. لو شايف مكان مكتوب عليه "المواعيد غير مؤكدة" — ساعدنا نحدّثه.
        </p>
    </div>

    {{-- ─── Category quick filters ─── --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 px-4 mb-5">
        <div class="flex items-center gap-2 min-w-max">
            <a href="{{ route('open-now.index') }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-xs font-extrabold transition
                      {{ ! $cat ? 'bg-coral-500 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                الكل
            </a>
            @foreach($filters as $key => $label)
                <a href="{{ route('open-now.index', ['cat' => $key]) }}"
                   data-track-click="open_now_filter" data-cat="{{ $key }}"
                   class="shrink-0 px-4 py-1.5 rounded-full text-xs font-extrabold transition
                          {{ $cat === $key ? 'bg-coral-500 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- ─── Confirmed open ─── --}}
    @if($openConfirmed->isNotEmpty())
        <section class="mb-7 rise rise-2">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950 inline-flex items-center gap-2">
                    مواعيد مؤكدة
                    <span class="text-[10px] font-extrabold text-mint-700">({{ $openConfirmed->count() }})</span>
                </h2>
            </div>
            <div class="space-y-2">
                @foreach($openConfirmed as $b)
                    @include('open-now.partials.row', ['business' => $b, 'confirmed' => true])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Maybe-open (no hours data) ─── --}}
    @if($maybeOpen->isNotEmpty())
        <section class="mb-7 rise rise-3">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950 inline-flex items-center gap-2">
                    المواعيد غير مؤكدة
                    <span class="text-[10px] font-extrabold text-honey-700">({{ $maybeOpen->count() }})</span>
                </h2>
            </div>
            <p class="text-[11px] text-ink-500 mb-2 leading-relaxed px-1">
                النشاطات دي ما عندهاش جدول مواعيد مسجّل. لو تعرف مواعيدها، بلّغنا.
            </p>
            <div class="space-y-2">
                @foreach($maybeOpen as $b)
                    @include('open-now.partials.row', ['business' => $b, 'confirmed' => false])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Empty state ─── --}}
    @if($openConfirmed->isEmpty() && $maybeOpen->isEmpty())
        <div class="card-light p-10 text-center rise rise-2">
            <div class="text-5xl mb-3">🕐</div>
            <h3 class="text-base font-extrabold text-ink-950 mb-1">مفيش نتائج مؤكدة مفتوحة دلوقتي</h3>
            <p class="text-[12px] text-ink-500 max-w-xs mx-auto leading-relaxed mb-4">
                جرّب تصنيف تاني، أو بلّغنا عن مكان تعرف مواعيده.
            </p>
            <a href="{{ route('directory.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-coral-500 text-white text-[12px] font-extrabold hover:bg-coral-600 transition">
                ضيف نشاط
            </a>
        </div>
    @endif

</div>
@endsection
