@extends('layouts.app', ['title' => 'أوردراتي · بنهاوي'])

@php
    // Visual progression of an order: pending → confirmed → preparing → out → completed
    $statusSteps = [
        'pending'          => ['label' => 'بانتظار التأكيد', 'icon' => '⏳', 'tone' => 'honey'],
        'confirmed'        => ['label' => 'اتأكد',            'icon' => '✓',  'tone' => 'mint'],
        'preparing'        => ['label' => 'بيتجهّز',          'icon' => '🍳', 'tone' => 'coral'],
        'out_for_delivery' => ['label' => 'في الطريق',         'icon' => '🛵', 'tone' => 'coral'],
        'completed'        => ['label' => 'اتسلّم',            'icon' => '✓',  'tone' => 'mint'],
        'cancelled'        => ['label' => 'اتلغى',             'icon' => '✗',  'tone' => 'blush'],
    ];
    // Linear flow used for the step indicator (cancelled is a separate branch)
    $linearFlow = ['pending', 'confirmed', 'preparing', 'out_for_delivery', 'completed'];
    $toneRing = [
        'honey' => 'bg-honey-100 text-honey-700 ring-honey-500/20',
        'mint'  => 'bg-mint-100 text-mint-700 ring-mint-500/20',
        'coral' => 'bg-coral-50 text-coral-600 ring-coral-500/20',
        'blush' => 'bg-blush-100 text-blush-600 ring-blush-500/20',
    ];
    $fmt = fn ($n) => $n == (int) $n ? (string) (int) $n : number_format($n, 2, '.', '');
