@extends('layouts.app', ['title' => 'محفظتي · بنهاوي'])

@php
    use App\Models\PointTransaction;

    $egp = intdiv($availableBalance, $pointsPerEgp);
    $withdrawableEgp = intdiv($withdrawableBalance, $pointsPerEgp);
    $reservedPts = max(0, ((int) $user->reputation) - $availableBalance);
    $heldPts = max(0, $availableBalance - $withdrawableBalance);

    // Earn opportunities — surface the high-value ones, with friendly labels
    $earnCards = [
        ['code' => 'invite_settled',    'title' => 'ادعي صحابك',          'desc' => 'كل صاحب يدخل ويفعّل = ٣٠ نقطة في رصيدك', 'icon' => 'user',     'pts' => 30,  'cap' => '٥ يومياً',  'highlight' => true],
        ['code' => 'business_claimed',  'title' => 'أكّد ملكية نشاطك',     'desc' => 'لو عندك محل، ضمّه لبنهاوي ووثّقه',           'icon' => 'briefcase', 'pts' => 200, 'cap' => 'مرة واحدة'],
        ['code' => 'business_verified', 'title' => 'وثّق نشاطك',           'desc' => 'بعد المراجعة هتاخد علامة موثّق + ١٠٠ نقطة',  'icon' => 'check',    'pts' => 100, 'cap' => 'مرة واحدة'],
        ['code' => 'review_business',   'title' => 'قيّم الأماكن',         'desc' => 'كل تقييم بـ ٥ نقاط، اقصى ٥ تقييمات يومياً',    'icon' => 'star',     'pts' => 5,   'cap' => '٥ يومياً'],
        ['code' => 'alert_confirmed',   'title' => 'بلّغ عن مشكلة',         'desc' => 'بلاغ يتأكّد = ١٠ نقاط',                       'icon' => 'bolt',     'pts' => 10,  'cap' => '٣ يومياً'],
        ['code' => 'daily_login',       'title' => 'سجّل دخول كل يوم',     'desc' => 'افتح بنهاوي مرة في اليوم وتلاقي نقاطك زادت',  'icon' => 'home',     'pts' => 2,   'cap' => 'يومي'],
    ];

    $inviteUrl = url('/signup?ref=' . urlencode($user->username));
@endphp

