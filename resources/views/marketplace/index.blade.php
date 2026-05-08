@extends('layouts.app', ['title' => 'بيع وشراء · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-3">
        <h1 class="text-xl font-extrabold text-ink-950">بيع وشراء</h1>
        @auth
            <a href="{{ route('marketplace.create') }}" class="btn-primary !py-2 !px-4 text-sm">
                <x-icon name="plus" class="w-4 h-4"/> إعلان جديد
            </a>
        @else
            <a href="{{ route('login') }}" class="btn-primary !py-2 !px-4 text-sm">دخول للنشر</a>
        @endauth
    </div>

    {{-- Kind tabs --}}
    <div class="flex gap-2 mb-3 overflow-x-auto scrollbar-hide -mx-4 px-4">
        @foreach($kinds as $k => $meta)
            <a href="{{ route('marketplace.index', ['kind' => $k]) }}"
               class="chip pill-{{ $meta['tone'] }} {{ $activeKind === $k ? 'chip-active' : '' }} shrink-0">
                <x-icon :name="$meta['icon']" class="w-3.5 h-3.5"/>
                {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Search + filters --}}
    <form method="GET" action="{{ route('marketplace.index') }}" class="card-light p-3 mb-3 flex items-center gap-2">
        <input type="hidden" name="kind" value="{{ $activeKind }}">
        <input type="text" name="q" value="{{ $q }}" placeholder="ابحث في الإعلانات…"
               class="flex-1 bg-cream-100 rounded-xl px-3 py-2 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm">
        <select name="category" class="select-styled bg-cream-100 rounded-xl px-3 py-2 text-ink-950 outline-0 border border-ink-950/8 text-sm">
            <option value="">كل الأنواع</option>
            @foreach($categories as $key => $cm)
                <option value="{{ $key }}" {{ $activeCategory === $key ? 'selected' : '' }}>{{ $cm['label'] }}</option>
            @endforeach
        </select>
        <button class="btn-primary !py-2 !px-3 text-xs">فلتر</button>
    </form>

    @if($listings->isEmpty())
        <div class="card-light p-10 text-center">
            <div class="icon-tile mx-auto mb-4 text-coral-600 w-16 h-16">
                <x-icon name="tag" class="w-7 h-7"/>
            </div>
            <h3 class="text-xl font-extrabold text-ink-950 mb-1">مفيش إعلانات لسه</h3>
            <p class="text-ink-500 text-sm">@auth كن أول واحد ينشر! @else ادخل عشان تنشر إعلان @endauth</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3">
            @foreach($listings as $listing)
                <a href="{{ route('marketplace.show', $listing) }}" class="card-light p-0 overflow-hidden block hover:scale-[1.01] transition">
                    @if($listing->photo_url)
                        <img src="{{ $listing->photo_url }}" alt="" loading="lazy" class="w-full aspect-square object-cover">
                    @else
                        @php $cm = $listing->categoryMeta(); @endphp
                        <div class="w-full aspect-square bg-cream-100 grid place-items-center text-ink-300">
                            <x-icon :name="$cm['icon']" class="w-12 h-12"/>
                        </div>
                    @endif
                    <div class="p-3">
                        <h3 class="text-sm font-extrabold text-ink-950 line-clamp-1">{{ $listing->title }}</h3>
                        @if(in_array($listing->kind, ['sale','buy'], true))
                            <div class="text-coral-600 font-black text-base mt-1">{{ $listing->priceLabel() }}</div>
                        @endif
                        <div class="text-[10px] text-ink-400 mt-1">
                            @if($listing->zone) {{ $listing->zone->name }} · @endif
                            {{ $listing->created_at->diffForHumans() }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-4">{{ $listings->links() }}</div>
    @endif
</div>
@endsection