@endphp

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="mb-4 rise rise-1">
        <h1 class="text-xl font-black text-ink-950 leading-tight">أوردراتي</h1>
        <p class="text-[11px] text-ink-500 mt-0.5">تابع حالة كل طلب طلبته من بنهاوي.</p>
    </div>

    {{-- Filters --}}
    <div class="flex gap-2 overflow-x-auto scrollbar-hide mb-4 -mx-4 px-4">
        @foreach([
            'active'    => 'الشغّالة·' . $counts['active'],
            'completed' => 'تمّت·' . $counts['completed'],
            'cancelled' => 'ملغية·' . $counts['cancelled'],
        ] as $key => $label)
            <a href="{{ route('my-orders.index', ['filter' => $key]) }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-xs font-extrabold transition
                      {{ $filter === $key ? 'bg-coral-500 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                {{ str_replace('·', ' · ', $label) }}
            </a>
        @endforeach
    </div>

    {{-- Empty state --}}
    @if($orders->isEmpty())
        <div class="card-light p-10 text-center rise rise-2">
            <div class="text-5xl mb-3">🛍</div>
            <h3 class="text-base font-extrabold text-ink-950 mb-1">
                @if($filter === 'active') مفيش أوردرات شغّالة دلوقتي
                @elseif($filter === 'completed') لسه ماخلصتش أوردر
                @else مفيش أوردرات ملغية
                @endif
            </h3>
            <p class="text-[12px] text-ink-500 max-w-xs mx-auto leading-relaxed mb-4">
                اطلب من أي مطعم أو محل على بنهاوي وهنا هتلاقي كل أوردراتك بحالتها.
            </p>
            <a href="{{ route('directory.category', 'food') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-coral-500 text-white text-[12px] font-extrabold hover:bg-coral-600 transition">
                اطلب من مطعم
            </a>
        </div>
    @endif

    {{-- Orders list --}}
    <div class="space-y-3">
        @foreach($orders as $o)
            @php
                $statusMeta = $statusSteps[$o->status] ?? $statusSteps['pending'];
                $tone = $toneRing[$statusMeta['tone']];
                $currentIdx = array_search($o->status, $linearFlow);
                $isCancelled = $o->status === 'cancelled';
            @endphp
            <div class="card-light p-4">
                {{-- Top row: business + status pill --}}
                <div class="flex items-start gap-3 mb-3">
                    @if($o->business->photo_url)
                        <img src="{{ $o->business->photo_url }}" alt="" class="w-11 h-11 rounded-xl object-cover shrink-0">
                    @else
                        <span class="w-11 h-11 rounded-xl bg-cream-100 grid place-items-center text-xl shrink-0">🏪</span>
                    @endif
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('directory.show', $o->business) }}" class="text-sm font-extrabold text-ink-950 truncate block hover:text-coral-600 transition">
                            {{ $o->business->name }}
                        </a>
                        <div class="text-[10px] text-ink-500 flex items-center gap-1.5 flex-wrap">
                            <span>#{{ $o->id }}</span>
                            <span>·</span>
                            <span>{{ $o->created_at->diffForHumans() }}</span>
                            @if($o->area)
                                <span>·</span>
                                <span>🗺 {{ $o->area->name }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $tone }} ring-1 shrink-0">
                        <span>{{ $statusMeta['icon'] }}</span>
                        <span>{{ $statusMeta['label'] }}</span>
                    </span>
                </div>

                {{-- Step indicator (linear flow). Hidden for cancelled. --}}
                @unless($isCancelled)
                    <div class="flex items-center gap-1 mb-3" aria-label="مرحلة الطلب">
                        @foreach($linearFlow as $idx => $step)
                            @php
                                $reached = $currentIdx !== false && $idx <= $currentIdx;
                                $current = $idx === $currentIdx;
                            @endphp
                            <div class="flex-1 h-1.5 rounded-full transition
                                        {{ $reached ? 'bg-mint-500' : 'bg-ink-950/10' }}
                                        {{ $current ? 'animate-pulse' : '' }}"></div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-blush-50 ring-1 ring-blush-500/20 rounded-xl p-2 text-[11px] font-bold text-blush-600 mb-3 text-center">
                        ✗ الأوردر اتلغى
                    </div>
                @endunless

                {{-- Items --}}
                <div class="bg-cream-100/70 rounded-xl p-2.5 space-y-1 mb-3">
                    @foreach($o->items as $it)
                        <div class="flex items-center justify-between text-[12px]">
                            <span class="text-ink-950 font-bold truncate">{{ $it->name }} × {{ $it->qty }}</span>
                            <span class="text-ink-500 shrink-0" dir="ltr">{{ $fmt((float) $it->line_total) }} {{ $o->currency }}</span>
                        </div>
                    @endforeach

                    {{-- Totals --}}
                    <div class="border-t border-ink-950/8 mt-2 pt-1.5 space-y-1">
                        <div class="flex items-center justify-between text-[11px] text-ink-500">
                            <span>الأصناف</span>
                            <span dir="ltr">{{ $fmt((float) $o->subtotal) }} {{ $o->currency }}</span>
                        </div>
                        @if((float) $o->delivery_fee > 0)
                            <div class="flex items-center justify-between text-[11px] text-ink-500">
                                <span>🛵 الشحن</span>
                                <span dir="ltr">{{ $fmt((float) $o->delivery_fee) }} {{ $o->currency }}</span>
                            </div>
                        @elseif($o->area_id)
                            <div class="flex items-center justify-between text-[11px] text-mint-700 font-bold">
                                <span>🛵 الشحن</span>
                                <span>مجاناً</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between pt-1 border-t border-ink-950/8">
                            <span class="text-[11px] font-extrabold text-ink-950">الإجمالي</span>
                            <span class="text-sm font-black text-coral-600" dir="ltr">
                                {{ $fmt($o->grandTotal()) }} {{ $o->currency }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Quick actions: call/whatsapp business + view their page --}}
                <div class="flex flex-wrap gap-1.5">
                    @if($o->business->phone)
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $o->business->phone) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-ink-950 text-white text-[11px] font-extrabold hover:bg-ink-800 transition">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-3 h-3">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            اتصل
                        </a>
                    @endif
                    @if($o->business->whatsapp)
                        <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($o->business->whatsapp) }}?text={{ urlencode('سلام، استفسار عن أوردر #'.$o->id.' من بنهاوي') }}"
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-mint-100 text-mint-700 text-[11px] font-extrabold hover:bg-mint-500 hover:text-white transition">
                            <x-icon name="whatsapp" class="w-3 h-3"/>
                            واتساب
                        </a>
                    @endif
                    @if($o->status === 'completed')
                        <a href="{{ route('menu.public', $o->business) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-coral-500 text-white text-[11px] font-extrabold hover:bg-coral-600 transition ms-auto">
                            اطلب تاني
                        </a>
                    @endif
                </div>

                @if($o->notes)
                    <p class="text-[11px] text-ink-500 mt-2 bg-cream-100/60 rounded-lg p-2 leading-relaxed">📝 {{ $o->notes }}</p>
                @endif
            </div>
        @endforeach
    </div>

</div>
@endsection
