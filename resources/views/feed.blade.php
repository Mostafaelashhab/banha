@extends('layouts.app', ['title' => 'الترند · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Compact action chips row (replaces orange welcome + 4 tiles) --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-3">
        <div class="flex gap-2 px-4 w-max">
            @php
                $chips = [
                    ['route' => route('marketplace.index'),'icon' => 'tag',         'label' => 'بيع وشراء','tone' => 'coral'],
                    ['route' => route('alerts.index'),    'icon' => 'bolt',        'label' => 'تنبيهات',  'tone' => 'blush'],
                    ['route' => route('prices.index'),    'icon' => 'tag',         'label' => 'أسعار',    'tone' => 'mint'],
                    ['route' => route('directory.index'), 'icon' => 'bag',         'label' => 'الدليل',   'tone' => 'coral'],
                    ['route' => route('zones'),           'icon' => 'map-pin',     'label' => 'المناطق', 'tone' => 'honey'],
                    ['route' => route('hashtag.trending'),'icon' => 'flame',       'label' => 'هاشتاجات', 'tone' => 'coral'],
                    ['route' => route('bookmark.index'),  'icon' => 'heart',       'label' => 'محفوظاتي','tone' => 'blush'],
                    ['route' => route('directory.category','food'),     'icon' => 'utensils',    'label' => 'مطاعم',   'tone' => 'coral'],
                    ['route' => route('directory.category','medical'),  'icon' => 'stethoscope', 'label' => 'دكاترة',  'tone' => 'mint'],
                    ['route' => route('directory.category','craftsmen'),'icon' => 'more',        'label' => 'صنايعية','tone' => 'honey'],
                ];
            @endphp
            @foreach($chips as $c)
                <a href="{{ $c['route'] }}" class="action-chip">
                    <span class="action-chip-icon pill-{{ $c['tone'] }}">
                        <x-icon :name="$c['icon']" class="w-3.5 h-3.5"/>
                    </span>
                    <span>{{ $c['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Tiny inline filter row --}}
    @php
        $activeZoneName = $activeZone ? optional($zones->firstWhere('id', $activeZone))->name : null;
        $tabLabel = $tab === 'new' ? 'جديد' : 'ترند';
    @endphp
    <div class="flex items-center justify-between mb-3 text-sm">
        <div class="text-ink-500">
            {{ $tabLabel }}
            @if($activeZoneName)
                · <span class="text-ink-950 font-bold">{{ $activeZoneName }}</span>
            @endif
        </div>
        <button type="button" data-feed-filter
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-ink-950/8 text-ink-500 hover:text-ink-950 hover:bg-cream-200 transition text-xs font-bold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                <line x1="4" y1="6" x2="20" y2="6"/>
                <line x1="7" y1="12" x2="17" y2="12"/>
                <line x1="10" y1="18" x2="14" y2="18"/>
            </svg>
            فلتر
        </button>
    </div>

    {{-- Hidden filter sheet (template) --}}
    <template id="feed-filter-template">
        <div class="p-5">
            <h3 class="text-lg font-extrabold text-ink-950 mb-4">فلترة الفيد</h3>

            <div class="mb-5">
                <div class="text-xs font-bold text-ink-500 mb-2">الترتيب</div>
                <div class="flex gap-2">
                    <a href="{{ route('feed', ($activeZone ? ['zone' => $activeZone] : []) + ['tab' => 'hot']) }}"
                       class="chip chip-coral {{ $tab === 'hot' ? 'chip-active' : '' }}">
                        🔥 ترند
                    </a>
                    <a href="{{ route('feed', ($activeZone ? ['zone' => $activeZone] : []) + ['tab' => 'new']) }}"
                       class="chip {{ $tab === 'new' ? 'chip-active' : '' }}">
                        جديد
                    </a>
                </div>
            </div>

            <div class="mb-5">
                <div class="text-xs font-bold text-ink-500 mb-2">المنطقة</div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('feed', ['tab' => $tab]) }}"
                       class="chip {{ ! $activeZone ? 'chip-active' : '' }}">كل المناطق</a>
                    @foreach($zones as $zone)
                        <a href="{{ route('feed', ['tab' => $tab, 'zone' => $zone->id]) }}"
                           class="chip {{ $activeZone === $zone->id ? 'chip-active' : '' }}">
                            {{ $zone->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <button type="button" class="btn-ghost w-full justify-center" data-close>تمام</button>
        </div>
    </template>

    {{-- Unified feed (posts + alerts + businesses ads + prices) — infinite scroll --}}
    @if($items->isEmpty())
        <div class="card-light p-10 text-center">
            <div class="icon-tile mx-auto mb-4 text-coral-600 w-16 h-16">
                <x-icon name="flame" class="w-7 h-7"/>
            </div>
            <h3 class="text-xl font-extrabold text-ink-950 mb-1">مفيش حاجة في الفيد</h3>
            <p class="text-ink-500 text-sm">كن أول واحد يبدأ.</p>
            <a href="{{ route('posts.create') }}" class="btn-primary mt-5">
                ابدأ أول بوست
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </a>
        </div>
    @else
        <div id="feed-list" data-infinite-scroll>
            @include('partials.feed-page', ['items' => $items, 'paginator' => $paginator, 'userVotes' => $userVotes])
        </div>

        {{-- Loader spinner --}}
        <div data-feed-loader class="hidden text-center py-6">
            <div class="inline-flex items-center gap-2 text-ink-500 text-sm">
                <span class="w-4 h-4 rounded-full border-2 border-coral-500/30 border-t-coral-500 animate-spin"></span>
                لسه شوية…
            </div>
        </div>
        <div data-feed-done class="hidden text-center py-6 text-ink-400 text-xs">
            خلاص — وصلت لآخر البوستات 🎉
        </div>
    @endif
</div>
@endsection
