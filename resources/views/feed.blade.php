@extends('layouts.app', ['title' => 'الترند · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Slim welcome strip --}}
    <div class="card-orange p-4 mb-3 relative overflow-hidden">
        <div class="absolute -top-8 -end-8 w-32 h-32 rounded-full bg-white/15 blur-2xl"></div>
        <div class="relative flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="text-white/80 text-[11px]">أهلاً يا</div>
                <div class="text-white text-lg font-black truncate">{{ auth()->user()->username }}</div>
                @if(auth()->user()->zone)
                    <div class="text-white/85 text-[11px] mt-0.5 inline-flex items-center gap-1">
                        <x-icon name="map-pin" class="w-3 h-3"/>
                        {{ auth()->user()->zone->name }}
                    </div>
                @endif
            </div>
            <a href="{{ route('posts.create') }}" class="btn-dark !py-2 !px-4 text-sm shrink-0">
                <x-icon name="plus" class="w-4 h-4"/>
                بوست
            </a>
        </div>
    </div>

    {{-- Quick actions (horizontal scroll) --}}
    @php
        $quickActions = [
            ['url' => route('alerts.index'),                   'icon' => 'bolt',        'label' => 'تنبيهات',     'tone' => 'blush'],
            ['url' => route('prices.index'),                   'icon' => 'tag',         'label' => 'الأسعار',      'tone' => 'mint'],
            ['url' => route('directory.index'),                'icon' => 'bag',         'label' => 'الدليل',       'tone' => 'coral'],
            ['url' => route('zones'),                          'icon' => 'map-pin',     'label' => 'المناطق',     'tone' => 'honey'],
            ['url' => route('discover'),                       'icon' => 'flame',       'label' => 'اكتشف',       'tone' => 'coral'],
            ['url' => route('directory.category', 'medical'),  'icon' => 'stethoscope', 'label' => 'دكاترة',      'tone' => 'mint'],
            ['url' => route('directory.category', 'food'),     'icon' => 'utensils',    'label' => 'مطاعم',       'tone' => 'coral'],
            ['url' => route('directory.category', 'craftsmen'),'icon' => 'more',        'label' => 'صنايعية',     'tone' => 'honey'],
            ['url' => route('directory.category', 'shops'),    'icon' => 'cart',        'label' => 'محلات',       'tone' => 'coral'],
            ['url' => route('alerts.create'),                  'icon' => 'plus',        'label' => 'بلّغ',         'tone' => 'blush'],
        ];
    @endphp

    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-3">
        <div class="flex gap-2.5 px-4 w-max">
            @foreach($quickActions as $a)
                <a href="{{ $a['url'] }}" class="quick-tile">
                    <span class="quick-tile-icon pill-{{ $a['tone'] }}">
                        <x-icon :name="$a['icon']" class="w-5 h-5"/>
                    </span>
                    <span class="quick-tile-label">{{ $a['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Sticky filter bar --}}
    <div class="sticky top-14 bg-cream-100/90 backdrop-blur z-30 -mx-4 px-4 pt-2 pb-3 mb-3 border-b border-ink-950/5 space-y-2">
        @php $zoneParam = $activeZone ? ['zone' => $activeZone] : []; @endphp

        {{-- Tabs --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('feed', $zoneParam + ['tab' => 'hot']) }}"
               class="chip chip-coral {{ $tab === 'hot' ? 'chip-active' : '' }}">
                <x-icon name="flame" class="w-3.5 h-3.5"/> ترند
            </a>
            <a href="{{ route('feed', $zoneParam + ['tab' => 'new']) }}"
               class="chip {{ $tab === 'new' ? 'chip-active' : '' }}">
                جديد
            </a>
        </div>

        {{-- Zone chips (horizontal scroll) --}}
        <div class="overflow-x-auto scrollbar-hide -mx-4">
            <div class="flex gap-2 w-max px-4">
                <a href="{{ route('feed', ['tab' => $tab]) }}"
                   class="chip {{ ! $activeZone ? 'chip-active' : '' }}">
                    كل المناطق
                </a>
                @foreach($zones as $zone)
                    <a href="{{ route('feed', ['tab' => $tab, 'zone' => $zone->id]) }}"
                       class="chip {{ $activeZone === $zone->id ? 'chip-active' : '' }}">
                        {{ $zone->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Posts (infinite scroll) --}}
    @if($posts->isEmpty())
        <div class="card-light p-10 text-center">
            <div class="icon-tile mx-auto mb-4 text-coral-600 w-16 h-16">
                <x-icon name="flame" class="w-7 h-7"/>
            </div>
            <h3 class="text-xl font-extrabold text-ink-950 mb-1">لسه مفيش بوستات</h3>
            <p class="text-ink-500 text-sm">كن أول واحد يبدأ — اكتب بوست وشارك حاجة بتحصل في حيك.</p>
            <a href="{{ route('posts.create') }}" class="btn-primary mt-5">
                ابدأ أول بوست
                <x-icon name="arrow-left" class="w-4 h-4"/>
            </a>
        </div>
    @else
        <div id="feed-list" data-infinite-scroll>
            @include('partials.feed-page', ['posts' => $posts, 'userVotes' => $userVotes])
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
