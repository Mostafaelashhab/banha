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
            <span class="w-14 h-14 rounded-2xl bg-white grid place-items-center text-3xl shrink-0">🎓</span>
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
    <div class="grid grid-cols-4 gap-2 mb-7 rise rise-1">
        @foreach([
            ['#food',     '🍽️', 'أكل'],
            ['#books',    '📚', 'كتب'],
            ['#housing',  '🏠', 'سكن'],
            ['#courses',  '✏️', 'كورسات'],
            ['#jobs',     '💼', 'وظائف'],
            ['#transport','🚌', 'مواصلات'],
            ['#used',     '📦', 'مستعمل'],
            ['#print',    '🖨️', 'مطابع'],
        ] as [$href, $em, $lbl])
            <a href="{{ $href }}" class="flex flex-col items-center gap-1.5 py-2 rounded-2xl bg-white ring-1 ring-ink-950/8 hover:ring-coral-500/40 transition">
                <span class="text-xl">{{ $em }}</span>
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
                                <div class="text-[10px] text-ink-500 mt-0.5 truncate">📍 {{ $l->zone->name }}</div>
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
                            <span class="w-10 h-10 rounded-xl bg-honey-100 text-honey-700 grid place-items-center text-lg shrink-0">💼</span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-extrabold text-ink-950 truncate">{{ $l->title }}</div>
                                @if($l->zone)
                                    <div class="text-[10px] text-ink-500 mt-0.5">📍 {{ $l->zone->name }}</div>
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
            <div class="text-4xl mb-3">🎓</div>
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
