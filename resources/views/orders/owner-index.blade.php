@extends('layouts.app', [
    'title' => 'طلبات · ' . $business->name . ' · بنهاوي',
])

@php
    $tones = [
        'pending'          => ['bg' => 'bg-honey-100', 'text' => 'text-honey-700', 'dot' => 'bg-honey-500'],
        'confirmed'        => ['bg' => 'bg-mint-100',  'text' => 'text-mint-700',  'dot' => 'bg-mint-500'],
        'preparing'        => ['bg' => 'bg-coral-100', 'text' => 'text-coral-600', 'dot' => 'bg-coral-500'],
        'out_for_delivery' => ['bg' => 'bg-coral-100', 'text' => 'text-coral-600', 'dot' => 'bg-coral-500'],
        'completed'        => ['bg' => 'bg-mint-100',  'text' => 'text-mint-700',  'dot' => 'bg-mint-600'],
        'cancelled'        => ['bg' => 'bg-blush-100', 'text' => 'text-blush-600', 'dot' => 'bg-blush-500'],
    ];

    $waSendLabels = [
        'pending'   => ['label' => 'لسه ما اتبعتش', 'cls' => 'text-honey-700'],
        'sent'      => ['label' => 'اتبعت على واتساب', 'cls' => 'text-mint-700'],
        'simulated' => ['label' => 'وضع تجريبي (لم يُرسل فعلياً)', 'cls' => 'text-ink-500'],
        'failed'    => ['label' => 'فشل الإرسال — كلّم العميل يدويًا', 'cls' => 'text-blush-600'],
    ];
