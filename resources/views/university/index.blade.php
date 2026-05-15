@extends('layouts.app', [
    'title' => 'دليل طلاب جامعة بنها · أكل، سكن، كورسات، وظائف · بنهاوي',
    'description' => 'كل اللي محتاجه كطالب في جامعة بنها — أكل قريب من الجامعة، مكتبات وتصوير، سكن طلاب، مذكرات وكتب، كورسات، وشغل Part-time.',
    'keywords' => 'جامعة بنها, طلاب جامعة بنها, سكن طلاب بنها, كورسات بنها, أكل جامعة بنها, شغل part-time بنها',
])

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- ─── Hero ─── --}}
    <div class="rounded-3xl p-5 mb-6 relative overflow-hidden rise rise-1" style="background: #FFF6E6;">
        <div class="absolute -top-12 -end-12 w-48 h-48 rounded-full bg-honey-400/30 blur-3xl"></div>
        <div class="relative flex items-start gap-3">
            <span class="w-14 h-14 rounded-2xl bg-white text-honey-700 grid place-items-center shrink-0">
                {{-- graduation cap --}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                    <path d="M22 10v6"/>
                    <path d="M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c0 2 3 3 6 3s6-1 6-3v-5"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="text-[10px] font-extrabold text-honey-700 mb-1">للطلاب</div>
                <h1 class="text-xl font-black text-ink-950 leading-tight mb-1">دليل طلاب جامعة بنها</h1>
                <p class="text-[12px] text-ink-500 leading-snug">
                    كل اللي محتاجه قربك من الجامعة في صفحة واحدة.
                </p>
            </div>
        </div>
    </div>

    {{-- ─── Quick jumps ─── --}}
    @php
        // Each row: [anchor, svg-path-snippet, label]. SVGs use a unified 24×24
        // viewBox with currentColor stroke so they inherit the chip color.
        $jumps = [
            ['#food', 'أكل',
                '<path d="M3 2v7c0 1.66 1.34 3 3 3s3-1.34 3-3V2"/><line x1="6" y1="2" x2="6" y2="12"/><path d="M14 2v20"/><path d="M14 8c0-2 2-4 4-4v18"/>'],
            ['#books', 'كتب',
                '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>'],
            ['#housing', 'سكن',
                '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2h-4v-7H9v7H5a2 2 0 0 1-2-2z"/>'],
            ['#courses', 'كورسات',
                '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z"/>'],
            ['#jobs', 'وظائف',
                '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>'],
            ['#transport', 'مواصلات',
                '<path d="M8 6v6"/><path d="M16 6v6"/><path d="M3 14h18"/><rect x="4" y="3" width="16" height="16" rx="2"/><circle cx="8" cy="17" r="1.5"/><circle cx="16" cy="17" r="1.5"/>'],
            ['#used', 'مستعمل',
                '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>'],
            ['#print', 'مطابع',
                '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>'],
        ];
    @endphp
    <div class="grid grid-cols-4 gap-2 mb-7 rise rise-1">
        @foreach($jumps as [$href, $lbl, $path])
            <a href="{{ $href }}" class="flex flex-col items-center gap-1.5 py-2.5 rounded-2xl bg-white ring-1 ring-ink-950/8 hover:ring-coral-500/40 transition">
                <span class="w-9 h-9 rounded-full bg-honey-100 text-honey-700 grid place-items-center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        {!! $path !!}
                    </svg>
                </span>
                <span class="text-[10px] font-extrabold text-ink-950">{{ $lbl }}</span>
            </a>
        @endforeach
    </div>

    {{-- ─── Food near campus ─── --}}
    @if($nearbyFood->isNotEmpty())
        <section id="food" class="mb-7 rise rise-2">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950">أكل قريب من الجامعة</h2>
                <a href="{{ route('directory.category', 'food') }}" class="text-[11px] font-extrabold text-coral-600 hover:underline">شوف كل المطاعم ←</a>
            </div>
            <div class="biz-card-scroll">
                @foreach($nearbyFood as $b)
                    @include('directory.partials.biz-card', ['business' => $b])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Bookshops + printing ─── --}}
    @if($bookshops->isNotEmpty())
        <section id="books" class="mb-7 rise rise-3">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950">مكتبات وتصوير</h2>
            </div>
            <div class="biz-card-scroll" id="print">
                @foreach($bookshops as $b)
                    @include('directory.partials.business-mini', ['business' => $b])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Housing ─── --}}
    @if($housing->isNotEmpty())
        <section id="housing" class="mb-7 rise rise-3">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950">سكن طلاب وعقارات</h2>
                <a href="{{ route('marketplace.index', ['cat' => 'real_estate']) }}" class="text-[11px] font-extrabold text-coral-600 hover:underline">شوف الكل ←</a>
            </div>
            <div class="grid grid-cols-2 gap-2">
                @foreach($housing as $l)
                    <a href="{{ route('marketplace.show', $l) }}" class="bg-white rounded-2xl ring-1 ring-ink-950/8 overflow-hidden hover:ring-coral-500/40 transition">
                        <div class="aspect-video bg-coral-50 relative">
                            <div class="absolute inset-0 grid place-items-center text-coral-600/40">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10">
                                    <path d="M3 9 12 2l9 7v11a2 2 0 0 1-2 2h-4v-7H9v7H5a2 2 0 0 1-2-2z"/>
                                </svg>
                            </div>
                            @if($l->photo_url)
                                <img src="{{ $l->photo_url }}" alt="{{ $l->title }}" loading="lazy" class="absolute inset-0 w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="p-2.5">
                            <div class="text-[12px] font-extrabold text-ink-950 truncate">{{ $l->title }}</div>
                            <div class="text-[13px] font-black text-coral-600 mt-0.5" dir="ltr">{{ $l->priceLabel() }}</div>
                            @if($l->zone)
                                <div class="text-[10px] text-ink-500 mt-0.5 inline-flex items-center gap-1">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5 shrink-0">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    {{ $l->zone->name }}
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Books / used items ─── --}}
    @if($bookListings->isNotEmpty())
        <section id="used" class="mb-7 rise rise-3">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950">مذكرات وكتب مستعملة</h2>
                <a href="{{ route('marketplace.index', ['cat' => 'books']) }}" class="text-[11px] font-extrabold text-coral-600 hover:underline">شوف الكل ←</a>
            </div>
            <div class="grid grid-cols-2 gap-2">
                @foreach($bookListings as $l)
                    <a href="{{ route('marketplace.show', $l) }}" class="bg-white rounded-2xl ring-1 ring-ink-950/8 overflow-hidden hover:ring-coral-500/40 transition p-3">
                        <div class="text-[12px] font-extrabold text-ink-950 truncate">{{ $l->title }}</div>
                        <div class="text-[13px] font-black text-coral-600 mt-1" dir="ltr">{{ $l->priceLabel() }}</div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Courses ─── --}}
    @if($courses->isNotEmpty())
        <section id="courses" class="mb-7 rise rise-4">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950">كورسات وسناتر</h2>
            </div>
            <div class="biz-card-scroll">
                @foreach($courses as $b)
                    @include('directory.partials.business-mini', ['business' => $b])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Part-time jobs ─── --}}
    @if($jobs->isNotEmpty())
        <section id="jobs" class="mb-7 rise rise-4">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950">شغل Part-time للطلاب</h2>
                <a href="{{ route('marketplace.index', ['cat' => 'jobs']) }}" class="text-[11px] font-extrabold text-coral-600 hover:underline">شوف الكل ←</a>
            </div>
            <div class="space-y-2">
                @foreach($jobs as $l)
                    <a href="{{ route('marketplace.show', $l) }}" class="block bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 hover:ring-coral-500/40 transition">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-honey-100 text-honey-700 grid place-items-center shrink-0">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                </svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-extrabold text-ink-950 truncate">{{ $l->title }}</div>
                                @if($l->zone)
                                    <div class="text-[10px] text-ink-500 mt-0.5 inline-flex items-center gap-1">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        {{ $l->zone->name }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-[12px] font-black text-coral-600 shrink-0" dir="ltr">{{ $l->priceLabel() }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Transport ─── --}}
    @if($transport->isNotEmpty())
        <section id="transport" class="mb-7 rise rise-4">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-black text-ink-950">مواصلات</h2>
                <a href="{{ route('directory.category', 'transport') }}" class="text-[11px] font-extrabold text-coral-600 hover:underline">شوف الكل ←</a>
            </div>
            <div class="biz-card-scroll">
                @foreach($transport as $b)
                    @include('directory.partials.business-mini', ['business' => $b])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ─── Empty fallback ─── --}}
    @if($nearbyFood->isEmpty() && $bookshops->isEmpty() && $courses->isEmpty() && $housing->isEmpty() && $bookListings->isEmpty() && $jobs->isEmpty() && $transport->isEmpty())
        <div class="card-light p-8 text-center rise rise-2">
            <span class="w-14 h-14 rounded-2xl bg-honey-100 text-honey-700 grid place-items-center mx-auto mb-3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                    <path d="M22 10v6"/>
                    <path d="M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c0 2 3 3 6 3s6-1 6-3v-5"/>
                </svg>
            </span>
            <h3 class="text-base font-extrabold text-ink-950 mb-1">الدليل لسه بيتجمّع</h3>
            <p class="text-[12px] text-ink-500 max-w-xs mx-auto leading-relaxed mb-4">
                لو عندك مكان مفيد للطلاب — ضيفه على الدليل وساعد زمايلك.
            </p>
            <a href="{{ route('directory.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-coral-500 text-white text-[12px] font-extrabold hover:bg-coral-600 transition">
                ضيف نشاط
            </a>
        </div>
    @endif

</div>
@endsection
