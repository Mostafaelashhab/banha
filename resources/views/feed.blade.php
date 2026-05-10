@extends('layouts.app', ['title' => 'بنهاوي · دليلك الكامل لمدينة بنها'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Search bar — subtle border, clean --}}
    <a href="{{ route('search') }}"
       class="flex items-center gap-3 mb-5 px-4 py-3 rounded-2xl bg-white ring-1 ring-ink-950/8 hover:ring-ink-950/15 transition">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-5 h-5 text-ink-400 shrink-0">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <span class="text-sm text-ink-400">دوّر على نشاط، صنايعي، أو منيو…</span>
    </a>

    {{-- Map CTA — minimal banner, NOT a fake map (the real map is one click away) --}}
    <a href="{{ route('directory.map') }}"
       class="flex items-center gap-3 mb-6 px-4 py-3.5 rounded-2xl bg-cream-200/70 hover:bg-cream-200 transition group">
        <span class="w-11 h-11 rounded-2xl bg-coral-500 grid place-items-center text-white shrink-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                <line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/>
            </svg>
        </span>
        <div class="flex-1 min-w-0">
            <div class="text-ink-950 font-extrabold text-sm">شوف بنها على الخريطة</div>
            <div class="text-ink-500 text-xs mt-0.5">كل النشاطات بمكانها</div>
        </div>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-5 h-5 text-ink-400 shrink-0 group-hover:text-coral-600 group-hover:-translate-x-1 transition">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
    </a>

    {{-- Categories — compact horizontal scroll --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-6">
        <div class="flex gap-2.5 px-4 w-max">
            @foreach(\App\Models\Business::CATEGORIES as $catKey => $cat)
                <a href="{{ route('directory.category', $catKey) }}"
                   class="flex flex-col items-center gap-1.5 shrink-0 w-16 group">
                    <span class="w-12 h-12 rounded-2xl grid place-items-center transition group-hover:scale-105"
                          style="background: {{ $cat['color'] }}14; color: {{ $cat['color'] }};">
                        <x-icon :name="$cat['icon'] ?? 'bag'" class="w-5 h-5"/>
                    </span>
                    <span class="text-[10px] font-bold text-ink-950 leading-tight text-center">{{ $cat['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Featured / top-rated businesses (the main content) --}}
    @php
        $featured = \App\Models\Business::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_verified', true)->orWhere('promoted_until', '>', now());
            })
            ->with('zone:id,name')
            ->orderByRaw('CASE WHEN promoted_until > NOW() THEN 0 ELSE 1 END')
            ->orderByDesc('rating_avg')
            ->limit(10)
            ->get();
    @endphp
    @if($featured->isNotEmpty())
        <section class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-extrabold text-ink-950">الأكتر تقييم</h2>
                <a href="{{ route('directory.index') }}" class="text-xs font-bold text-coral-600">الكل ←</a>
            </div>
            <div class="space-y-3">
                @foreach($featured as $b)
                    @include('directory.partials.business-row', ['business' => $b])
                @endforeach
            </div>
        </section>
    @endif

    {{-- Community posts — secondary --}}
    @if($items->isNotEmpty())
        <section>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-extrabold text-ink-950">من المجتمع</h2>
                @auth
                    <a href="{{ route('posts.create') }}" class="text-xs font-bold text-coral-600">شارك ←</a>
                @endauth
            </div>
            <div id="feed-list" data-infinite-scroll>
                @include('partials.feed-page', ['items' => $items, 'paginator' => $paginator, 'userVotes' => $userVotes])
            </div>
        </section>
    @endif
</div>
@endsection