@endphp

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('directory.show', $business) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <span class="text-xs font-bold text-ink-500 truncate">{{ $business->name }}</span>
        <a href="{{ route('menu.manage', $business) }}"
           class="ms-auto text-xs font-bold text-coral-600 hover:underline">إدارة المنيو</a>
    </div>

    <h1 class="text-xl font-black text-ink-950 mb-1">طلبات المطعم</h1>
    <p class="text-xs text-ink-500 mb-3">
        كل أوردر بييجي من بنهاوي بيظهر هنا، وبيتبعتلك على واتساب تلقائيًا.
    </p>

    @if(session('flash'))
        <div class="card-light bg-mint-50 ring-1 ring-mint-500/30 p-3 mb-3 text-sm font-bold text-mint-700">
            {{ session('flash') }}
        </div>
    @endif

    <div class="grid grid-cols-3 gap-2 mb-3">
        <div class="card-light p-3 text-center">
            <div class="text-[10px] font-bold text-ink-500">شغّالة</div>
            <div class="text-2xl font-black text-coral-600 mt-1">{{ $counts['active'] }}</div>
        </div>
        <div class="card-light p-3 text-center">
            <div class="text-[10px] font-bold text-ink-500">تمّت</div>
            <div class="text-2xl font-black text-mint-700 mt-1">{{ $counts['completed'] }}</div>
        </div>
        <div class="card-light p-3 text-center">
            <div class="text-[10px] font-bold text-ink-500">ملغية</div>
            <div class="text-2xl font-black text-blush-600 mt-1">{{ $counts['cancelled'] }}</div>
        </div>
    </div>

    <div class="flex gap-2 overflow-x-auto scrollbar-hide mb-3 -mx-4 px-4">
        @foreach([
            'active'    => 'الشغّالة',
            'pending'   => 'بانتظار التأكيد',
            'preparing' => 'بتتجهز',
            'completed' => 'تمّت',
            'cancelled' => 'ملغية',
        ] as $key => $label)
            <a href="{{ route('order.owner.index', ['business' => $business, 'filter' => $key]) }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-xs font-extrabold transition
                      {{ $filter === $key ? 'bg-coral-500 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if($orders->isEmpty())
        <div class="card-light p-10 text-center">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-14 h-14 mx-auto text-ink-300 mb-2">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>
            </svg>
            <p class="text-sm font-bold text-ink-500">مفيش طلبات في الفلتر ده</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($orders as $o)
                @php
                    $tone = $tones[$o->status] ?? $tones['pending'];
                    $wa = $waSendLabels[$o->wa_send_status] ?? null;
                @endphp
                <div class="card-light p-3">
                    <div class="flex items-start gap-3">
                        <div class="w-14 text-center shrink-0 rounded-xl pill-coral py-2">
                            <div class="text-[9px] font-bold">طلب</div>
                            <div class="text-base font-black leading-none" dir="ltr">#{{ $o->id }}</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-extrabold text-ink-950">{{ $o->customer_name }}</span>
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $tone['bg'] }} {{ $tone['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $tone['dot'] }}"></span>
                                    {{ $o->statusLabel() }}
                                </span>
                                <span class="text-[10px] text-ink-400 ms-auto">{{ $o->created_at->diffForHumans() }}</span>
                            </div>
                            <a href="tel:{{ $o->customer_phone }}" class="text-[11px] text-coral-600 hover:underline" dir="ltr">{{ $o->customer_phone }}</a>
                            @if($o->customer_address)
                                <div class="text-[11px] text-ink-500 mt-0.5">📍 {{ $o->customer_address }}</div>
                            @endif

                            <div class="mt-2 bg-cream-100/70 rounded-xl p-2 space-y-1">
                                @foreach($o->items as $it)
                                    <div class="flex items-center justify-between text-[12px]">
                                        <span class="text-ink-950 font-bold">{{ $it->name }} × {{ $it->qty }}</span>
                                        <span class="text-coral-600 font-black" dir="ltr">{{ rtrim(rtrim(number_format($it->line_total, 2), '0'), '.') }} {{ $o->currency }}</span>
                                    </div>
                                @endforeach
                                <div class="flex items-center justify-between pt-1 border-t border-ink-950/8 mt-1">
                                    <span class="text-[11px] font-bold text-ink-500">الإجمالي</span>
                                    <span class="text-sm font-black text-ink-950" dir="ltr">{{ rtrim(rtrim(number_format($o->subtotal, 2), '0'), '.') }} {{ $o->currency }}</span>
                                </div>
                            </div>

                            @if($o->notes)
                                <p class="text-[11px] text-ink-500 mt-2 bg-cream-100 rounded-lg p-2 leading-relaxed">📝 {{ $o->notes }}</p>
                            @endif

                            @if($wa)
                                <p class="text-[10px] font-bold mt-2 {{ $wa['cls'] }}">{{ $wa['label'] }}</p>
                            @endif

                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @if($o->status === 'pending')
                                    <form method="POST" action="{{ route('order.status.update', $o) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-mint-500 text-white hover:bg-mint-600 transition">✓ أكّد</button>
                                    </form>
                                @endif
                                @if(in_array($o->status, ['pending', 'confirmed']))
                                    <form method="POST" action="{{ route('order.status.update', $o) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="preparing">
                                        <button class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-coral-100 text-coral-600 hover:bg-coral-500 hover:text-white transition">بيتجهز</button>
                                    </form>
                                @endif
                                @if(in_array($o->status, ['pending', 'confirmed', 'preparing']))
                                    <form method="POST" action="{{ route('order.status.update', $o) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="out_for_delivery">
                                        <button class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-white ring-1 ring-ink-950/8 text-ink-950 hover:ring-coral-500 transition">في الطريق</button>
                                    </form>
                                @endif
                                @if(! in_array($o->status, ['completed', 'cancelled']))
                                    <form method="POST" action="{{ route('order.status.update', $o) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-mint-500 text-white hover:bg-mint-600 transition">تمّ</button>
                                    </form>
                                    <form method="POST" action="{{ route('order.status.update', $o) }}" class="inline"
                                          data-confirm="إلغاء الطلب؟" data-confirm-tone="danger">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-blush-100 text-blush-600 hover:bg-blush-500 hover:text-white transition">إلغي</button>
                                    </form>
                                @endif
                                <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($o->customer_phone) }}"
                                   target="_blank" rel="noopener"
                                   class="ms-auto text-[10px] font-extrabold px-2.5 py-1 rounded-full text-white transition hover:scale-[1.02]"
                                   style="background: linear-gradient(135deg, #25D366, #128C7E)">
                                    كلّم العميل
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
