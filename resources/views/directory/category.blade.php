@extends('layouts.app', ['title' => $meta['label'] . ' · دليل بنها'])

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950 inline-flex items-center gap-2">
            <span>{{ $meta['emoji'] }}</span>
            {{ $meta['label'] }}
        </h1>
        <span class="ms-auto text-xs text-ink-400">{{ $businesses->total() }} نشاط</span>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('directory.category', $category) }}" class="card-light p-2 mb-3 flex items-center gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="دوّر باسم النشاط…"
               class="flex-1 bg-transparent outline-0 px-3 py-2 text-ink-950 placeholder-ink-400 text-sm">
        @if($activeSubType) <input type="hidden" name="type" value="{{ $activeSubType }}"> @endif
        @if($activeZone)    <input type="hidden" name="zone" value="{{ $activeZone }}"> @endif
        <button class="btn-primary !py-2 !px-4 text-sm">دوّر</button>
    </form>

    {{-- Sub-type chips --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-3">
        <div class="flex gap-2 px-4 w-max">
            <a href="{{ route('directory.category', ['category' => $category, 'zone' => $activeZone]) }}"
               class="chip {{ ! $activeSubType ? 'chip-active' : '' }}">الكل</a>
            @foreach($subTypes as $st)
                <a href="{{ route('directory.category', ['category' => $category, 'type' => $st['key'], 'zone' => $activeZone]) }}"
                   class="chip {{ $activeSubType === $st['key'] ? 'chip-active' : '' }}">
                    <span>{{ $st['emoji'] }}</span> {{ $st['label'] }}
                    @if(($subTypeCounts[$st['key']] ?? 0) > 0)
                        <span class="opacity-60 text-xs">{{ $subTypeCounts[$st['key']] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {{-- Zone chips --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-4 pb-1">
        <div class="flex gap-2 px-4 w-max">
            <a href="{{ route('directory.category', ['category' => $category, 'type' => $activeSubType]) }}"
               class="chip {{ ! $activeZone ? 'chip-active' : '' }}">كل المناطق</a>
            @foreach($zones as $z)
                <a href="{{ route('directory.category', ['category' => $category, 'type' => $activeSubType, 'zone' => $z->id]) }}"
                   class="chip {{ $activeZone === $z->id ? 'chip-active' : '' }}">
                    {{ $z->name }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Results --}}
    <div class="space-y-2">
        @forelse($businesses as $b)
            @include('directory.partials.business-row', ['business' => $b])
        @empty
            <div class="card-light p-10 text-center">
                <div class="text-3xl mb-3">{{ $meta['emoji'] }}</div>
                <h3 class="font-extrabold text-ink-950 mb-1">مفيش نتيجة</h3>
                <p class="text-ink-500 text-sm">جرّب filter تاني أو اطلب من الإدارة تضيف نشاطك.</p>
            </div>
        @endforelse
    </div>

    @if($businesses->hasPages())
        <div class="mt-6">{{ $businesses->links() }}</div>
    @endif
</div>
@endsection
