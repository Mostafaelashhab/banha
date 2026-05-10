@extends('layouts.app', ['title' => 'سحوبات الفلوس · أدمن'])

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('admin.dashboard') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-extrabold text-ink-950">طلبات سحب الفلوس</h1>
            <p class="text-xs text-ink-500">تظهر الطلبات بانتظار المراجعة أولاً.</p>
        </div>
    </div>

    {{-- Status filter chips --}}
    <div class="flex items-center gap-2 mb-4 overflow-x-auto scrollbar-hide -mx-4 px-4">
        @php
            $tabs = [
                'pending'   => 'بانتظار المراجعة',
                'approved'  => 'اتعمد · بنحوّل',
                'paid'      => 'اتدفع',
                'rejected'  => 'مرفوض',
                'cancelled' => 'مُلغى',
                'all'       => 'الكل',
            ];
        @endphp
        @foreach($tabs as $k => $lbl)
            @php $c = $k === 'all' ? array_sum($counts) : ($counts[$k] ?? 0); @endphp
            <a href="{{ route('admin.withdrawals', ['status' => $k]) }}"
               class="chip shrink-0 {{ $status === $k ? 'chip-active' : '' }}">
                {{ $lbl }}
                <span class="opacity-60 text-xs">{{ $c }}</span>
            </a>
        @endforeach
    </div>

    @if($withdrawals->isEmpty())
        <div class="card-light p-10 text-center text-ink-500 text-sm">مفيش طلبات في الحالة دي.</div>
    @else
        <div class="space-y-3">
            @foreach($withdrawals as $w)
                @php $meta = $w->statusMeta(); @endphp
                <div class="card-light p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.users.points', $w->user) }}" class="text-sm font-extrabold text-ink-950 hover:underline truncate">
                                {{ $w->user->username }}
                            </a>
                            <div class="text-[11px] text-ink-500 mt-0.5" dir="ltr">
                                {{ $w->user->phone }} · tier: {{ $w->user->verification_tier }} · رصيد {{ $w->user->reputation }}
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-1 rounded-full shrink-0
                                     {{ $meta['tone'] === 'honey'  ? 'bg-honey-100 text-honey-700' : '' }}
                                     {{ $meta['tone'] === 'mint'   ? 'bg-mint-100 text-mint-700' : '' }}
                                     {{ $meta['tone'] === 'blush'  ? 'bg-blush-100 text-blush-500' : '' }}
                                     {{ $meta['tone'] === 'ink'    ? 'bg-ink-950/8 text-ink-500' : '' }}">
                            {{ $meta['label'] }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center mb-3">
                        <div class="bg-cream-100 rounded-xl px-2 py-2">
                            <div class="text-base font-black text-ink-950">{{ number_format($w->amount_egp) }} ج</div>
                            <div class="text-[10px] text-ink-500">المبلغ</div>
                        </div>
                        <div class="bg-cream-100 rounded-xl px-2 py-2">
                            <div class="text-base font-black text-ink-950">{{ number_format($w->points_cost) }}</div>
                            <div class="text-[10px] text-ink-500">نقطة</div>
                        </div>
                        <div class="bg-cream-100 rounded-xl px-2 py-2">
                            <div class="text-sm font-extrabold text-ink-950">{{ $w->methodLabel() }}</div>
                            <div class="text-[10px] text-ink-500" dir="ltr">{{ $w->payout_handle }}</div>
                        </div>
                    </div>

                    <div class="text-[10px] text-ink-400 mb-3">
                        طلب من {{ $w->requested_at->diffForHumans() }}
                        @if(! empty($w->meta['ip']))
                            · IP {{ $w->meta['ip'] }}
                        @endif
                        @if($w->admin)
                            · مراجَعَ بـ {{ $w->admin->username }}
                        @endif
                    </div>

                    @if($w->admin_note)
                        <div class="text-[11px] bg-cream-100 rounded-lg p-2 mb-3 text-ink-700">
                            <b>ملاحظة الأدمن:</b> {{ $w->admin_note }}
                        </div>
                    @endif

                    @if($w->payout_reference)
                        <div class="text-[11px] bg-mint-100 rounded-lg p-2 mb-3 text-mint-700" dir="ltr">
                            <b>Ref:</b> {{ $w->payout_reference }}
                        </div>
                    @endif

                    {{-- Actions per status --}}
                    @if($w->status === 'pending')
                        <div class="grid grid-cols-2 gap-2">
                            <form method="POST" action="{{ route('admin.withdrawals.approve', $w) }}"
                                  data-confirm="موافقة على سحب {{ $w->amount_egp }} ج؟" data-confirm-action="وافق">
                                @csrf
                                <input type="text" name="note" placeholder="ملاحظة (اختياري)"
                                       class="hidden">
                                <button class="w-full px-3 py-2 rounded-xl bg-mint-500 text-white text-xs font-extrabold hover:bg-mint-600 transition">
                                    ✓ وافق + اخصم النقاط
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.withdrawals.reject', $w) }}"
                                  data-confirm="ارفض الطلب؟" data-confirm-action="ارفض" data-confirm-tone="danger">
                                @csrf
                                <input type="text" name="reason" required placeholder="السبب" maxlength="200"
                                       class="hidden" value="رفض إداري">
                                <button class="w-full px-3 py-2 rounded-xl bg-blush-100 text-blush-600 text-xs font-extrabold hover:bg-blush-500 hover:text-white transition">
                                    ✗ ارفض
                                </button>
                            </form>
                        </div>
                    @elseif($w->status === 'approved')
                        <form method="POST" action="{{ route('admin.withdrawals.paid', $w) }}" class="flex gap-2">
                            @csrf
                            <input type="text" name="reference" required maxlength="64"
                                   placeholder="رقم مرجع التحويل (اختياري لكن مهم)"
                                   class="flex-1 bg-cream-100 rounded-xl px-3 py-2 text-xs outline-0 border border-ink-950/8 focus:border-coral-500">
                            <button class="px-4 py-2 rounded-xl bg-mint-500 text-white text-xs font-extrabold hover:bg-mint-600 transition">
                                💰 اتدفع
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $withdrawals->links() }}</div>
    @endif

</div>
@endsection
