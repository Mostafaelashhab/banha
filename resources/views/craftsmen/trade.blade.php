@php
    $tradeLabel = $meta['label'] ?? $trade;
    $emoji      = $meta['emoji'] ?? '🔧';
    $h1         = $tradeLabel . ' في بنها والقليوبية';
@endphp
@extends('layouts.app', [
    'title'       => $tradeLabel . ' في بنها · أسعار وأرقام · بنها.shop',
    'description' => 'دليل ' . $tradeLabel . ' في بنها والقليوبية — أسعار شائعة، تقييمات حقيقية، وأرقام واتساب مباشرة.',
    'canonical'   => route('craftsmen.trade', $trade),
    'keywords'    => $tradeLabel . ' بنها, ' . $tradeLabel . ' القليوبية, ' . $tradeLabel . ' طوخ, ' . $tradeLabel . ' أسعار',
])

@push('json-ld')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'بنها.shop', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'صنايعية',  'item' => route('craftsmen.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $tradeLabel, 'item' => route('craftsmen.trade', $trade)],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@if(!empty($priceGuide))
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => collect($priceGuide)->map(fn ($range, $service) => [
        '@type' => 'Question',
        'name'  => 'كم سعر ' . $service . ' عند ' . $tradeLabel . ' في بنها؟',
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $range . ' جنيه (متوسط، بيختلف حسب الشغلانة)'],
    ])->values()->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush

@section('content')
<div class="max-w-2xl mx-auto" data-no-edge-swipe>

    {{-- Top bar --}}
    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('craftsmen.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <span class="text-xs font-bold text-ink-500 truncate">صنايعية · {{ $tradeLabel }}</span>
    </div>

    {{-- Hero --}}
    <div class="card-light p-5 mb-4 bg-gradient-to-br from-coral-50 to-honey-100/40">
        <div class="text-5xl mb-2">{{ $emoji }}</div>
        <h1 class="text-2xl font-black text-ink-950 mb-1">{{ $h1 }}</h1>
        <p class="text-sm text-ink-600">
            {{ $businesses->count() }} {{ $tradeLabel }} في الدليل ·
            <a href="{{ route('craft-jobs.create') . '?trade=' . $trade }}" class="text-coral-600 font-extrabold hover:underline">اطلب دلوقتي</a>
        </p>

        @if($tip)
            <div class="mt-3 p-3 rounded-2xl bg-white/70 backdrop-blur">
                <div class="text-[10px] font-bold text-coral-600 mb-1">💡 نصيحة</div>
                <p class="text-[12px] text-ink-700 leading-relaxed">{{ $tip }}</p>
            </div>
        @endif
    </div>

    {{-- Filters --}}
    <div class="card-light p-3 mb-4">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <select name="zone" onchange="this.form.submit()"
                    class="bg-cream-100 rounded-xl px-3 py-2 text-xs font-bold text-ink-950 outline-0 border border-ink-950/8">
                <option value="">كل المناطق</option>
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" @selected($zoneId === $z->id)>{{ $z->name }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                <input type="checkbox" name="verified" value="1" {{ $verifiedOnly ? 'checked' : '' }}
                       onchange="this.form.submit()" class="w-4 h-4 accent-mint-500">
                <span class="text-xs font-bold text-ink-950">موثّقين فقط</span>
            </label>
        </form>
    </div>

    {{-- Open jobs in this trade --}}
    @if($openJobs->isNotEmpty())
        <div class="card-light p-4 mb-4 bg-mint-50 ring-1 ring-mint-500/20">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-mint-500 animate-pulse"></span>
                    شغلانات مفتوحة دلوقتي
                </h3>
                <a href="{{ route('craft-jobs.index', ['trade' => $trade]) }}" class="text-xs font-bold text-coral-600 hover:underline">شوف الكل ←</a>
            </div>
            <div class="space-y-1.5">
                @foreach($openJobs as $job)
                    <a href="{{ route('craft-jobs.show', $job) }}" class="block bg-white rounded-lg p-2.5 hover:bg-cream-50">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold text-coral-600">{{ $job->urgencyLabel() }}</span>
                            <span class="text-[10px] text-ink-500">· {{ $job->zone->name ?? '' }}</span>
                            <span class="text-[10px] text-ink-400">· {{ $job->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-ink-700 mt-0.5 line-clamp-1">{{ $job->description }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Craftsmen list --}}
    <h2 class="text-sm font-extrabold text-ink-950 mb-3 px-1">{{ $tradeLabel }} المتاحين</h2>
    @if($businesses->isEmpty())
        <div class="card-light p-8 text-center mb-4">
            <div class="text-5xl mb-2">{{ $emoji }}</div>
            <h3 class="text-base font-extrabold text-ink-950 mb-1">لسه مفيش {{ $tradeLabel }} مسجّل</h3>
            <p class="text-xs text-ink-500 mb-4 leading-relaxed">
                إنت أول واحد؟ سجّل نشاطك دلوقتي وكون أول من يظهر للعملاء.
            </p>
            <div class="grid grid-cols-2 gap-2 max-w-sm mx-auto">
                <a href="{{ route('craftsmen.signup') . '?trade=' . $trade }}" class="py-2.5 rounded-full bg-coral-500 text-white text-xs font-extrabold">
                    سجّل نشاطك
                </a>
                <a href="{{ route('craft-jobs.create') . '?trade=' . $trade }}" class="py-2.5 rounded-full bg-white text-ink-950 text-xs font-extrabold ring-1 ring-ink-950/10">
                    اطلب الخدمة
                </a>
            </div>
        </div>
    @else
        <div class="space-y-2 mb-5">
            @foreach($businesses as $b)
                @include('craftsmen.partials.craftsman-row', ['craftsman' => $b])
            @endforeach
        </div>
    @endif

    {{-- Price guide (SEO content + user value) --}}
    @if(!empty($priceGuide))
        <div class="card-light p-4 mb-5">
            <h2 class="text-sm font-extrabold text-ink-950 mb-3 inline-flex items-center gap-2">
                <span>💵</span> متوسط أسعار {{ $tradeLabel }} في بنها
            </h2>
            <p class="text-[11px] text-ink-500 mb-3 leading-relaxed">
                الأسعار اللي تحت تقريبية وبتختلف حسب الموقع، نوع الشغل، والتوقيت. اطلب عرض سعر من الصنايعي قبل بداية الشغلانة.
            </p>
            <dl class="space-y-2">
                @foreach($priceGuide as $service => $range)
                    <div class="flex items-center justify-between bg-cream-100/60 rounded-lg px-3 py-2">
                        <dt class="text-sm font-bold text-ink-950">{{ $service }}</dt>
                        <dd class="text-sm font-extrabold text-coral-600" dir="ltr">{{ $range }} ج</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endif

    {{-- Bottom CTAs --}}
    <div class="grid grid-cols-2 gap-2 mb-6">
        <a href="{{ route('craft-jobs.create') . '?trade=' . $trade }}" class="block py-3 rounded-2xl bg-coral-500 text-white text-center font-extrabold text-sm">
            📋 اطلب {{ $tradeLabel }}
        </a>
        <a href="{{ route('craftsmen.signup') . '?trade=' . $trade }}" class="block py-3 rounded-2xl bg-ink-950 text-white text-center font-extrabold text-sm">
            🛠️ سجّل كصنايعي
        </a>
    </div>
</div>
@endsection
