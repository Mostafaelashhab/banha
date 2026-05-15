@extends('layouts.app', [
    'title' => 'مواعيد قطارات بنها · جدول رحلات السكة الحديد · بنهاوي',
    'description' => 'كل مواعيد القطارات من وإلى بنها — القاهرة، الإسكندرية، طنطا، المنصورة، أسوان، الأقصر — مع رقم القطار ومدة الرحلة وعدد التوقفات.',
    'keywords' => 'مواعيد قطارات بنها, قطار بنها القاهرة, قطار بنها الإسكندرية, جدول قطارات بنها, سكة حديد بنها',
])

@php
    $arType = [
        'VIP'              => 'مكيف VIP',
        'Russian'          => 'مكيف روسي',
        'Spanish'          => 'مكيف اسباني',
        'Spanish-VIP'      => 'مكيف اسباني VIP',
        'Hot Plus'         => 'تالت محسّن',
        'Hot Regular'      => 'تالت عادي',
        'Improved Hot'     => 'تالت محسّن',
        'Hot Service'      => 'تالت',
    ];

    // 24h → 12h with Arabic ص/م suffix.
    //   "13:45" → "1:45 م"   ·   "06:05" → "6:05 ص"   ·   "00:30" → "12:30 ص"
    $fmt12 = function (string $time): string {
        if (! preg_match('/^(\d{1,2}):(\d{2})$/', $time, $m)) return $time;
        $h = (int) $m[1];
        $min = $m[2];
        $suffix = $h < 12 ? 'ص' : 'م';
        $h12 = $h % 12 ?: 12;
        return $h12 . ':' . $min . ' ' . $suffix;
    };
