@extends('layouts.app', [
    'title' => 'مراجعة طلبات التفعيل · أدمن',
])

@php
    $tones = [
        'pending'  => ['bg' => 'bg-honey-100', 'text' => 'text-honey-700'],
        'approved' => ['bg' => 'bg-mint-100',  'text' => 'text-mint-700'],
        'rejected' => ['bg' => 'bg-blush-100', 'text' => 'text-blush-600'],
    ];
@endphp

@section('content')
<div class="max-w-2xl mx-auto" data-no-edge-swipe>

    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('admin.dashboard') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <span class="text-xs font-bold text-ink-500">مراجعة طلبات تفعيل البادج</span>
    </div>

    @if(session('flash'))
        <div class="card-light bg-mint-50 ring-1 ring-mint-500/30 p-3 mb-3 text-sm font-bold text-mint-700">
            {{ session('flash') }}
        </div>
    @endif

    <h1 class="text-xl font-black text-ink-950 mb-1">طلبات تفعيل الـ Verified Badge</h1>
    <p class="text-xs text-ink-500 mb-3">راجع كل دفعة، شيك في الـ proof، ووافق أو ارفض.</p>

    {{-- Counts --}}
    <div class="grid grid-cols-3 gap-2 mb-3">
        <div class="card-light p-3 text-center">
            <div class="text-[10px] font-bold text-ink-500">بانتظار</div>
            <div class="text-2xl font-black text-honey-700 mt-1">{{ $counts['pending'] }}</div>
        </div>
        <div class="card-light p-3 text-center">
            <div class="text-[10px] font-bold text-ink-500">اتفعّل</div>
            <div class="text-2xl font-black text-mint-700 mt-1">{{ $counts['approved'] }}</div>
        </div>
        <div class="card-light p-3 text-center">
            <div class="text-[10px] font-bold text-ink-500">اترفض</div>
            <div class="text-2xl font-black text-blush-600 mt-1">{{ $counts['rejected'] }}</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="flex gap-2 overflow-x-auto scrollbar-hide mb-3 -mx-4 px-4">
        @foreach(['pending'=>'بانتظار','approved'=>'اتفعّل','rejected'=>'اترفض','all'=>'الكل'] as $key=>$label)
            <a href="{{ route('verify.admin', ['filter' => $key]) }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-xs font-extrabold transition
                      {{ $filter === $key ? 'bg-coral-500 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- List --}}
    @if($payments->isEmpty())
        <div class="card-light p-10 text-center">
            <div class="text-5xl mb-2">★</div>
            <p class="text-sm font-bold text-ink-500">مفيش طلبات في الفلتر ده</p>
        </div>
    @else
    <div class="space-y-3">
        @foreach($payments as $p)
            @php $tone = $tones[$p->status] ?? $tones['pending']; @endphp
            <div class="card-light p-4">
                <div class="flex items-start gap-3 mb-3">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('directory.show', $p->business) }}" class="text-sm font-extrabold text-ink-950 hover:underline">
                            {{ $p->business->name ?? '— نشاط محذوف —' }}
                        </a>
                        <div class="text-[11px] text-ink-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                            <span class="font-bold">{{ $p->methodLabel() }}</span>
                            <span>·</span>
                            <span class="font-extrabold text-coral-600">{{ number_format($p->amount) }} ج / {{ $p->months }} شهر</span>
                            <span>·</span>
                            <span>{{ $p->created_at->diffForHumans() }}</span>
                        </div>
                        @if($p->user)
                            <div class="text-[11px] text-ink-500 mt-0.5">
                                صاحب الطلب: <span class="font-bold text-ink-950">{{ '@'.$p->user->username }}</span>
                            </div>
                        @endif
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $tone['bg'] }} {{ $tone['text'] }} shrink-0">
                        {{ $p->statusLabel() }}
                    </span>
                </div>

                @if($p->transaction_id || $p->proof_url || $p->note)
                    <div class="bg-cream-100 rounded-xl p-3 mb-3 space-y-2">
                        @if($p->transaction_id)
                            <div>
                                <div class="text-[10px] font-bold text-ink-500">رقم العملية</div>
                                <code class="text-sm font-extrabold text-coral-600 select-all" dir="ltr">{{ $p->transaction_id }}</code>
                            </div>
                        @endif
                        @if($p->proof_url)
                            <div>
                                <div class="text-[10px] font-bold text-ink-500 mb-1">إثبات التحويل</div>
                                <a href="{{ $p->proof_url }}" target="_blank" rel="noopener">
                                    <img src="{{ $p->proof_url }}" alt="proof" class="max-w-full h-auto max-h-64 rounded-lg ring-1 ring-ink-950/8" loading="lazy">
                                </a>
                            </div>
                        @endif
                        @if($p->note)
                            <div>
                                <div class="text-[10px] font-bold text-ink-500">ملاحظة من صاحب النشاط</div>
                                <p class="text-xs text-ink-700">{{ $p->note }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                @if($p->status === 'rejected' && $p->reject_reason)
                    <div class="bg-blush-50 rounded-xl p-3 mb-3">
                        <div class="text-[10px] font-bold text-blush-600">سبب الرفض</div>
                        <p class="text-xs text-ink-700">{{ $p->reject_reason }}</p>
                    </div>
                @endif

                @if($p->status === 'pending')
                    <div class="grid grid-cols-2 gap-2">
                        <form method="POST" action="{{ route('verify.approve', $p) }}">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-full bg-mint-500 text-white text-xs font-extrabold hover:bg-mint-600 transition">
                                ✓ وافق وفعّل
                            </button>
                        </form>
                        <button type="button"
                                onclick="document.getElementById('reject-{{ $p->id }}').classList.toggle('hidden')"
                                class="w-full py-2.5 rounded-full bg-blush-100 text-blush-600 text-xs font-extrabold hover:bg-blush-500 hover:text-white transition">
                            × ارفض
                        </button>
                    </div>
                    <form id="reject-{{ $p->id }}" method="POST" action="{{ route('verify.reject', $p) }}" class="hidden mt-2 space-y-2">
                        @csrf
                        <textarea name="reject_reason" rows="2" maxlength="300" required minlength="5"
                                  placeholder="السبب (هيظهر لصاحب النشاط)"
                                  class="w-full bg-cream-100 rounded-xl px-3 py-2 text-xs outline-0 border border-ink-950/8 resize-none"></textarea>
                        <button type="submit" class="w-full py-2 rounded-full bg-blush-500 text-white text-xs font-extrabold">أكّد الرفض</button>
                    </form>
                @endif

                @if($p->status === 'approved')
                    <div class="text-[11px] text-mint-700 font-bold">
                        ✓ اتفعّل {{ $p->reviewed_at?->diffForHumans() }} بواسطة {{ $p->reviewed_by_admin }}
                        @if($p->business?->verified_paid_until)
                            · ساري حتى {{ $p->business->verified_paid_until->translatedFormat('d M Y') }}
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
