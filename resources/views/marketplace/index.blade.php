@extends('layouts.app', ['title' => 'بيع وشراء · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-xl font-extrabold text-ink-950 mb-3">🛒 بيع وشراء</h1>

    {{-- Kind tabs (4 columns — fits exactly: بيع / مطلوب / مفقودات / لقطات) --}}
    <div class="grid grid-cols-4 gap-1.5 mb-3">
        @foreach($kinds as $k => $meta)
            <a href="{{ route('marketplace.index', ['kind' => $k]) }}"
               class="flex flex-col items-center gap-1 py-2.5 rounded-2xl text-xs font-bold transition
                      {{ $activeKind === $k ? 'bg-coral-500 text-white' : 'bg-white border border-ink-950/8 text-ink-500 hover:bg-cream-100' }}">
                <x-icon :name="$meta['icon']" class="w-4 h-4"/>
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Search + category filter (full-width inputs) --}}
    <form method="GET" action="{{ route('marketplace.index') }}" class="card-light p-2 mb-3 space-y-2">
        <input type="hidden" name="kind" value="{{ $activeKind }}">
        <div class="flex items-center gap-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4 text-ink-400 ms-2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="q" value="{{ $q }}" placeholder="ابحث في الإعلانات…"
                   class="flex-1 bg-transparent outline-0 text-ink-950 placeholder-ink-400 text-sm py-2">
            @if($q !== '' || $activeCategory)
                <a href="{{ route('marketplace.index', ['kind' => $activeKind]) }}" class="text-xs text-ink-400 px-2">إلغاء</a>
            @endif
        </div>
        <div class="flex gap-1.5 overflow-x-auto scrollbar-hide -mx-2 px-2 pt-1 border-t border-ink-950/5">
            <button name="category" value="" type="submit"
                    class="chip shrink-0 {{ ! $activeCategory ? 'chip-active' : '' }}">الكل</button>
            @foreach($categories as $key => $cm)
                <button name="category" value="{{ $key }}" type="submit"
                        class="chip shrink-0 {{ $activeCategory === $key ? 'chip-active' : '' }} inline-flex items-center gap-1">
                    <x-icon :name="$cm['icon']" class="w-3 h-3"/>
                    {{ $cm['label'] }}
                </button>
            @endforeach
        </div>
    </form>

    @auth
        <a href="{{ route('marketplace.create') }}" class="btn-primary w-full justify-center mb-3 !py-3">
            <x-icon name="plus" class="w-4 h-4"/> أضف إعلان جديد
        </a>
    @else
        <a href="{{ route('login') }}" class="btn-primary w-full justify-center mb-3 !py-3">
            <x-icon name="user" class="w-4 h-4"/> دخول لنشر إعلان
        </a>
    @endauth

    @if($listings->isEmpty())
        <div class="card-light p-10 text-center">
            <div class="icon-tile mx-auto mb-4 text-coral-600 w-16 h-16">
                <x-icon name="tag" class="w-7 h-7"/>
            </div>
            <h3 class="text-xl font-extrabold text-ink-950 mb-1">مفيش إعلانات لسه</h3>
            <p class="text-ink-500 text-sm">@auth كن أول واحد ينشر! @else ادخل عشان تنشر إعلان @endauth</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-2" data-infinite-scroll>
            @include('marketplace._page', ['listings' => $listings])
        </div>
    @endif
</div>
@endsection
