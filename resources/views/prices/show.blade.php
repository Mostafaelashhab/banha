@extends('layouts.app', ['title' => $product->name . ' · رادار الأسعار'])

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('prices.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-base font-bold text-ink-500">رادار الأسعار</h1>
    </div>

    {{-- Hero card — flat, no loud brand block --}}
    <div class="bg-white rounded-2xl p-6 mb-4 ring-1 ring-ink-950/6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-6xl mb-1">{{ $product->emoji ?: '🛒' }}</div>
                <h2 class="text-2xl font-black text-ink-950">{{ $product->name }}</h2>
                <div class="text-ink-500 text-xs mt-1">{{ $product->unit }} · {{ \App\Models\Product::CATEGORIES[$product->category] ?? '' }}</div>
            </div>
            <a href="{{ route('prices.create', ['product' => $product->id]) }}" class="btn-primary !py-2 !px-4 text-sm">
                <x-icon name="plus" class="w-4 h-4"/>
                ضيف سعر
            </a>
        </div>

        @if($stats['avg_today'])
            <div class="mt-5 pt-5 border-t border-ink-950/8 flex items-end justify-between">
                <div>
                    <div class="text-ink-500 text-xs">متوسط النهاردة</div>
                    <div class="text-4xl md:text-5xl font-black leading-none text-ink-950">
                        {{ number_format($stats['avg_today'], 2) }}
                        <span class="text-base text-ink-500 font-bold">ج</span>
                    </div>
                </div>
                <div class="text-end text-ink-500 text-xs space-y-0.5">
                    @if($stats['min']) <div>أقل: <b>{{ number_format($stats['min'], 2) }}</b></div> @endif
                    @if($stats['max']) <div>أكتر: <b>{{ number_format($stats['max'], 2) }}</b></div> @endif
                    <div>{{ $stats['reports'] }} تقرير</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Trend chart (simple bar) --}}
    @if($trend->count() > 1)
        @php
            $maxAvg = $trend->max('avg_p');
            $minAvg = $trend->min('avg_p');
            $range  = max(($maxAvg - $minAvg), 0.01);
        @endphp
        <div class="card-light p-4 mb-4">
            <h3 class="text-sm font-extrabold text-ink-950 mb-4 inline-flex items-center gap-2">
                <x-icon name="flame" class="w-4 h-4 text-coral-500"/>
                أسعار آخر ٧ أيام
            </h3>
            <div class="flex items-end gap-1.5 h-32">
                @foreach($trend as $t)
                    @php
                        $h = max(20, (($t->avg_p - $minAvg) / $range) * 100);
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1.5">
                        <div class="text-[10px] font-bold text-ink-500">{{ number_format($t->avg_p, 1) }}</div>
                        <div class="w-full rounded-t-lg brand-bg" style="height: {{ $h }}%"></div>
                        <div class="text-[10px] text-ink-400">{{ \Illuminate\Support\Carbon::parse($t->d)->format('d/m') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Recent reports --}}
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-extrabold text-ink-950">آخر التقارير</h3>
        <span class="text-ink-400 text-xs">{{ $recent->count() }} تقرير</span>
    </div>

    <div class="space-y-2">
        @forelse($recent as $r)
            <div class="card-light p-4 flex items-center gap-3">
                <span class="w-10 h-10 rounded-full grid place-items-center text-white font-bold text-sm shrink-0"
                      style="background: {{ \App\Support\AnonSeed::avatarColor($r->user->username) }}">
                    {{ \App\Support\AnonSeed::initial($r->user->username) }}
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-ink-950 text-sm">{{ $r->user->username }}</span>
                        <span class="text-ink-400 text-xs">· {{ $r->zone->name ?? '' }}</span>
                    </div>
                    <div class="text-xs text-ink-500 truncate">
                        @if($r->shop_name) <b>{{ $r->shop_name }}</b> @endif
                        @if($r->notes) {{ $r->notes }} @endif
                        @if(!$r->shop_name && !$r->notes) {{ $r->created_at->diffForHumans() }} @endif
                    </div>
                </div>
                <div class="text-end">
                    <div class="text-xl font-black text-ink-950 leading-none">{{ number_format($r->price, 2) }}</div>
                    <div class="text-[10px] text-ink-400 mt-0.5">ج · {{ $product->unit }}</div>
                </div>
            </div>
        @empty
            <div class="card-light p-10 text-center">
                <p class="text-ink-500 mb-4">لسه مفيش تقارير لـ {{ $product->name }}.</p>
                <a href="{{ route('prices.create', ['product' => $product->id]) }}" class="btn-primary">
                    كن أول واحد يضيف
                    <x-icon name="arrow-left" class="w-4 h-4"/>
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