@section('content')
<div class="max-w-2xl mx-auto pb-6">

    {{-- ───── Hero balance card ────────────────────────────────── --}}
    <section class="wallet-card rise rise-1 mb-5">
        <div class="wallet-card-grid" aria-hidden="true"></div>
        <div class="wallet-card-shine" aria-hidden="true"></div>

        <div class="wallet-card-row">
            <div class="wallet-card-label">رصيدك المتاح</div>
            <span class="wallet-card-chip">
                <span class="wallet-card-chip-dot"></span>
                {{ number_format($availableBalance) }} نقطة
            </span>
        </div>

        <div class="wallet-card-amount">
            <span class="wallet-card-amount-num">{{ number_format($egp) }}</span>
            <span class="wallet-card-amount-cur">ج.م</span>
        </div>

        @php
            $threshold = $minEgp * $pointsPerEgp;          // points needed to withdraw
            $progress  = $threshold > 0 ? min(100, ($withdrawableBalance / $threshold) * 100) : 100;
            $canCash   = $withdrawableEgp >= $minEgp;
            $needPts   = max(0, $threshold - $withdrawableBalance);
        @endphp

        <div class="wallet-card-progress">
            <div class="wallet-card-progress-row">
                <span>{{ $canCash ? 'جاهز للسحب' : 'لباب الـ' . $minEgp . ' جنيه' }}</span>
                <span class="font-extrabold">
                    {{ $canCash ? number_format($withdrawableEgp) . ' ج.م' : number_format($needPts) . ' نقطة باقية' }}
                </span>
            </div>
            <div class="wallet-card-progress-bar">
                <span style="width: {{ $progress }}%"></span>
            </div>
        </div>

        <div class="wallet-card-actions">
            <button type="button"
                    @if($canCash) onclick="document.getElementById('withdraw-modal')?.showModal()" @else disabled @endif
                    class="wallet-cta-pri {{ $canCash ? '' : 'is-disabled' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <path d="M7 11l5-5 5 5"/><path d="M12 6v12"/>
                </svg>
                اسحب فلوسك
            </button>
            <a href="#earn" class="wallet-cta-sec">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                اكسب
            </a>
        </div>
    </section>

    {{-- ───── Invite — compact card ──────────────────────────────── --}}
    <section class="invite-compact rise rise-2 mb-6">
        <div class="invite-compact-head">
            <span class="invite-compact-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15 8 22 9 17 14 18 21 12 17 6 21 7 14 2 9 9 8"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-black text-ink-950">
                    ادعي صاحب = <span class="text-coral-600">١٥ جنيه</span>
                </div>
                <div class="text-[11px] text-ink-500 mt-0.5 leading-snug">
                    أول ما صاحبك يفعّل حسابه بـ OTP تاخد ٣٠ نقطة.
                </div>
            </div>
        </div>

        <div class="invite-link" id="invite-link" data-url="{{ $inviteUrl }}">
            <span class="invite-link-url">{{ $inviteUrl }}</span>
            <button type="button" class="invite-copy" onclick="copyInvite(this)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                    <rect x="9" y="9" width="13" height="13" rx="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
                <span class="invite-copy-label">انسخ</span>
            </button>
        </div>

        <a href="https://wa.me/?text={{ urlencode('انضم بنهاوي — دليل مدينة بنها الكامل: ' . $inviteUrl) }}"
           target="_blank" rel="noopener"
           class="invite-share">
            <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                <path d="M20.5 3.5A10 10 0 0 0 4.4 16l-1.4 5.1 5.2-1.4a10 10 0 0 0 14.8-8.7c0-2.6-1-5.2-2.9-7.1ZM12.1 20.6c-1.6 0-3.2-.4-4.6-1.2l-.3-.2-3 .8.8-3-.2-.3a8.2 8.2 0 0 1 13.9-7.1 8.2 8.2 0 0 1-6.6 11.1Z"/>
            </svg>
            ابعت على واتس
        </a>
    </section>

    {{-- ───── How to earn — compact one-line rows ────────────── --}}
    <section id="earn" class="mb-6 rise rise-3">
        <h2 class="text-base font-extrabold text-ink-950 mb-2 px-1 inline-flex items-center gap-1.5">
            <span class="text-coral-500">⚡</span> اكسب نقاط
        </h2>
        <div class="earn-list">
            @foreach($earnCards as $card)
                <div class="earn-row {{ ($card['highlight'] ?? false) ? 'earn-row--hl' : '' }}">
                    <span class="earn-row-icon">
                        <x-icon :name="$card['icon']" class="w-4 h-4"/>
                    </span>
                    <div class="earn-row-text">
                        <div class="earn-row-title">{{ $card['title'] }}</div>
                        <div class="earn-row-cap">{{ $card['cap'] }}</div>
                    </div>
                    <span class="earn-row-pts">+{{ $card['pts'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ───── Recent transactions ─────────────────────────────────── --}}
    @if($transactions->isNotEmpty())
        <section class="rise rise-4 mb-6">
            <h2 class="text-base font-extrabold text-ink-950 mb-3 px-1">آخر المعاملات</h2>
            <div class="card-light divide-y divide-ink-950/5">
                @foreach($transactions as $tx)
                    @php
                        $positive = $tx->delta > 0;
                        $label    = PointTransaction::reasonLabel($tx->reason);
                    @endphp
                    <div class="flex items-center gap-3 p-3">
                        <span class="w-9 h-9 rounded-full grid place-items-center shrink-0 {{ $positive ? 'bg-mint-100 text-mint-700' : 'bg-blush-100 text-blush-500' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4">
                                @if($positive)
                                    <polyline points="18 15 12 9 6 15"/>
                                @else
                                    <polyline points="6 9 12 15 18 9"/>
                                @endif
                            </svg>
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-ink-950 truncate">{{ $label }}</div>
                            <div class="text-[10px] text-ink-400 mt-0.5">{{ $tx->created_at?->diffForHumans() }}</div>
                        </div>
                        <div class="text-sm font-black {{ $positive ? 'text-mint-700' : 'text-blush-500' }}">
                            {{ $positive ? '+' : '' }}{{ number_format($tx->delta) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ───── Withdrawals history ─────────────────────────────────── --}}
    @if($withdrawals->isNotEmpty())
        <section class="rise rise-5 mb-2">
            <h2 class="text-base font-extrabold text-ink-950 mb-3 px-1">طلبات السحب</h2>
            <div class="card-light divide-y divide-ink-950/5">
                @foreach($withdrawals as $w)
                    @php
                        $statusClass = match($w->status) {
                            'paid'     => 'bg-mint-100 text-mint-700',
                            'approved' => 'bg-honey-100 text-honey-700',
                            'pending'  => 'bg-coral-50 text-coral-700',
                            'rejected', 'canceled' => 'bg-blush-100 text-blush-500',
                            default    => 'bg-ink-100 text-ink-500',
                        };
                        $statusLabel = match($w->status) {
                            'paid'     => 'مدفوع',
                            'approved' => 'موافقة',
                            'pending'  => 'قيد المراجعة',
                            'rejected' => 'مرفوض',
                            'canceled' => 'ملغي',
                            default    => $w->status,
                        };
                    @endphp
                    <div class="flex items-center gap-3 p-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-bold text-ink-950">{{ number_format($w->amount_egp) }} جنيه · {{ $w->method }}</div>
                            <div class="text-[10px] text-ink-400 mt-0.5">{{ $w->created_at?->diffForHumans() }}</div>
                        </div>
                        <span class="text-[10px] font-extrabold px-2 py-1 rounded-full {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                        @if($w->status === 'pending')
                            <form action="{{ route('withdrawals.cancel', $w) }}" method="post" onsubmit="return confirm('إلغاء الطلب؟');">
                                @csrf
                                <button type="submit" class="text-[10px] font-bold text-ink-400 hover:text-blush-500">إلغاء</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>

{{-- ───── Withdraw modal ─────────────────────────────────────── --}}
<dialog id="withdraw-modal" class="wallet-modal">
    <form method="post" action="{{ route('withdrawals.store') }}" class="wallet-modal-body">
        @csrf
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-black text-ink-950">اسحب فلوسك</h3>
            <button type="button" onclick="this.closest('dialog').close()" class="text-ink-400 hover:text-ink-950" aria-label="إغلاق">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-5 h-5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <p class="text-xs text-ink-500 mb-4 leading-relaxed">
            الحد الأدنى {{ $minEgp }} جنيه · المراجعة خلال ٤٨ ساعة.
            <br>كل {{ $pointsPerEgp }} نقطة = ١ جنيه.
        </p>

        @if($errors->any())
            <div class="bg-blush-100 text-blush-500 text-xs font-bold p-2.5 rounded-lg mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <label class="block text-xs font-extrabold text-ink-950 mb-1.5">المبلغ بالجنيه</label>
        <input type="number" name="amount_egp" min="{{ $minEgp }}" max="{{ min($maxEgp, $withdrawableEgp) }}"
               required value="{{ $minEgp }}"
               class="w-full rounded-xl border border-ink-950/10 px-3 py-2.5 text-sm font-bold mb-3 focus:outline-none focus:ring-2 focus:ring-coral-500/40">

        <label class="block text-xs font-extrabold text-ink-950 mb-1.5">طريقة الاستلام</label>
        <select name="method" required
                class="w-full rounded-xl border border-ink-950/10 px-3 py-2.5 text-sm font-bold mb-3 focus:outline-none focus:ring-2 focus:ring-coral-500/40">
            <option value="instapay">InstaPay</option>
            <option value="vcash">Vodafone Cash</option>
        </select>

        <label class="block text-xs font-extrabold text-ink-950 mb-1.5">رقم/حساب الاستلام</label>
        <input type="text" name="payout_handle" required minlength="11" maxlength="20"
               placeholder="01xxxxxxxxx" inputmode="numeric"
               class="w-full rounded-xl border border-ink-950/10 px-3 py-2.5 text-sm font-bold mb-4 focus:outline-none focus:ring-2 focus:ring-coral-500/40">

        <button type="submit" class="btn-dark w-full justify-center">
            ابعت الطلب
        </button>
    </form>
</dialog>

@push('scripts')
<script>
    function copyInvite(btn) {
        const wrap = document.getElementById('invite-link');
        if (!wrap) return;
        const url = wrap.dataset.url;
        const label = btn.querySelector('.invite-copy-label');
        const prev = label.textContent;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(() => {
                label.textContent = 'تم!';
                btn.classList.add('is-copied');
                setTimeout(() => { label.textContent = prev; btn.classList.remove('is-copied'); }, 1500);
            });
        }
    }
</script>
@endpush
@endsection
