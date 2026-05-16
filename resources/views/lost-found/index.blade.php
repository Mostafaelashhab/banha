@extends('layouts.app', [
    'title' => 'مفقودات بنها · حاجات ضايعة وملقية في بنها · بنهاوي',
    'description' => 'لوحة الحاجات الضايعة والملقية في بنها — لقيت موبايل، شنطة، مفاتيح؟ أو ضاع منك شيء؟ انشرها هنا.',
    'keywords' => 'مفقودات بنها, حاجات ضايعة بنها, لقطات بنها, لقيت موبايل بنها',
])

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- ─── Header ─── --}}
    <div class="mb-4 rise rise-1">
        <h1 class="text-2xl font-black text-ink-950 leading-tight mb-1">مفقودات بنها</h1>
        <p class="text-[12px] text-ink-500 leading-relaxed">
            ضاع منك حاجة أو لقيت حاجة في الشارع/المواصلات/الكافيه؟ انشرها هنا — أهل بنها كلهم بيشوفوا.
        </p>
    </div>

    {{-- ─── Tabs ─── --}}
    <div class="flex gap-2 overflow-x-auto scrollbar-hide mb-4 -mx-4 px-4">
        @foreach([
            'all'   => ['الكل',  $counts['all']],
            'lost'  => ['ضايع',  $counts['lost']],
            'found' => ['ملقي',  $counts['found']],
        ] as $key => [$label, $count])
            <a href="{{ route('lost-found.index', ['tab' => $key]) }}"
               class="shrink-0 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-extrabold transition
                      {{ $tab === $key ? 'bg-coral-500 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                {{ $label }}
                <span class="text-[9px] {{ $tab === $key ? 'text-white/70' : 'text-ink-400' }} font-bold">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    {{-- ─── Post-it CTA ─── --}}
    <a href="{{ Auth::check() ? route('marketplace.create') : route('login') }}"
       class="block mb-4 rounded-2xl p-4 bg-honey-50 ring-1 ring-honey-500/30 hover:ring-honey-500/50 transition">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-honey-500 text-ink-950 grid place-items-center shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-extrabold text-ink-950">عندك حاجة ضاعت أو لقيتها؟</div>
                <div class="text-[11px] text-ink-500 mt-0.5">انشرها وكلّم أصحابها مجاناً</div>
            </div>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4 text-honey-700 rtl:rotate-180">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </div>
    </a>

    {{-- ─── List ─── --}}
    @if($items->isEmpty())
        <div class="card-light p-10 text-center">
            <span class="w-14 h-14 rounded-2xl bg-cream-100 text-ink-400 grid place-items-center mx-auto mb-3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </span>
            <h3 class="text-base font-extrabold text-ink-950 mb-1">مفيش بوستات لسه</h3>
            <p class="text-[12px] text-ink-500 max-w-xs mx-auto">
                أول واحد ينشر هيلاقي أهل بنها كلهم بيشوفوا منشوره.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach($items as $l)
                @php
                    $km = $l->kindMeta();
                    $isLost = $l->kind === 'lost';
                @endphp
                <a href="{{ route('marketplace.show', $l) }}"
                   class="bg-white rounded-2xl ring-1 ring-ink-950/8 overflow-hidden hover:ring-coral-500/40 transition">
                    <div class="aspect-video bg-cream-100 relative">
                        @if($l->photo_url)
                            <img src="{{ $l->photo_url }}" alt="{{ $l->title }}" loading="lazy"
                                 class="absolute inset-0 w-full h-full object-cover">
                        @else
                            <div class="absolute inset-0 grid place-items-center text-ink-300">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-10 h-10">
                                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                            </div>
                        @endif
                        <span class="absolute top-1.5 start-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold
                                     {{ $isLost ? 'bg-blush-500 text-white' : 'bg-honey-500 text-ink-950' }}">
                            @if($isLost)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="w-2.5 h-2.5">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                                ضايع
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                ملقي
                            @endif
                        </span>
                    </div>
                    <div class="p-3">
                        <div class="text-[13px] font-extrabold text-ink-950 line-clamp-2 leading-snug mb-1">{{ $l->title }}</div>
                        <div class="text-[10px] text-ink-500 inline-flex items-center gap-1.5 flex-wrap">
                            @if($l->zone)
                                <span class="inline-flex items-center gap-0.5">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    {{ $l->zone->name }}
                                </span>
                                <span>·</span>
                            @endif
                            <span>{{ $l->created_at->diffForHumans(short: true) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
