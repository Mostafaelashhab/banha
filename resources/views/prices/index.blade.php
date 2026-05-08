@extends('layouts.app', ['title' => 'رادار الأسعار · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-black text-ink-950 inline-flex items-center gap-2">
                <span class="w-9 h-9 rounded-xl brand-bg grid place-items-center text-white">
                    <x-icon name="tag" class="w-4 h-4"/>
                </span>
                رادار الأسعار
            </h1>
            <p class="text-ink-500 text-sm mt-1">آخر ٢٤ ساعة · {{ $zones->find($activeZone)?->name ?? 'كل المناطق' }}</p>
        </div>
        <a href="{{ route('prices.create') }}" class="btn-primary !py-2 !px-4 text-sm">
            <x-icon name="plus" class="w-4 h-4"/>
            ضيف سعر
        </a>
    </div>

    {{-- Category chips --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-3">
        <div class="flex gap-2 px-4 w-max">
            <a href="{{ route('prices.index', ['zone' => $activeZone]) }}"
               class="chip {{ ! $category ? 'chip-active' : '' }}">الكل</a>
            @foreach($categories as $key => $label)
                <a href="{{ route('prices.index', ['category' => $key, 'zone' => $activeZone]) }}"
                   class="chip {{ $category === $key ? 'chip-active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Zone selector chips --}}
    <div class="overflow-x-auto scrollbar-hide -mx-4 mb-4 pb-1">
        <div class="flex gap-2 px-4 w-max">
            @foreach($zones as $z)
                <a href="{{ route('prices.index', ['category' => $category, 'zone' => $z->id]) }}"
                   class="chip {{ $activeZone === $z->id ? 'chip-active' : '' }}">
                    {{ $z->name }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Products grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        @forelse($products as $product)
            @php
                $today = $todayAvg[$product->id] ?? null;
                $yest  = $yesterdayAvg[$product->id] ?? null;
                $price = $today?->avg_p;
                $delta = ($today && $yest) ? ((float) $price - (float) $yest) : null;
                $tone  = is_null($delta) ? null : ($delta > 0 ? 'blush' : ($delta < 0 ? 'mint' : 'ink'));
            @endphp
            <a href="{{ route('prices.show', ['product' => $product, 'zone' => $activeZone]) }}"
               class="card-light p-4 hover:-translate-y-0.5 hover:shadow-lg transition group">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-2xl">{{ $product->emoji ?: '🛒' }}</span>
                    @if($price !== null)
                        @if($tone === 'mint')
                            <span class="pill-mint text-[10px] font-bold px-2 py-0.5 rounded-full">↓ {{ number_format(abs($delta), 2) }}</span>
                        @elseif($tone === 'blush')
                            <span class="pill-blush text-[10px] font-bold px-2 py-0.5 rounded-full">↑ {{ number_format($delta, 2) }}</span>
                        @else
                            <span class="bg-ink-950/8 text-ink-500 text-[10px] font-bold px-2 py-0.5 rounded-full">ثابت</span>
                        @endif
                    @endif
                </div>
                <div class="text-sm font-bold text-ink-950 truncate">{{ $product->name }}</div>
                <div class="text-[11px] text-ink-400 mb-2">{{ $product->unit }}</div>

                @if($price !== null)
                    <div class="text-2xl font-black text-coral leading-none">
                        {{ number_format($price, 2) }}
                        <span class="text-sm text-ink-400 font-bold">ج</span>
                    </div>
                    <div class="text-[10px] text-ink-400 mt-1">{{ $today->c }} تقرير</div>
                @else
                    <div class="text-sm text-ink-400 font-bold">لسه مفيش سعر</div>
                    <div class="text-[10px] text-coral mt-1">كن أول واحد</div>
                @endif
            </a>
        @empty
            <div class="card-light p-10 text-center col-span-full">
                <p class="text-ink-500">مفيش منتجات في الفئة دي.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
