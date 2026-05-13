@extends('layouts.app', ['title' => 'نقاط '.$user->username.' · أدمن'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ─── Header ─── --}}
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('admin.users') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-extrabold text-ink-950 truncate">نقاط · {{ $user->username }}</h1>
            <p class="text-xs text-ink-500">{{ $user->phone }} · {{ ucfirst($user->verification_tier) }}</p>
        </div>
    </div>

    {{-- ─── Balance card ─── --}}
    <div class="card-light p-5 mb-4 relative overflow-hidden"
         style="background: #2D5BFF;">
        <div class="absolute -top-12 -end-10 w-40 h-40 rounded-full bg-white/20 blur-3xl pointer-events-none"></div>
        <div class="relative">
            <div class="text-white/85 text-[11px] font-bold uppercase tracking-wider">الرصيد الحالي</div>
            <div class="text-white font-black text-4xl leading-none mt-1">{{ number_format($user->reputation) }}</div>
            <div class="text-white/85 text-[11px] mt-2">إجمالي {{ $txs->total() }} عملية</div>
        </div>
    </div>

    {{-- ─── Manual award / penalty form (anti-fraud audit-logged) ─── --}}
    <form method="POST" action="{{ route('admin.users.points.award', $user) }}" class="card-light p-4 mb-4 space-y-3">
        @csrf
        <h3 class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-1.5">
            <x-icon name="more" class="w-4 h-4 text-coral-500"/> تعديل يدوي
        </h3>
        <div class="grid grid-cols-3 gap-2">
            <input type="number" name="delta" placeholder="مثلاً 50 أو -30" required min="-5000" max="5000"
                   class="col-span-1 bg-cream-100 rounded-xl px-3 py-2.5 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 text-sm">
            <input type="text" name="note" placeholder="السبب (للسجل)" maxlength="200"
                   class="col-span-2 bg-cream-100 rounded-xl px-3 py-2.5 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 text-sm">
        </div>
        <button class="btn-primary w-full justify-center !py-2 text-xs">
            طبّق التعديل
        </button>
        <p class="text-[10px] text-ink-400">كل تعديل بيتسجّل بـ IP و الأدمن المسؤول. مفيش طريقة للحذف — بس revoke بكتب صف معاكس.</p>
    </form>

    {{-- ─── Spike detection summary ─── --}}
    @if($byReason->count() > 0)
        <div class="card-light p-4 mb-4">
            <h3 class="text-sm font-extrabold text-ink-950 mb-3">حسب السبب</h3>
            <div class="space-y-1.5 text-xs">
                @foreach($byReason as $r)
                    @php
                        // Flag suspicious patterns
                        $suspicious = ($r->reason === 'daily_login' && $r->c > 30)
                                   || ($r->reason === 'alert_confirmed' && $r->c > 15)
                                   || ($r->reason === 'admin_award'  && $r->c > 5);
                    @endphp
                    <div class="flex items-center gap-2 py-1.5 border-b border-ink-950/5 last:border-0">
                        <span class="text-ink-700 flex-1 truncate {{ $suspicious ? 'text-blush-500 font-bold' : '' }}">
                            @if($suspicious) ⚠️ @endif
                            {{ \App\Models\PointTransaction::reasonLabel($r->reason) }}
                        </span>
                        <span class="text-[11px] text-ink-400 w-16 text-center">{{ $r->c }} مرة</span>
                        <span class="font-extrabold text-sm w-16 text-end {{ $r->s > 0 ? 'text-mint-700' : 'text-blush-500' }}">
                            {{ $r->s > 0 ? '+' : '' }}{{ $r->s }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ─── Transaction log ─── --}}
    <h3 class="text-sm font-extrabold text-ink-950 mb-2 px-1">السجل الكامل</h3>
    @if($txs->isEmpty())
        <div class="card-light p-8 text-center text-ink-500 text-xs">مفيش عمليات.</div>
    @else
        <div class="card-light divide-y divide-ink-950/5 overflow-hidden">
            @foreach($txs as $tx)
                <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-ink-950 truncate">
                            {{ \App\Models\PointTransaction::reasonLabel($tx->reason) }}
                            @if($tx->target_type !== 'self' && $tx->target_type !== 'date')
                                <span class="text-[10px] text-ink-400 font-normal">· {{ $tx->target_type }}#{{ $tx->target_id }}</span>
                            @endif
                        </div>
                        <div class="text-[10px] text-ink-400 mt-0.5">
                            {{ $tx->created_at->format('Y-m-d H:i') }}
                            @if(! empty($tx->meta['ip']))
                                · IP {{ $tx->meta['ip'] }}
                            @endif
                            @if(! empty($tx->meta['note']))
                                · "{{ $tx->meta['note'] }}"
                            @endif
                        </div>
                    </div>
                    <div class="font-extrabold text-sm shrink-0 {{ $tx->delta > 0 ? 'text-mint-700' : 'text-blush-500' }}">
                        {{ $tx->delta > 0 ? '+' : '' }}{{ $tx->delta }}
                    </div>
                    @if($tx->reason !== 'admin_revoke')
                        <form method="POST" action="{{ route('admin.users.points.revoke', $tx) }}"
                              data-confirm="إلغاء العملية دي؟" data-confirm-action="ألغى" data-confirm-tone="danger">
                            @csrf
                            <button type="submit" class="text-blush-500 hover:text-blush-600 text-[10px] font-bold p-1" title="ألغي">
                                <x-icon name="trash" class="w-3.5 h-3.5"/>
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-3">{{ $txs->links() }}</div>
    @endif

</div>
@endsection
