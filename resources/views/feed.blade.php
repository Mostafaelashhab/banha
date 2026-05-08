@extends('layouts.app', ['title' => 'الترند · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Compact welcome strip --}}
    <div class="card-orange p-4 mb-3 relative overflow-hidden">
        <div class="absolute -top-8 -end-8 w-32 h-32 rounded-full bg-white/15 blur-2xl"></div>
        <div class="relative flex items-center justify-between gap-3 mb-3">
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

        {{-- Quick actions --}}
        <div class="relative grid grid-cols-3 gap-2">
            <a href="{{ route('alerts.index') }}"
               class="bg-ink-950/25 backdrop-blur rounded-xl px-2.5 py-2.5 text-white border border-white/15 hover:bg-ink-950/35 transition flex flex-col items-center gap-1">
                <x-icon name="bolt" class="w-4 h-4"/>
                <span class="text-[11px] font-bold text-center leading-tight">تنبيهات</span>
            </a>
            <a href="{{ route('prices.index') }}"
               class="bg-ink-950/25 backdrop-blur rounded-xl px-2.5 py-2.5 text-white border border-white/15 hover:bg-ink-950/35 transition flex flex-col items-center gap-1">
                <x-icon name="tag" class="w-4 h-4"/>
                <span class="text-[11px] font-bold text-center leading-tight">أسعار</span>
            </a>
            <a href="{{ route('directory.index') }}"
               class="bg-ink-950/25 backdrop-blur rounded-xl px-2.5 py-2.5 text-white border border-white/15 hover:bg-ink-950/35 transition flex flex-col items-center gap-1">
                <x-icon name="stethoscope" class="w-4 h-4"/>
                <span class="text-[11px] font-bold text-center leading-tight">دليل بنها</span>
            </a>
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

    {{-- Posts --}}
    @forelse($posts as $post)
        @include('partials.post-card', ['post' => $post])
    @empty
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
    @endforelse

    @if($posts->hasPages())
        <div class="mt-6">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
