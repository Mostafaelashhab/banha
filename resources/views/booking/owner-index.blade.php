@extends('layouts.app', [
    'title' => 'حجوزات · ' . $business->name . ' · بنهاوي',
])

@php
    $tones = [
        'pending'   => ['bg' => 'bg-honey-100', 'text' => 'text-honey-700', 'dot' => 'bg-honey-500'],
        'confirmed' => ['bg' => 'bg-mint-100',  'text' => 'text-mint-700',  'dot' => 'bg-mint-500'],
        'cancelled' => ['bg' => 'bg-blush-100', 'text' => 'text-blush-600', 'dot' => 'bg-blush-500'],
        'completed' => ['bg' => 'bg-mint-100',  'text' => 'text-mint-700',  'dot' => 'bg-mint-600'],
        'no_show'   => ['bg' => 'bg-ink-100',   'text' => 'text-ink-500',   'dot' => 'bg-ink-400'],
    ];
@endphp

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Top bar --}}
    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('directory.show', $business) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <span class="text-xs font-bold text-ink-500 truncate">{{ $business->name }}</span>
        <a href="{{ route('directory.edit', $business) }}#booking"
           class="ms-auto text-xs font-bold text-coral-600 hover:underline">إعدادات الحجز</a>
    </div>

    <h1 class="text-xl font-black text-ink-950 mb-1">حجوزاتك</h1>
    <p class="text-xs text-ink-500 mb-3">
        كل من حجز موعد عند نشاطك. ممكن تأكد، تلغي، أو تعلّم الموعد كـ "تم".
    </p>

    @if(session('flash'))
        <div class="card-light bg-mint-50 ring-1 ring-mint-500/30 p-3 mb-3 text-sm font-bold text-mint-700">
            {{ session('flash') }}
        </div>
    @endif

    {{-- Quick stats --}}
    <div class="grid grid-cols-3 gap-2 mb-3">
        <div class="card-light p-3 text-center">
            <div class="text-[10px] font-bold text-ink-500">بانتظار التأكيد</div>
            <div class="text-2xl font-black text-honey-700 mt-1">{{ $counts['pending'] }}</div>
        </div>
        <div class="card-light p-3 text-center">
            <div class="text-[10px] font-bold text-ink-500">الجاية</div>
            <div class="text-2xl font-black text-mint-700 mt-1">{{ $counts['upcoming'] }}</div>
        </div>
        <div class="card-light p-3 text-center">
            <div class="text-[10px] font-bold text-ink-500">السابقة</div>
            <div class="text-2xl font-black text-ink-500 mt-1">{{ $counts['past'] }}</div>
        </div>
    </div>

    {{-- Filter chips --}}
    <div class="flex gap-2 overflow-x-auto scrollbar-hide mb-3 -mx-4 px-4">
        @foreach([
            'upcoming' => 'الجاية',
            'pending'  => 'بانتظار التأكيد',
            'past'     => 'السابقة',
            'all'      => 'الكل',
        ] as $key => $label)
            <a href="{{ route('booking.owner.index', ['business' => $business, 'filter' => $key]) }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-xs font-extrabold transition
                      {{ $filter === $key ? 'bg-coral-500 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- List --}}
    @if($bookings->isEmpty())
        <div class="card-light p-10 text-center">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-14 h-14 mx-auto text-ink-300 mb-2">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <p class="text-sm font-bold text-ink-500">مفيش حجوزات في الفلتر ده</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($bookings as $b)
                @php $tone = $tones[$b->status] ?? $tones['pending']; @endphp
                <div class="card-light p-3">
                    <div class="flex items-start gap-3">
                        <div class="w-12 text-center shrink-0 rounded-xl pill-coral py-2">
                            <div class="text-[9px] font-bold uppercase">{{ $b->starts_at->translatedFormat('M') }}</div>
                            <div class="text-lg font-black leading-none">{{ $b->starts_at->translatedFormat('d') }}</div>
                            <div class="text-[9px] font-bold">{{ $b->starts_at->translatedFormat('D') }}</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-extrabold text-ink-950" dir="ltr">{{ $b->starts_at->translatedFormat('h:i a') }}</span>
                                <span class="text-[10px] text-ink-500">· {{ $b->duration_minutes }}د</span>
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $tone['bg'] }} {{ $tone['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $tone['dot'] }}"></span>
                                    {{ $b->statusLabel() }}
                                </span>
                            </div>
                            <div class="text-sm font-bold text-ink-950 mt-1 truncate">{{ $b->name }}</div>
                            <a href="tel:{{ $b->phone }}" class="text-[11px] text-coral-600 hover:underline" dir="ltr">{{ $b->phone }}</a>
                            @if($b->notes)
                                <p class="text-[11px] text-ink-500 mt-1 bg-cream-100 rounded-lg p-2 leading-relaxed">{{ $b->notes }}</p>
                            @endif

                            @php
                                $waLabels = [
                                    'pending'   => ['label' => 'لسه ما اتبعتش',                    'cls' => 'text-honey-700'],
                                    'sent'      => ['label' => 'اتبعت على واتساب',                'cls' => 'text-mint-700'],
                                    'simulated' => ['label' => 'وضع تجريبي (لم يُرسل فعلياً)',    'cls' => 'text-ink-500'],
                                    'failed'    => ['label' => 'فشل الإرسال — كلّم العميل يدويًا','cls' => 'text-blush-600'],
                                ];
                                $wa = $waLabels[$b->wa_send_status ?? 'pending'] ?? null;
                            @endphp
                            @if($wa)
                                <p class="text-[10px] font-bold mt-1.5 {{ $wa['cls'] }}">{{ $wa['label'] }}</p>
                            @endif

                            {{-- Quick actions --}}
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @if($b->status === 'pending')
                                    <form method="POST" action="{{ route('booking.status.update', $b) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-mint-500 text-white hover:bg-mint-600 transition">✓ أكّد</button>
                                    </form>
                                @endif
                                @if(in_array($b->status, ['pending', 'confirmed']))
                                    <form method="POST" action="{{ route('booking.status.update', $b) }}" class="inline"
                                          data-confirm="إلغاء الحجز؟" data-confirm-tone="danger">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-blush-100 text-blush-600 hover:bg-blush-500 hover:text-white transition">إلغي</button>
                                    </form>
                                    <form method="POST" action="{{ route('booking.status.update', $b) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-white ring-1 ring-ink-950/8 text-ink-950 hover:ring-mint-500 transition">تم</button>
                                    </form>
                                    <form method="POST" action="{{ route('booking.status.update', $b) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="no_show">
                                        <button class="text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-white ring-1 ring-ink-950/8 text-ink-500 hover:ring-ink-950 transition">لم يحضر</button>
                                    </form>
                                @endif
                                @if($business->whatsapp)
                                    <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($b->phone) }}"
                                       target="_blank" rel="noopener"
                                       class="ms-auto text-[10px] font-extrabold px-2.5 py-1 rounded-full text-white transition hover:scale-[1.02]"
                                       style="background: linear-gradient(135deg, #25D366, #128C7E)">
                                        واتساب
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
