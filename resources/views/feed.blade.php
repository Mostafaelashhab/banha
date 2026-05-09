@extends('layouts.app', ['title' => 'الترند · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Stories strip (FB-style: avatar circles with ring) --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-3">
        <div class="flex gap-3 px-4 w-max">
            @auth
                {{-- "+ Add story" tile --}}
                <a href="{{ route('stories.create') }}" class="flex flex-col items-center gap-1 shrink-0 w-16">
                    <div class="w-16 h-16 rounded-full bg-cream-100 border-2 border-dashed border-coral-500 grid place-items-center text-coral-500">
                        <x-icon name="plus" class="w-6 h-6"/>
                    </div>
                    <span class="text-[10px] font-bold text-ink-500 truncate w-full text-center">ستوري</span>
                </a>
            @endauth

            @forelse($stories as $userId => $userStories)
                @php $latest = $userStories->first(); @endphp
                <a href="{{ route('stories.show', $latest) }}" class="flex flex-col items-center gap-1 shrink-0 w-16">
                    <div class="w-16 h-16 rounded-full p-0.5 bg-gradient-to-tr from-coral-500 via-honey-500 to-coral-300">
                        <div class="w-full h-full rounded-full bg-white p-0.5">
                            <img src="{{ $latest->image_url }}" alt="" loading="lazy" class="w-full h-full object-cover rounded-full">
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-ink-500 truncate w-full text-center">{{ $latest->user->username }}</span>
                </a>
            @empty
                @guest
                    <a href="{{ route('stories.index') }}" class="flex flex-col items-center gap-1 shrink-0 w-16">
                        <div class="w-16 h-16 rounded-full bg-cream-100 grid place-items-center text-ink-400">
                            <x-icon name="flame" class="w-6 h-6"/>
                        </div>
                        <span class="text-[10px] font-bold text-ink-400 truncate w-full text-center">مفيش</span>
                    </a>
                @endguest
            @endforelse
        </div>
    </div>

    {{-- Composer (FB-style: avatar + "what's on your mind") --}}
    @auth
        <a href="{{ route('posts.create') }}" class="card-light p-3 mb-3 flex items-center gap-3 hover:bg-cream-100 transition">
            <x-avatar :user="auth()->user()" size="md"/>
            <span class="flex-1 text-sm text-ink-400">إيه اللي بتفكر فيه يا {{ auth()->user()->username }}؟</span>
        </a>
    @endauth

    {{-- 4 main quick actions (FB-style: evenly spaced row, not slider) --}}
    <div class="card-light p-2 mb-3 grid grid-cols-4 gap-1">
        @php
            $main = [
                ['route' => route('marketplace.index'), 'icon' => 'tag',     'label' => 'سوق',     'color' => 'text-coral-600'],
                ['route' => route('feed.following'),    'icon' => 'heart',   'label' => 'متابعينك','color' => 'text-blush-500'],
                ['route' => route('events.index'),      'icon' => 'bell',    'label' => 'أحداث',  'color' => 'text-mint-700'],
                ['route' => route('directory.nearby'),  'icon' => 'map-pin', 'label' => 'حواليّا','color' => 'text-honey-700'],
            ];
        @endphp
        @foreach($main as $m)
            <a href="{{ $m['route'] }}" class="flex flex-col items-center gap-1 py-2 rounded-xl hover:bg-cream-100 transition">
                <span class="{{ $m['color'] }}">
                    <x-icon :name="$m['icon']" class="w-5 h-5"/>
                </span>
                <span class="text-[10px] font-bold text-ink-950">{{ $m['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- Secondary chips (overflow menu) --}}
    <details class="mb-3">
        <summary class="card-light p-3 text-sm font-bold text-ink-500 cursor-pointer list-none flex items-center justify-between hover:bg-cream-100 transition">
            <span class="inline-flex items-center gap-2">
                <x-icon name="more" class="w-4 h-4"/>
                المزيد
            </span>
            <x-icon name="chevron-down" class="w-4 h-4"/>
        </summary>
        <div class="grid grid-cols-4 gap-2 mt-2">
            @php
                $extras = [
                    ['route' => route('stories.index'),    'icon' => 'flame',       'label' => 'ستوريز'],
                    ['route' => route('users.index'),      'icon' => 'user',        'label' => 'يوزرز'],
                    ['route' => route('alerts.index'),     'icon' => 'bolt',        'label' => 'تنبيهات'],
                    ['route' => route('prices.index'),     'icon' => 'tag',         'label' => 'أسعار'],
                    ['route' => route('directory.index'),  'icon' => 'bag',         'label' => 'الدليل'],
                    ['route' => route('zones'),            'icon' => 'map',         'label' => 'المناطق'],
                    ['route' => route('hashtag.trending'), 'icon' => 'flame',       'label' => 'هاشتاجات'],
                    ['route' => route('bookmark.index'),   'icon' => 'heart',       'label' => 'محفوظاتي'],
                ];
            @endphp
            @foreach($extras as $c)
                <a href="{{ $c['route'] }}" class="card-light p-3 flex flex-col items-center gap-1 hover:bg-cream-100 transition">
                    <x-icon :name="$c['icon']" class="w-4 h-4 text-coral-600"/>
                    <span class="text-[10px] font-bold text-ink-950">{{ $c['label'] }}</span>
                </a>
            @endforeach
        </div>
    </details>

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
