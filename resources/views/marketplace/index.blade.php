@extends('layouts.app', ['title' => 'بيع وشراء · بنهاوي'])

@push('head')
<style>
    /* Kind segmented control: each chip uses its own tone */
    .kind-chip {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 12px 6px;
        border-radius: 18px;
        font-size: 11px;
        font-weight: 800;
        text-align: center;
        background: #fff;
        border: 1.5px solid rgba(11, 11, 12, .06);
        color: #5C5C66;
        transition: transform .15s, box-shadow .15s, border-color .15s;
        position: relative;
    }
    .kind-chip:hover { transform: translateY(-2px); border-color: var(--kind-color); }
    .kind-chip .kind-icon {
        width: 32px; height: 32px;
        border-radius: 12px;
        display: grid; place-items: center;
        background: color-mix(in srgb, var(--kind-color) 12%, transparent);
        color: var(--kind-color);
        transition: all .15s;
    }
    .kind-chip.is-active {
        background: var(--kind-color);
        border-color: var(--kind-color);
        color: #fff;
        box-shadow: 0 8px 18px -6px var(--kind-color);
    }
    .kind-chip.is-active .kind-icon { background: rgba(255,255,255,.22); color: #fff; }
    .kind-chip .kind-count {
        position: absolute;
        top: 6px; inset-inline-end: 6px;
        background: rgba(11, 11, 12, .06);
        border-radius: 999px;
        padding: 1px 6px;
        font-size: 9px;
        font-weight: 900;
        color: #5C5C66;
    }
    .kind-chip.is-active .kind-count { background: rgba(255,255,255,.25); color: #fff; }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto pb-20">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-4">
        <span class="w-10 h-10 rounded-2xl bg-coral-100 text-coral-600 grid place-items-center shrink-0">
            <x-icon name="tag" class="w-5 h-5"/>
        </span>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-black text-ink-950 leading-tight">بيع وشراء</h1>
            <p class="text-[11px] text-ink-500">{{ array_sum($kindCounts) }} إعلان نشط في القليوبية</p>
        </div>
        @auth
            <a href="{{ route('marketplace.create') }}" class="inline-flex items-center gap-1 bg-coral-500 hover:bg-coral-600 text-white text-xs font-extrabold rounded-full px-3 py-2 transition shadow-sm">
                <x-icon name="plus" class="w-3.5 h-3.5"/>
                إعلان
            </a>
        @endauth
    </div>

    {{-- Kind segmented control --}}
    <div class="grid grid-cols-4 gap-2 mb-3">
        @php $colorByTone = ['coral' => '#FF7A4D', 'mint' => '#1FA857', 'blush' => '#E64646', 'honey' => '#FFB85C']; @endphp
        @foreach($kinds as $k => $meta)
            @php $col = $colorByTone[$meta['tone']] ?? '#FF7A4D'; @endphp
            <a href="{{ route('marketplace.index', ['kind' => $k]) }}"
               class="kind-chip {{ $activeKind === $k ? 'is-active' : '' }}"
               style="--kind-color: {{ $col }};">
                <span class="kind-count">{{ $kindCounts[$k] ?? 0 }}</span>
                <span class="kind-icon">
                    <x-icon :name="$meta['icon']" class="w-4 h-4"/>
                </span>
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('marketplace.index') }}" class="card-light p-2.5 mb-3 flex items-center gap-2">
        <input type="hidden" name="kind" value="{{ $activeKind }}">
        @if($activeCategory) <input type="hidden" name="category" value="{{ $activeCategory }}"> @endif
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4 text-ink-400 ms-1 shrink-0">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" name="q" value="{{ $q }}" placeholder="ابحث في إعلانات {{ $kinds[$activeKind]['label'] }}…"
               class="flex-1 bg-transparent outline-0 text-ink-950 placeholder-ink-400 text-sm py-1.5">
        @if($q !== '' || $activeCategory)
            <a href="{{ route('marketplace.index', ['kind' => $activeKind]) }}"
               class="text-[11px] font-bold text-ink-400 hover:text-coral-600 px-2">إلغاء</a>
        @endif
    </form>

    {{-- Category chips --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-4 pb-1">
        <div class="flex gap-2 px-4 w-max">
            <a href="{{ route('marketplace.index', ['kind' => $activeKind, 'q' => $q ?: null]) }}"
               class="chip {{ ! $activeCategory ? 'chip-active' : '' }}">الكل</a>
            @foreach($categories as $key => $cm)
                <a href="{{ route('marketplace.index', ['kind' => $activeKind, 'category' => $key, 'q' => $q ?: null]) }}"
                   class="chip inline-flex items-center gap-1.5 {{ $activeCategory === $key ? 'chip-active' : '' }}">
                    <x-icon :name="$cm['icon']" class="w-3.5 h-3.5"/>
                    {{ $cm['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Results --}}
    @if($listings->isEmpty())
        @php $km = $kinds[$activeKind]; $col = $colorByTone[$km['tone']] ?? '#FF7A4D'; @endphp
        <div class="card-light p-10 text-center">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-3 grid place-items-center"
                 style="background: {{ $col }}1a; color: {{ $col }};">
                <x-icon :name="$km['icon']" class="w-7 h-7"/>
            </div>
            <h3 class="text-base font-extrabold text-ink-950 mb-1">
                مفيش إعلانات في "{{ $km['label'] }}"
            </h3>
            <p class="text-ink-500 text-xs leading-relaxed mb-4">
                @if($q !== '' || $activeCategory)
                    جرّب filter تاني أو شيل البحث.
                @else
                    @auth كن أول واحد ينشر إعلان! @else ادخل عشان تنشر إعلان @endauth
                @endif
            </p>
            @auth
                <a href="{{ route('marketplace.create') }}" class="btn-primary !py-2.5 !px-5 text-xs">
                    <x-icon name="plus" class="w-3.5 h-3.5"/> أضف إعلان
                </a>
            @else
                <a href="{{ route('login') }}?redirect={{ urlencode(route('marketplace.index')) }}"
                   class="btn-primary !py-2.5 !px-5 text-xs">
                    <x-icon name="user" class="w-3.5 h-3.5"/> دخول
                </a>
            @endauth
        </div>
    @else
        <div class="grid grid-cols-2 gap-2.5" data-infinite-scroll>
            @include('marketplace._page', ['listings' => $listings])
        </div>
    @endif
</div>

@endsection