@endphp

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ─── Header ─── --}}
    <div class="mb-5 rise rise-1">
        <h1 class="text-2xl md:text-3xl font-black text-ink-950 leading-tight mb-2">
            مواعيد قطارات بنها
        </h1>
        <p class="text-[12px] text-ink-500 leading-relaxed">
            جدول كامل لكل القطارات اللي بتمر من محطة بنها — مع رقم القطار، نوعه، مواعيد القيام والوصول، ومدة الرحلة.
            البيانات بتُحدّث تلقائياً من المواعيد الرسمية لـ هيئة السكة الحديد المصرية.
        </p>
        @if($scrapedAt)
            <p class="text-[10px] text-ink-400 mt-2">
                آخر تحديث: {{ \Carbon\Carbon::parse($scrapedAt)->diffForHumans() }}
            </p>
        @endif
    </div>

    {{-- ─── Direction tabs ─── --}}
    <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-1 grid grid-cols-2 gap-1 mb-4">
        <a href="{{ route('trains.index', ['dir' => 'outgoing']) }}"
           class="text-center py-2 rounded-xl text-xs font-extrabold transition
                  {{ $direction === 'outgoing' ? 'bg-coral-500 text-white' : 'text-ink-500 hover:bg-cream-100' }}">
            من بنها إلى …
        </a>
        <a href="{{ route('trains.index', ['dir' => 'incoming']) }}"
           class="text-center py-2 rounded-xl text-xs font-extrabold transition
                  {{ $direction === 'incoming' ? 'bg-coral-500 text-white' : 'text-ink-500 hover:bg-cream-100' }}">
            القطارات الواصلة لبنها
        </a>
    </div>

    @if(empty($routes))
        {{-- ─── Empty state — JSON file not generated yet ─── --}}
        <div class="card-light p-10 text-center">
            <span class="w-14 h-14 rounded-2xl bg-honey-100 text-honey-700 grid place-items-center mx-auto mb-3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                    <rect x="4" y="3" width="16" height="16" rx="2"/>
                    <path d="M4 11h16M9 3v8M15 3v8"/>
                    <circle cx="9" cy="17" r="1"/><circle cx="15" cy="17" r="1"/>
                </svg>
            </span>
            <h3 class="text-base font-extrabold text-ink-950 mb-1">مفيش بيانات لسه</h3>
            <p class="text-[12px] text-ink-500 leading-relaxed mb-3">
                البيانات بتُحدّث من command:
            </p>
            <code class="inline-block bg-cream-100 rounded-lg px-3 py-1.5 text-[11px] font-mono text-ink-950">
                php artisan trains:scrape:banha
            </code>
        </div>
    @else

        {{-- ─── Destination picker (horizontal pill scroll) ─── --}}
        <div class="overflow-x-auto scrollbar-hide -mx-4 px-4 mb-4">
            <div class="flex items-center gap-2 min-w-max">
                @foreach($routes as $key => $route)
                    <a href="{{ route('trains.index', ['dir' => $direction, 'to' => $key]) }}"
                       class="shrink-0 px-4 py-1.5 rounded-full text-xs font-extrabold transition
                              {{ $destination === $key ? 'bg-ink-950 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                        {{ $route['ar'] }}
                        <span class="text-[9px] {{ $destination === $key ? 'text-white/60' : 'text-ink-400' }} font-bold ms-1">
                            {{ count($route['trains'] ?? []) }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ─── Schedule header ─── --}}
        @php $destAr = $routes[$destination]['ar'] ?? $destination; @endphp
        <div class="bg-cream-100 ring-1 ring-ink-950/8 rounded-2xl p-4 mb-3 flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-coral-50 text-coral-600 grid place-items-center shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                    <path d="M4 11h16"/>
                    <circle cx="8" cy="16" r="1"/><circle cx="16" cy="16" r="1"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="text-[10px] font-bold text-ink-500">
                    {{ $direction === 'outgoing' ? 'القطارات من بنها إلى' : 'القطارات الواصلة من' }}
                </div>
                <div class="text-base font-black text-ink-950">{{ $destAr }}</div>
            </div>
            <span class="text-[11px] font-extrabold text-ink-500 bg-white px-2.5 py-1 rounded-full">
                {{ count($trains) }} قطار
            </span>
        </div>

        @if(empty($trains))
            <div class="card-light p-8 text-center text-[12px] text-ink-500">
                مفيش قطارات مُسجّلة على المسار ده.
            </div>
        @else
            {{-- ─── Table view (desktop) + card list (mobile) ─── --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 overflow-hidden">
                {{-- Header — hidden on small screens --}}
                <div class="hidden md:grid grid-cols-12 gap-2 px-4 py-2.5 bg-cream-100 text-[10px] font-extrabold text-ink-500">
                    <div class="col-span-2">القيام</div>
                    <div class="col-span-2">الوصول</div>
                    <div class="col-span-3">النوع</div>
                    <div class="col-span-2">المدة</div>
                    <div class="col-span-1 text-center">توقفات</div>
                    <div class="col-span-2">رقم القطار</div>
                </div>

                <div class="divide-y divide-ink-950/6">
                    @php
                        // All train stops, keyed by train number — populated by the scraper's phase 2.
                        $allStops = $payload['details'] ?? [];
                    @endphp
                    @foreach($trains as $t)
                        @php
                            $typeAr   = $arType[$t['type']] ?? $t['type'];
                            $isVip    = str_contains(strtolower($t['type']), 'vip');
                            $detail   = $allStops[$t['number']] ?? null;
                            $hasStops = $detail && ! empty($detail['stops']);
                        @endphp
                        <details class="group">
                            <summary class="md:grid md:grid-cols-12 md:gap-2 px-4 py-3 hover:bg-cream-50 transition cursor-pointer list-none flex items-center gap-3">
                                {{-- Mobile layout --}}
                                <div class="md:hidden flex items-center justify-between gap-2 flex-1 min-w-0">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-base font-black text-ink-950">{{ $fmt12($t['start']) }}</span>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-3 h-3 text-ink-300 rtl:rotate-180">
                                                <polyline points="9 18 15 12 9 6"/>
                                            </svg>
                                            <span class="text-base font-extrabold text-ink-700">{{ $fmt12($t['end']) }}</span>
                                        </div>
                                        <div class="text-[10px] text-ink-500 mt-0.5 inline-flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold {{ $isVip ? 'text-coral-600' : '' }}">{{ $typeAr }}</span>
                                            <span>·</span>
                                            <span dir="ltr">{{ $t['duration'] }}</span>
                                            <span>·</span>
                                            <span>{{ $t['stops'] }} توقّف</span>
                                        </div>
                                    </div>
                                    <span class="shrink-0 bg-coral-50 text-coral-600 text-[10px] font-extrabold px-2 py-0.5 rounded-full" dir="ltr">
                                        #{{ $t['number'] }}
                                    </span>
                                    @if($hasStops)
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3 h-3 text-ink-400 transition group-open:rotate-180 shrink-0">
                                            <polyline points="6 9 12 15 18 9"/>
                                        </svg>
                                    @endif
                                </div>

                                {{-- Desktop layout --}}
                                <div class="hidden md:block col-span-2 text-sm font-black text-ink-950">{{ $fmt12($t['start']) }}</div>
                                <div class="hidden md:block col-span-2 text-sm font-extrabold text-ink-700">{{ $fmt12($t['end']) }}</div>
                                <div class="hidden md:block col-span-3 text-[12px] {{ $isVip ? 'text-coral-600 font-extrabold' : 'text-ink-950 font-bold' }}">
                                    {{ $typeAr }}
                                </div>
                                <div class="hidden md:block col-span-2 text-[12px] text-ink-500" dir="ltr">{{ $t['duration'] }}</div>
                                <div class="hidden md:block col-span-1 text-[12px] text-ink-500 text-center">{{ $t['stops'] }}</div>
                                <div class="hidden md:block col-span-2 text-[12px] font-extrabold text-coral-600 flex items-center justify-between gap-1" dir="ltr">
                                    <span>#{{ $t['number'] }}</span>
                                    @if($hasStops)
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3 h-3 text-ink-400 transition group-open:rotate-180 shrink-0">
                                            <polyline points="6 9 12 15 18 9"/>
                                        </svg>
                                    @endif
                                </div>
                            </summary>

                            {{-- Expanded stops timeline --}}
                            @if($hasStops)
                                <div class="bg-cream-50 px-4 py-3 border-t border-ink-950/6">
                                    <div class="text-[10px] font-extrabold text-ink-500 mb-2 inline-flex items-center gap-1.5">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        المحطات اللي بيقف فيها ({{ count($detail['stops']) }})
                                    </div>

                                    <ol class="space-y-0">
                                        @foreach($detail['stops'] as $i => $stop)
                                            @php
                                                $isBanha = str_contains(strtolower($stop['name']), 'banha');
                                                $isFirst = $i === 0;
                                                $isLast  = $i === count($detail['stops']) - 1;
                                            @endphp
                                            <li class="flex items-stretch gap-3 relative">
                                                {{-- Time column --}}
                                                <div class="w-16 sm:w-20 shrink-0 text-end py-2">
                                                    @if(! empty($stop['a']))
                                                        <div class="text-[10px] text-ink-400">وصول</div>
                                                        <div class="text-[11px] font-bold text-ink-700">{{ $fmt12($stop['a']) }}</div>
                                                    @endif
                                                    @if(! empty($stop['d']))
                                                        <div class="text-[10px] text-ink-400 {{ ! empty($stop['a']) ? 'mt-1' : '' }}">قيام</div>
                                                        <div class="text-[11px] font-bold text-ink-950">{{ $fmt12($stop['d']) }}</div>
                                                    @endif
                                                </div>

                                                {{-- Connector line + dot --}}
                                                <div class="relative flex flex-col items-center pt-3 pb-3">
                                                    @unless($isFirst)
                                                        <div class="absolute top-0 bottom-1/2 w-px {{ $isBanha ? 'bg-coral-500' : 'bg-ink-950/15' }}"></div>
                                                    @endunless
                                                    <div class="w-3 h-3 rounded-full ring-2 ring-white relative z-10
                                                                {{ $isBanha ? 'bg-coral-500' : ($isFirst || $isLast ? 'bg-ink-950' : 'bg-ink-950/30') }}"></div>
                                                    @unless($isLast)
                                                        <div class="absolute top-1/2 bottom-0 w-px {{ $isBanha ? 'bg-coral-500' : 'bg-ink-950/15' }}"></div>
                                                    @endunless
                                                </div>

                                                {{-- Station name --}}
                                                <div class="flex-1 min-w-0 py-2">
                                                    <div class="text-[12px] font-extrabold {{ $isBanha ? 'text-coral-600' : 'text-ink-950' }}">
                                                        {{ $stop['name'] }}
                                                        @if($isBanha)
                                                            <span class="text-[9px] font-extrabold text-white bg-coral-500 px-1.5 py-0.5 rounded-full ms-1">محطتك</span>
                                                        @endif
                                                        @if($isFirst)
                                                            <span class="text-[9px] font-bold text-ink-500 ms-1">(البداية)</span>
                                                        @elseif($isLast)
                                                            <span class="text-[9px] font-bold text-ink-500 ms-1">(النهاية)</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ol>

                                    @if(! empty($detail['working']))
                                        <p class="text-[10px] text-ink-500 mt-3 pt-2 border-t border-ink-950/6">
                                            <span class="font-bold">أيام العمل:</span> {{ $detail['working'] }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </details>
                    @endforeach
                </div>
            </div>

            <p class="text-[10px] text-ink-400 text-center mt-3 leading-relaxed">
                البيانات للاسترشاد فقط. تأكّد من المواعيد قبل سفرك بالاتصال بـ ١٤١ أو زيارة محطة بنها.
            </p>
        @endif

    @endif

    {{-- ─── Related links ─── --}}
    <div class="mt-6 grid grid-cols-2 gap-2">
        <a href="{{ route('directory.category', 'transport') }}" class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 hover:ring-coral-500/40 transition flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-coral-50 text-coral-600 grid place-items-center shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <rect x="4" y="3" width="16" height="16" rx="2"/><circle cx="8" cy="17" r="1"/><circle cx="16" cy="17" r="1"/>
                </svg>
            </span>
            <span class="text-xs font-extrabold text-ink-950">مواقف ومواصلات بنها</span>
        </a>
        <a href="{{ url('/emergency') }}" class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 hover:ring-coral-500/40 transition flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-blush-100 text-blush-600 grid place-items-center shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <polyline points="13 2 13 11 22 11"/>
                </svg>
            </span>
            <span class="text-xs font-extrabold text-ink-950">أرقام طوارئ بنها</span>
        </a>
    </div>

</div>
@endsection
