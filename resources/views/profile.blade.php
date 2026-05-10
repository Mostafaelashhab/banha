@extends('layouts.app', ['title' => $user->username . ' · بنهاوي'])

@php
    use App\Support\AnonSeed;
    $color   = AnonSeed::avatarColor($user->username);
    $initial = AnonSeed::initial($user->username);
    $tier    = $user->tierMeta();
    $isMe    = $isMe ?? false;
@endphp

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ─── PROFILE HEADER ─── minimal, centered, no colored hero --}}
    <div class="text-center mb-5 pt-2">
        {{-- Avatar — small, centered, with subtle ring --}}
        <div class="relative inline-block">
            <div class="p-0.5 rounded-full bg-gradient-to-br from-coral-500 to-honey-400 inline-block">
                <div class="p-0.5 rounded-full bg-cream-100">
                    <x-avatar :user="$user" size="lg"/>
                </div>
            </div>
            @if($isMe)
                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" data-no-progress="1"
                      class="absolute -bottom-0.5 -end-0.5">
                    @csrf
                    <label class="w-7 h-7 rounded-full bg-ink-950 grid place-items-center text-white shadow-md cursor-pointer hover:bg-coral-500 transition" title="غيّر الصورة">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="this.form.requestSubmit()">
                    </label>
                </form>
            @endif
        </div>

        {{-- Name + tier --}}
        <div class="mt-3 flex items-center justify-center gap-2 flex-wrap">
            <h1 class="text-xl font-black text-ink-950">{{ $user->username }}</h1>
            @if($user->verification_tier === 'gold')
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full text-white" style="background: #1D9BF0">
                    <x-icon name="check" class="w-2.5 h-2.5"/> موثّق
                </span>
            @elseif($user->verification_tier === 'silver')
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-ink-950/8 text-ink-700">
                    🥈 فضي
                </span>
            @elseif($user->verification_tier === 'bronze')
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-mint-100 text-mint-700">
                    ✓ مفعّل
                </span>
            @endif
        </div>

        {{-- Single-line meta --}}
        <div class="text-ink-500 text-xs mt-1.5 flex items-center justify-center gap-1.5 flex-wrap">
            @if($user->isOnline())
                <span class="inline-flex items-center gap-1 text-mint-700 font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-mint-500 animate-pulse"></span>
                    أونلاين
                </span>
                <span class="text-ink-300">·</span>
            @endif
            @if($user->zone)
                <span>📍 {{ $user->zone->name }}</span>
                <span class="text-ink-300">·</span>
            @endif
            <span>من {{ $user->created_at->diffForHumans(['parts' => 1, 'short' => true]) }}</span>
        </div>

        {{-- Inline stats — no card --}}
        <div class="mt-3 flex items-center justify-center gap-4 text-xs text-ink-500">
            <span><b class="text-ink-950">{{ number_format($stats['reputation']) }}</b> نقطة</span>
            <span class="text-ink-300">·</span>
            <span><b class="text-ink-950">{{ $stats['posts'] }}</b> بوست</span>
            <span class="text-ink-300">·</span>
            <span><b class="text-ink-950">{{ $stats['comments'] }}</b> كومنت</span>
        </div>

        {{-- Action button --}}
        <div class="mt-4 flex items-center justify-center gap-2">
            @if($isMe)
                <a href="{{ route('profile.me', ['tab' => 'settings']) }}"
                   class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full bg-white ring-1 ring-ink-950/10 text-ink-950 text-xs font-extrabold hover:bg-cream-100 transition">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z"/>
                    </svg>
                    عدّل البروفايل
                </a>
            @elseif(auth()->check())
                @php $isFollowing = auth()->user()->isFollowing($user->id); @endphp
                <form method="POST" action="{{ route('users.follow', $user) }}" class="inline">
                    @csrf
                    <button class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full text-xs font-extrabold transition
                                   {{ $isFollowing
                                      ? 'bg-white ring-1 ring-ink-950/10 text-ink-950 hover:bg-cream-100'
                                      : 'bg-coral-500 text-white hover:bg-coral-600' }}">
                        {{ $isFollowing ? '✓ متابع' : '+ تابع' }}
                    </button>
                </form>
                <a href="{{ route('chat.open', $user) }}"
                   class="inline-flex items-center gap-1.5 px-5 py-2 rounded-full bg-white ring-1 ring-ink-950/10 text-ink-950 text-xs font-extrabold hover:bg-cream-100 transition">
                    <x-icon name="comment" class="w-3.5 h-3.5"/> رسالة
                </a>
            @endif
        </div>
    </div>

    {{-- ─── TABS ─── flat underline-style (no chips, no scrolling pills) --}}
    @php
        $tabUrl = fn ($t) => $isMe
            ? route('profile.me', ['tab' => $t])
            : route('profile.show', ['username' => $user->username, 'tab' => $t]);

        $tabs = [
            ['key' => 'posts',    'label' => 'بوستات',  'show' => true],
            ['key' => 'listings', 'label' => 'إعلانات', 'show' => true],
            ['key' => 'badges',   'label' => 'شارات',   'show' => true],
        ];
        if ($isMe) {
            $tabs[] = ['key' => 'points',       'label' => 'نقاطي', 'show' => true];
            $tabs[] = ['key' => 'verification', 'label' => 'توثيق', 'show' => true];
            $tabs[] = ['key' => 'settings',     'label' => 'إعداد', 'show' => true];
        }
    @endphp
    <div class="border-b border-ink-950/8 -mx-4 px-4 mb-5">
        <div class="flex items-center gap-1 overflow-x-auto scrollbar-hide">
            @foreach($tabs as $t)
                @php $active = $tab === $t['key']; @endphp
                <a href="{{ $tabUrl($t['key']) }}"
                   class="relative px-3 py-2.5 text-xs font-extrabold whitespace-nowrap transition
                          {{ $active ? 'text-coral-600' : 'text-ink-500 hover:text-ink-950' }}">
                    {{ $t['label'] }}
                    @if($active)
                        <span class="absolute inset-x-2 -bottom-px h-0.5 bg-coral-500 rounded-t-full"></span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    {{-- ─── TAB CONTENT ─── --}}

    @if($tab === 'listings')
        @if($listings && $listings->isNotEmpty())
            <div class="grid grid-cols-2 gap-2.5" data-infinite-scroll>
                @include('marketplace._page', ['listings' => $listings])
            </div>
        @else
            <div class="card-light p-10 text-center text-ink-500">
                @if($isMe)
                    لسه مفيش إعلانات.
                    <div class="mt-4">
                        <a href="{{ route('marketplace.create') }}" class="btn-primary">
                            انشر إعلان
                            <x-icon name="arrow-left" class="w-4 h-4"/>
                        </a>
                    </div>
                @else
                    {{ $user->username }} مفيش عنده إعلانات نشطة.
                @endif
            </div>
        @endif

    @elseif($tab === 'badges')
        @if($earnedBadges->isNotEmpty())
            <h3 class="text-sm font-extrabold text-ink-950 mb-3">شارات اتجمعت ({{ $earnedBadges->count() }})</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                @foreach($earnedBadges as $b)
                    <div class="card-light p-4 text-center">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-2 grid place-items-center text-3xl"
                             style="background: {{ $b->color }}20; border: 1px solid {{ $b->color }}50">
                            {{ $b->emoji }}
                        </div>
                        <div class="text-sm font-extrabold text-ink-950">{{ $b->name }}</div>
                        <div class="text-[11px] text-ink-500 mt-1 leading-snug">{{ $b->description }}</div>
                        <div class="text-[10px] mt-2 inline-block px-2 py-0.5 rounded-full"
                             style="background: {{ $b->color }}20; color: {{ $b->color }}">
                            {{ \App\Models\Badge::TIERS[$b->tier] ?? $b->tier }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($lockedBadges->isNotEmpty())
            <h3 class="text-sm font-extrabold text-ink-950 mb-3">مقفولة ({{ $lockedBadges->count() }})</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($lockedBadges as $b)
                    <div class="card-light p-4 text-center opacity-60 grayscale">
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-2 grid place-items-center text-3xl bg-ink-950/5 border border-ink-950/10">
                            🔒
                        </div>
                        <div class="text-sm font-extrabold text-ink-950">{{ $b->name }}</div>
                        <div class="text-[11px] text-ink-500 mt-1 leading-snug">{{ $b->description }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($earnedBadges->isEmpty() && $lockedBadges->isEmpty())
            <div class="card-light p-10 text-center text-ink-500">مفيش شارات لسه.</div>
        @endif

    @elseif($isMe && $tab === 'points')
        @php
            $withdrawableEgp = intdiv($withdrawableBalance ?? 0, \App\Services\WithdrawalService::POINTS_PER_EGP);
            $reservedPoints  = ($stats['reputation'] ?? 0) - ($availableBalance ?? 0);
            $heldPoints      = ($availableBalance ?? 0) - ($withdrawableBalance ?? 0);
        @endphp

        {{-- ─── POINTS DASHBOARD ─── --}}
        <div class="card-light p-5 mb-4 relative overflow-hidden"
             style="background: linear-gradient(135deg, #FF7A4D 0%, #FFB85C 100%);">
            <div class="absolute -top-12 -end-10 w-40 h-40 rounded-full bg-white/20 blur-3xl pointer-events-none"></div>
            <div class="relative">
                <div class="text-white/85 text-[11px] font-bold uppercase tracking-wider">رصيد نقاطك</div>
                <div class="text-white font-black text-4xl leading-none mt-1">{{ number_format($stats['reputation']) }}</div>
                <div class="text-white/95 text-xs mt-2">
                    يساوي <b>{{ number_format(intdiv($stats['reputation'], \App\Services\WithdrawalService::POINTS_PER_EGP)) }} ج</b>
                    · قابل للسحب الآن: <b>{{ number_format($withdrawableEgp) }} ج</b>
                </div>
            </div>
        </div>

        {{-- ─── WITHDRAW FORM ─── --}}
        <div class="card-light p-5 mb-4">
            <h3 class="text-sm font-extrabold text-ink-950 mb-1 inline-flex items-center gap-1.5">
                <span class="text-coral-500">💸</span> اسحب فلوسك
            </h3>
            <p class="text-[11px] text-ink-500 mb-4 leading-relaxed">
                ٢ نقطة = ١ جنيه · حد أدنى {{ \App\Services\WithdrawalService::MIN_EGP }} ج
                · الموافقة من فريق بنهاوي خلال ٤٨ ساعة.
            </p>

            @if($withdrawableEgp < \App\Services\WithdrawalService::MIN_EGP)
                <div class="bg-cream-100 rounded-2xl p-4 text-center">
                    <div class="text-xs font-bold text-ink-700">
                        محتاج {{ \App\Services\WithdrawalService::MIN_EGP }} ج على الأقل ({{ \App\Services\WithdrawalService::MIN_EGP * \App\Services\WithdrawalService::POINTS_PER_EGP }} نقطة) عشان تسحب.
                    </div>
                    @if($heldPoints > 0)
                        <div class="text-[10px] text-ink-500 mt-2 leading-relaxed">
                            {{ number_format($heldPoints) }} نقطة لسه في فترة الانتظار ({{ \App\Services\WithdrawalService::HOLD_DAYS }} يوم من كسبها قبل ما تنفع للسحب).
                        </div>
                    @endif
                </div>
            @else
                <form method="POST" action="{{ route('withdrawals.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-[11px] font-bold text-ink-500 mb-1 block">المبلغ (بالجنيه)</label>
                        <input type="number" name="amount_egp"
                               min="{{ \App\Services\WithdrawalService::MIN_EGP }}"
                               max="{{ min(\App\Services\WithdrawalService::MAX_EGP, $withdrawableEgp) }}"
                               step="50" required
                               placeholder="مثلاً {{ \App\Services\WithdrawalService::MIN_EGP }}"
                               class="w-full bg-cream-100 rounded-xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 text-sm font-bold">
                        @error('amount') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-[11px] font-bold text-ink-500 mb-1 block">طريقة الدفع</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(\App\Models\Withdrawal::METHODS as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="method" value="{{ $key }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }} required>
                                    <span class="block px-3 py-2.5 rounded-xl bg-cream-100 border border-ink-950/8 text-center text-xs font-bold peer-checked:bg-coral-500 peer-checked:text-white peer-checked:border-coral-500 transition">
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('method') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-[11px] font-bold text-ink-500 mb-1 block">رقم الموبايل (هيستلم عليه)</label>
                        <input type="tel" name="payout_handle" inputmode="numeric" maxlength="11" dir="ltr"
                               value="{{ auth()->user()->phone }}" required
                               placeholder="01xxxxxxxxx"
                               class="w-full bg-cream-100 rounded-xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 text-sm font-bold">
                        @error('payout_handle') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-[10px] text-ink-400 mt-1">لازم رقم مصري ٠١xxxxxxxxx — يفضّل نفس رقم حسابك.</p>
                    </div>

                    <button type="submit" class="btn-primary w-full justify-center !py-3 text-sm"
                            data-confirm="تأكيد طلب السحب؟" data-confirm-action="ابعت">
                        ابعت طلب السحب
                    </button>
                </form>
            @endif

            @if($heldPoints > 0 && $withdrawableEgp >= \App\Services\WithdrawalService::MIN_EGP)
                <div class="bg-honey-50 ring-1 ring-honey-500/20 rounded-xl px-3 py-2 mt-3 text-[11px] text-honey-700">
                    ℹ️ {{ number_format($heldPoints) }} نقطة لسه في انتظار ({{ \App\Services\WithdrawalService::HOLD_DAYS }} يوم) قبل ما تنفع للسحب.
                </div>
            @endif
        </div>

        {{-- ─── My withdrawal history ─── --}}
        @if($withdrawals && $withdrawals->isNotEmpty())
            <h3 class="text-sm font-extrabold text-ink-950 mb-2 px-1">طلباتي</h3>
            <div class="card-light divide-y divide-ink-950/5 overflow-hidden mb-4">
                @foreach($withdrawals as $w)
                    @php $meta = $w->statusMeta(); @endphp
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-extrabold text-ink-950">
                                {{ number_format($w->amount_egp) }} ج · {{ $w->methodLabel() }}
                            </div>
                            <div class="text-[10px] text-ink-400 mt-0.5" dir="ltr">{{ $w->payout_handle }}</div>
                            <div class="text-[10px] text-ink-400 mt-0.5">{{ $w->requested_at->diffForHumans() }}</div>
                            @if($w->admin_note && $w->status === 'rejected')
                                <div class="text-[10px] text-blush-500 mt-1">سبب الرفض: {{ $w->admin_note }}</div>
                            @endif
                        </div>
                        <div class="text-end shrink-0">
                            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-1 rounded-full
                                         {{ $meta['tone'] === 'honey'  ? 'bg-honey-100 text-honey-700' : '' }}
                                         {{ $meta['tone'] === 'mint'   ? 'bg-mint-100 text-mint-700' : '' }}
                                         {{ $meta['tone'] === 'blush'  ? 'bg-blush-100 text-blush-500' : '' }}
                                         {{ $meta['tone'] === 'ink'    ? 'bg-ink-950/8 text-ink-500' : '' }}">
                                {{ $meta['label'] }}
                            </span>
                            @if($w->status === 'pending')
                                <form method="POST" action="{{ route('withdrawals.cancel', $w) }}" class="mt-1.5"
                                      data-confirm="إلغاء الطلب؟" data-confirm-action="ألغى" data-confirm-tone="danger">
                                    @csrf
                                    <button type="submit" class="text-[10px] font-bold text-blush-500 hover:underline">ألغي</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Earning rules cheat-sheet --}}
        <div class="card-light p-4 mb-4">
            <h3 class="text-sm font-extrabold text-ink-950 mb-3 inline-flex items-center gap-1.5">
                <x-icon name="flame" class="w-4 h-4 text-coral-500"/> إزّاي تكسب نقاط
            </h3>
            <div class="space-y-2 text-xs">
                @php
                    $rules = [
                        ['اشترك وفعّل OTP',           '+50',  'مرة واحدة'],
                        ['افتح التطبيق كل يوم',         '+2',   'يومي'],
                        ['أول تنبيه ليك',              '+25',  'مرة واحدة'],
                        ['تنبيهك يتأكّد من ٣ ناس',     '+10',  'لحد ٣/يوم'],
                        ['تقييم محل بـ ريڤيو',          '+5',   '١/محل'],
                        ['تأكيد ملكية نشاطك بـ OTP',   '+200', '١/نشاط'],
                        ['الإدارة توثّق نشاطك',         '+100', '١/نشاط'],
                        ['دعوة صديق فعّل حسابه',       '+30',  'لحد ٥/يوم'],
                    ];
                @endphp
                @foreach($rules as [$label, $delta, $cap])
                    <div class="flex items-center justify-between gap-2 py-1.5 border-b border-ink-950/5 last:border-0">
                        <span class="text-ink-700 flex-1 truncate">{{ $label }}</span>
                        <span class="font-extrabold text-mint-700">{{ $delta }}</span>
                        <span class="text-[10px] text-ink-400 w-20 text-end">{{ $cap }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Recent transactions log --}}
        <h3 class="text-sm font-extrabold text-ink-950 mb-2 px-1">آخر العمليات</h3>
        @if($pointTxs->isEmpty())
            <div class="card-light p-8 text-center text-ink-500 text-xs">
                لسه مفيش عمليات. ابدأ بـ تنبيه أو ريڤيو علشان تشوف نقاطك تتحرّك.
            </div>
        @else
            <div class="card-light divide-y divide-ink-950/5 overflow-hidden">
                @foreach($pointTxs as $tx)
                    <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-ink-950 truncate">
                                {{ \App\Models\PointTransaction::reasonLabel($tx->reason) }}
                            </div>
                            <div class="text-[10px] text-ink-400 mt-0.5">{{ $tx->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="font-extrabold text-sm shrink-0 {{ $tx->delta > 0 ? 'text-mint-700' : 'text-blush-500' }}">
                            {{ $tx->delta > 0 ? '+' : '' }}{{ $tx->delta }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    @elseif($isMe && $tab === 'verification')
        {{-- Verification status panel --}}
        <div class="space-y-3">
            {{-- Current tier --}}
            <div class="card-light p-5">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-12 h-12 rounded-2xl grid place-items-center text-2xl"
                          style="background: {{ $tier['color'] }}20; border: 1px solid {{ $tier['color'] }}50; color: {{ $tier['color'] }}">
                        @if($user->verification_tier === 'gold') ✓
                        @elseif($user->verification_tier === 'silver') 🥈
                        @elseif($user->verification_tier === 'bronze') ✓
                        @else ⏳
                        @endif
                    </span>
                    <div>
                        <div class="text-base font-extrabold text-ink-950">{{ $tier['label'] }}</div>
                        @if($user->verified_at)
                            <div class="text-[11px] text-ink-500">من {{ $user->verified_at->diffForHumans() }}</div>
                        @endif
                    </div>
                </div>
                <p class="text-sm text-ink-500 leading-relaxed">
                    @if($user->verification_tier === 'gold')
                        إنت موثّق ذهبي — حسابك معتمد رسمياً من فريق بنهاوي.
                    @elseif($user->verification_tier === 'silver')
                        إنت موثّق فضي — تنبيهاتك بتتطلب تأكيد واحد بس بدل ٣.
                    @elseif($user->verification_tier === 'bronze')
                        حسابك مفعّل. اشتغل أكتر علشان توصل لمستوى فضي وتاخد مزايا أكتر.
                    @else
                        حسابك لسه مش مفعّل — قريباً هتقدر تفعّله عبر <b>WhatsApp OTP</b>.
                    @endif
                </p>
            </div>

            {{-- Silver progress --}}
            @if($silverProgress && in_array($user->verification_tier, ['none','bronze'], true))
                <div class="card-light p-5">
                    <h3 class="text-sm font-extrabold text-ink-950 mb-1 inline-flex items-center gap-2">
                        🥈 طريقك للموثّق الفضي
                    </h3>
                    <p class="text-xs text-ink-500 mb-4">حقّق الـ ٤ شروط دول واتـ upgrade تلقائياً.</p>

                    <div class="space-y-3">
                        @php
                            $reqs = [
                                ['أيام نشاط', $silverProgress['days']],
                                ['ريبيوتيشن', $silverProgress['reputation']],
                                ['بوستات نشيطة', $silverProgress['posts']],
                            ];
                        @endphp
                        @foreach($reqs as [$lbl, $r])
                            @php
                                $cur = $r['current']; $tgt = $r['target'];
                                $pct = min(100, ($cur / max($tgt, 1)) * 100);
                                $done = $cur >= $tgt;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1.5 text-xs">
                                    <span class="font-bold text-ink-950 inline-flex items-center gap-1.5">
                                        @if($done) <x-icon name="check" class="w-3.5 h-3.5 text-mint-700"/> @endif
                                        {{ $lbl }}
                                    </span>
                                    <span class="text-ink-500"><b class="text-ink-950">{{ $cur }}</b> / {{ $tgt }}</span>
                                </div>
                                <div class="h-2 bg-cream-200 rounded-full overflow-hidden">
                                    <div class="h-full transition-all"
                                         style="width: {{ $pct }}%; background: {{ $done ? 'var(--color-mint-500)' : 'var(--color-coral-500)' }}"></div>
                                </div>
                            </div>
                        @endforeach

                        <div class="flex items-center gap-2 text-xs pt-2 border-t border-ink-950/5">
                            @if($silverProgress['clean_record']['current'])
                                <x-icon name="check" class="w-4 h-4 text-mint-700"/>
                                <span class="text-mint-700 font-bold">سجل نظيف من البلاغات الصحيحة</span>
                            @else
                                <span class="text-blush-500 font-bold">عندك بلاغات valid — ده هيوقفك عن الـ silver</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Gold info --}}
            @if($user->verification_tier !== 'gold')
                <div class="card-light p-5">
                    <h3 class="text-sm font-extrabold text-ink-950 mb-1">🥇 موثّق ذهبي (Gold)</h3>
                    <p class="text-xs text-ink-500 leading-relaxed mb-3">
                        للبيزنس، الدكاترة، الصيدليات، والـ public figures. بنطلبك تبعت رخصة الشغل أو إثبات الهوية،
                        وفريق بنهاوي بيراجعها ويوثّقك يدوياً.
                    </p>
                    <span class="text-xs font-bold text-coral-600 inline-flex items-center gap-1.5">
                        <x-icon name="bell" class="w-3.5 h-3.5"/>
                        قريباً — هنفتح طلبات Gold بعد الـ launch
                    </span>
                </div>
            @endif
        </div>

    @elseif($isMe && $tab === 'settings')
        <div class="space-y-3">
            {{-- Avatar upload --}}
            <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="card-light p-5">
                @csrf
                <h3 class="text-sm font-extrabold text-ink-950 mb-3">صورتك الشخصية</h3>
                <div class="flex items-center gap-3">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="" class="w-16 h-16 rounded-2xl object-cover shrink-0 ring-2 ring-coral-500/20">
                    @else
                        <span class="w-16 h-16 rounded-2xl grid place-items-center text-white font-black text-2xl shrink-0"
                              style="background: {{ $color }}">{{ $initial }}</span>
                    @endif
                    <label class="flex-1 cursor-pointer bg-cream-100 rounded-2xl p-3 border border-ink-950/8 hover:border-coral-500/40 transition text-sm font-bold text-ink-950">
                        <span data-photo-name>{{ $user->avatar_url ? 'استبدل الصورة' : 'ارفع صورة' }}</span>
                        <span class="block text-[10px] text-ink-500 font-normal mt-0.5">JPG / PNG / WEBP · حتى ٢ ميجا</span>
                        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="this.parentElement.querySelector('[data-photo-name]').textContent = this.files[0]?.name || 'استبدل'; this.form.requestSubmit()">
                    </label>
                </div>
                <p class="text-[11px] text-ink-400 mt-3 leading-relaxed">
                    <b class="text-ink-950">ملاحظة:</b>
                    صورتك مش بتظهر لما تنشر بوست/كومنت <b class="text-ink-950">مجهول</b> — خصوصيتك أولاً.
                </p>
                @error('avatar') <p class="text-blush-500 text-xs mt-2">{{ $message }}</p> @enderror

                @if($user->avatar_url)
                    <button type="button" onclick="document.getElementById('delete-avatar').requestSubmit()"
                            class="text-xs font-bold text-blush-500 hover:underline mt-3">احذف الصورة</button>
                @endif
            </form>

            @if($user->avatar_url)
                <form id="delete-avatar" method="POST" action="{{ route('profile.avatar.delete') }}" class="hidden"
                      data-confirm="حذف الصورة؟" data-confirm-action="احذف" data-confirm-tone="danger">
                    @csrf @method('DELETE')
                </form>
            @endif

            {{-- Update profile --}}
            <form id="edit-profile" method="POST" action="{{ route('profile.update') }}" class="card-light p-5 space-y-3 transition-shadow scroll-mt-20">
                @csrf
                <h3 class="text-sm font-extrabold text-ink-950">معلوماتك</h3>

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1 block">المنطقة</label>
                    <select name="zone_id"
                            class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                        @foreach(\App\Models\Zone::orderBy('sort')->get() as $z)
                            <option value="{{ $z->id }}" {{ $user->zone_id == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1 block">إنت إيه؟</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['student'=>'طالب','worker'=>'موظف','merchant'=>'تاجر','homemaker'=>'في البيت','resident'=>'ساكن'] as $key => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="persona" value="{{ $key }}" class="peer sr-only" {{ ($user->persona ?? 'resident') === $key ? 'checked' : '' }}>
                                <div class="bg-cream-100 border border-ink-950/8 rounded-2xl px-3 py-2 text-center text-xs font-bold peer-checked:bg-coral-500 peer-checked:text-white peer-checked:border-coral-500 transition">
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button class="btn-primary w-full justify-center !py-2.5 text-sm">
                    احفظ
                    <x-icon name="check" class="w-4 h-4"/>
                </button>
            </form>

            {{-- Change password --}}
            <form method="POST" action="{{ route('profile.password') }}" class="card-light p-5 space-y-3">
                @csrf
                <h3 class="text-sm font-extrabold text-ink-950">تغيير الباسورد</h3>
                <p class="text-xs text-ink-500">قريباً هتقدر تغيّره عبر WhatsApp OTP بدل ما تكتب الباسورد القديم.</p>

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1 block">الباسورد الحالي</label>
                    <input type="password" name="current_password" required minlength="6"
                           class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                    @error('current_password') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-ink-500 mb-1 block">الجديد</label>
                        <input type="password" name="password" required minlength="6"
                               class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-ink-500 mb-1 block">تأكيد</label>
                        <input type="password" name="password_confirmation" required minlength="6"
                               class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                    </div>
                </div>
                @error('password') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

                <button class="btn-primary w-full justify-center !py-2.5 text-sm">
                    غيّر الباسورد
                    <x-icon name="check" class="w-4 h-4"/>
                </button>
            </form>

            {{-- Push notifications --}}
            <div class="card-light p-5">
                <h3 class="text-sm font-extrabold text-ink-950 mb-1 inline-flex items-center gap-2">
                    <x-icon name="bell" class="w-4 h-4 text-coral-500"/>
                    تنبيهات الموبايل
                </h3>
                <p class="text-xs text-ink-500 mb-4 leading-relaxed">
                    شغّل الإشعارات وهتوصلك تنبيهات لحظية لما يحصل حاجة في حيك (زحمة، كهربا، ميمز ترند…).
                </p>
                <button type="button"
                        data-push-toggle data-push-on="0"
                        class="btn-primary w-full justify-center !py-2.5 text-sm">
                    تشغيل التنبيهات
                </button>
            </div>

            @if($user->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="card-light p-4 flex items-center gap-3 hover:-translate-y-0.5 hover:shadow-lg transition border-2 border-coral-500/40 bg-coral-50">
                    <span class="w-10 h-10 rounded-2xl brand-bg grid place-items-center text-white shrink-0">★</span>
                    <div class="flex-1">
                        <div class="text-sm font-extrabold text-ink-950">لوحة الأدمن</div>
                        <div class="text-[11px] text-ink-500">إدارة المستخدمين، البلاغات، البوستات…</div>
                    </div>
                    <x-icon name="arrow-left" class="w-4 h-4 text-coral-600"/>
                </a>
            @endif

            {{-- My listings --}}
            <a href="{{ route('directory.mine') }}" class="card-light p-4 flex items-center gap-3 hover:-translate-y-0.5 hover:shadow-lg transition">
                <span class="w-10 h-10 rounded-2xl pill-coral grid place-items-center shrink-0">
                    <x-icon name="bag" class="w-4 h-4"/>
                </span>
                <div class="flex-1">
                    <div class="text-sm font-extrabold text-ink-950">نشاطاتي في الدليل</div>
                    <div class="text-[11px] text-ink-500">صنايعية · مطاعم · دكاترة · محلات</div>
                </div>
                <x-icon name="arrow-left" class="w-4 h-4 text-ink-400"/>
            </a>

            {{-- PWA install hint --}}
            <div class="card-light p-5">
                <h3 class="text-sm font-extrabold text-ink-950 mb-1 inline-flex items-center gap-2">
                    <x-icon name="arrow-left" class="w-4 h-4 text-coral-500 rotate-90"/>
                    حمّل بنهاوي على موبايلك
                </h3>
                <p class="text-xs text-ink-500 mb-4">افتحه من الـ home screen زي أي تطبيق عادي — أسرع وأنضف.</p>
                <button type="button" onclick="window.banhawyInstall?.maybeShow()"
                        class="btn-ghost w-full justify-center !py-2.5 text-sm">
                    اعرف إزاي
                </button>
            </div>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}"
                  data-confirm="خروج من بنهاوي؟"
                  data-confirm-action="اخرج"
                  data-confirm-tone="danger">
                @csrf
                <button type="submit" class="card-light p-4 w-full flex items-center justify-between text-blush-500 font-bold hover:bg-blush-100/50 transition">
                    <span class="inline-flex items-center gap-2">
                        <x-icon name="logout" class="w-4 h-4"/>
                        خروج
                    </span>
                    <x-icon name="chevron-down" class="w-4 h-4 -rotate-90 text-ink-400"/>
                </button>
            </form>
        </div>

    @else
        {{-- Posts (default) --}}
        @forelse($posts as $post)
            @php $userVotes = []; @endphp
            @include('partials.post-card', ['post' => $post, 'userVotes' => $userVotes])
        @empty
            <div class="card-light p-10 text-center text-ink-500">
                لسه مفيش بوستات.
                @if($isMe)
                    <div class="mt-4">
                        <a href="{{ route('posts.create') }}" class="btn-primary">
                            ابدأ أول بوست
                            <x-icon name="arrow-left" class="w-4 h-4"/>
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    @endif
</div>
@endsection
