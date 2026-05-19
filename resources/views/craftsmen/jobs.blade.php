@extends('layouts.app', [
    'title'       => 'شغلانات صنايعية لحظية · بنها.shop',
    'description' => 'كل طلبات الشغل الجديدة من أصحاب البيوت في بنها والقليوبية. سباكة، كهربا، تكييف، نقاشة، وكل التخصصات.',
    'canonical'   => route('craft-jobs.index'),
])

@section('content')
<div class="max-w-2xl mx-auto" data-no-edge-swipe>

    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('craftsmen.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <span class="text-xs font-bold text-ink-500">شغلانات لحظية</span>
        <a href="{{ route('craft-jobs.create') }}" class="ms-auto text-xs font-extrabold text-coral-600 hover:underline">+ اطلب شغل</a>
    </div>

    {{-- Hero --}}
    <div class="card-light p-4 mb-4 bg-gradient-to-br from-coral-50 to-cream-50">
        <h1 class="text-xl font-black text-ink-950 mb-1">شغلانات صنايعية مفتوحة</h1>
        <p class="text-xs text-ink-500">
            <span class="inline-flex items-center gap-1 text-mint-700 font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-mint-500 animate-pulse"></span>
                {{ $openCount }} طلب مفتوح دلوقتي
            </span>
        </p>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card-light p-3 mb-4 flex flex-wrap gap-2 items-center">
        <select name="trade" onchange="this.form.submit()"
                class="bg-cream-100 rounded-xl px-3 py-2 text-xs font-bold text-ink-950 outline-0 border border-ink-950/8">
            <option value="">كل التخصصات</option>
            @foreach($trades as $t)
                <option value="{{ $t['key'] }}" @selected($tradeFilter === $t['key'])>{{ $t['emoji'] }} {{ $t['label'] }}</option>
            @endforeach
        </select>
        <select name="zone" onchange="this.form.submit()"
                class="bg-cream-100 rounded-xl px-3 py-2 text-xs font-bold text-ink-950 outline-0 border border-ink-950/8">
            <option value="">كل المناطق</option>
            @foreach($zones as $z)
                <option value="{{ $z->id }}" @selected($zoneFilter === $z->id)>{{ $z->name }}</option>
            @endforeach
        </select>
        @if($tradeFilter || $zoneFilter)
            <a href="{{ route('craft-jobs.index') }}" class="text-[11px] font-bold text-coral-600 hover:underline ms-auto">مسح الفلتر ×</a>
        @endif
    </form>

    {{-- Jobs list --}}
    @if($jobs->isEmpty())
        <div class="card-light p-10 text-center">
            <div class="text-5xl mb-2">🔧</div>
            <h3 class="text-base font-extrabold text-ink-950 mb-1">مفيش شغلانات بهذي الفلاتر</h3>
            <p class="text-xs text-ink-500">جرّب فلاتر تانية أو ارجع بعد شوية.</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($jobs as $job)
                @php $tm = $job->tradeMeta(); @endphp
                <a href="{{ route('craft-jobs.show', $job) }}" class="card-light p-3 block hover:bg-cream-100 transition">
                    <div class="flex items-start gap-3">
                        <span class="w-12 h-12 rounded-2xl bg-coral-50 text-coral-600 grid place-items-center text-2xl shrink-0">
                            {{ $tm['emoji'] ?? '🔧' }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-sm font-extrabold text-ink-950">{{ $tm['label'] ?? $job->sub_type }}</span>
                                @php
                                    $urgencyColor = match($job->urgency) {
                                        'asap'      => 'bg-blush-500 text-white',
                                        'today'     => 'bg-coral-500 text-white',
                                        'this_week' => 'bg-honey-100 text-honey-700',
                                        default     => 'bg-ink-100 text-ink-500',
                                    };
                                @endphp
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $urgencyColor }}">
                                    {{ $job->urgencyLabel() }}
                                </span>
                            </div>
                            <div class="text-[11px] text-ink-500 mt-0.5">
                                {{ $job->zone->name ?? 'بنها' }}
                                @if($job->address) · {{ \Illuminate\Support\Str::limit($job->address, 30) }} @endif
                                · {{ $job->created_at->diffForHumans() }}
                            </div>
                            <p class="text-xs text-ink-700 mt-1.5 line-clamp-2 leading-relaxed">{{ $job->description }}</p>
                            <div class="flex items-center gap-3 mt-2 text-[10px] text-ink-500 font-bold">
                                @if($job->budgetLabel())
                                    <span class="text-mint-700">💰 {{ $job->budgetLabel() }}</span>
                                @endif
                                <span>👁 {{ $job->views_count }}</span>
                                <span>💬 {{ $job->responses_count }} رد</span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- CTA --}}
    <div class="card-light p-4 mt-6 bg-ink-950 text-white text-center">
        <div class="text-sm font-extrabold mb-1">صنايعي ومش لاقي شغل؟</div>
        <p class="text-[11px] text-white/70 mb-3">سجّل نشاطك في تخصصك ومنطقتك. كل طلب جديد بييجيلك notification.</p>
        <a href="{{ route('craftsmen.signup') }}" class="inline-flex items-center gap-1 py-2 px-5 rounded-full bg-coral-500 text-white text-xs font-extrabold">
            ابدأ التسجيل ←
        </a>
    </div>
</div>
@endsection
