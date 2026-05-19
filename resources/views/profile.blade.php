@extends('layouts.app', ['title' => '@'.$user->username.' · بنهاوي'])

@php
    use App\Support\AnonSeed;

    $avatarColor = AnonSeed::avatarColor($user->username);
    $initial     = AnonSeed::initial($user->username);

    $circles   = $earnedBadges ?? collect();
    $repPoints = $stats['reputation'] ?? 0;
@endphp

@section('content')
<div class="max-w-3xl mx-auto pb-8">

    {{-- ─── Top row: avatar + wallet pill + 3 quick actions ───── --}}
    <div class="flex items-center gap-3 mb-5">
      

        @if($isMe)
            <x-button :href="route('wallet')" pill icon="bag" block>محفظتي</x-button>
        @endif

        <div class="flex items-center gap-1.5 shrink-0">
            @if($isMe)
                <x-icon-tile icon="comment" shape="circle" :href="route('chat.inbox')" aria-label="الرسائل"/>
                <x-icon-tile icon="bell" shape="circle" :href="route('notifications.index')" aria-label="الإشعارات"/>
            @else
                <button type="button" aria-label="بلّغ"
                        class="w-10 h-10 rounded-full bg-coral-100 text-coral-600 grid place-items-center hover:bg-coral-200 transition">
                    <x-icon name="flag" class="w-4 h-4"/>
                </button>
            @endif
        </div>
    </div>

    {{-- ─── Greeting ────────────────────────────────────────── --}}
    <h1 class="text-3xl font-black text-ink-950 mb-5 leading-none">
        {{ $isMe ? 'أهلاً، '.$user->username.'!' : '@'.$user->username }}
    </h1>

    {{-- ─── Voucher-style status card (wallet hint) ─────────── --}}
    @if($isMe)
        <a href="{{ route('wallet') }}"
           class="block bg-white rounded-2xl p-4 mb-7 hover:bg-cream-100 transition">
            <div class="flex items-center gap-4">
                <x-icon-tile icon="bag" size="xl" shape="circle"/>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-black text-ink-950 mb-0.5">
                        عندك {{ number_format($repPoints) }} نقطة
                    </div>
                    <p class="text-xs text-ink-500 leading-relaxed">
                        كل نقطتين بـ ١ جنيه · اسحب فلوسك من المحفظة فودافون كاش أو إنستاباي.
                    </p>
                </div>
            </div>
        </a>
    @else
        @php $joined = $user->created_at?->translatedFormat('F Y'); @endphp
        <div class="bg-white rounded-2xl p-4 mb-7">
            <div class="flex items-center gap-4">
                <span class="w-14 h-14 rounded-full bg-coral-50 grid place-items-center text-coral-600 shrink-0">
                    <x-icon name="user" class="w-6 h-6"/>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-black text-ink-950 mb-0.5">
                        {{ number_format($repPoints) }} نقطة · {{ $stats['days'] }} يوم في بنهاوي
                    </div>
                    <p class="text-xs text-ink-500">انضم في {{ $joined ?: '—' }}.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ─── My Orders + Saved (two-column quick access — visible only on own profile) ───── --}}
    @if($isMe)
        @php
            $activeOrders = \App\Models\Order::where('user_id', auth()->id())
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->count();
        @endphp
        <div class="grid grid-cols-2 gap-2 mb-7">
            <a href="{{ route('my-orders.index') }}"
               class="bg-white rounded-2xl p-3 hover:bg-cream-100 transition relative">
                @if($activeOrders > 0)
                    <span class="absolute top-2 end-2 min-w-[18px] h-[18px] px-1 rounded-full bg-coral-500 text-white text-[10px] font-extrabold grid place-items-center">{{ $activeOrders }}</span>
                @endif
                <x-icon-tile icon="cart" class="mb-2"/>
                <div class="text-sm font-extrabold text-ink-950">أوردراتي</div>
                <div class="text-[10px] text-ink-500 mt-0.5">
                    @if($activeOrders > 0)
                        {{ $activeOrders }} أوردر شغّال
                    @else
                        تابع حالة طلباتك
                    @endif
                </div>
            </a>
            
        </div>
    @endif


    {{-- ─── إعداداتي ─────────────────────────────────────────── --}}
    @if($isMe)
        <section class="mb-7">
            <h2 class="text-base font-black text-ink-950 mb-3">إعداداتي</h2>

            <div class="bg-white rounded-2xl divide-y divide-ink-950/6 overflow-hidden">
                {{-- Avatar --}}
               
                <script>document.currentScript.previousElementSibling?.addEventListener('click', () => document.querySelector('#avatar-form input').click());</script>

                {{-- Password --}}
                <button type="button" data-settings-open="password"
                        class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-cream-100 transition text-start">
                    <x-icon-tile icon="lock" shape="circle"/>
                    <span class="flex-1 text-sm font-extrabold text-ink-950">كلمة المرور</span>
                    <x-icon name="arrow-left" class="w-4 h-4 text-ink-400"/>
                </button>

           

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-cream-100 transition text-start">
                        <x-icon-tile icon="logout" shape="circle"/>
                        <span class="flex-1 text-sm font-extrabold text-coral-600">تسجيل الخروج</span>
                        <x-icon name="arrow-left" class="w-4 h-4 text-coral-400"/>
                    </button>
                </form>
            </div>
        </section>

        {{-- Password modal (lightweight) --}}
        <div id="pw-modal" class="modal-wrap" role="dialog" aria-modal="true">
            <div class="modal-backdrop" data-close></div>
            <div class="modal-sheet">
                <div class="px-5 pt-3 pb-6">
                    <div class="modal-drag-handle"><span class="modal-drag-bar"></span></div>
                    <h3 class="text-lg font-black text-ink-950 mb-4">تغيير كلمة المرور</h3>
                    <form action="{{ route('profile.password') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="password" name="current_password" required placeholder="كلمة المرور الحالية"
                               class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-coral-500/30">
                        <input type="password" name="password" required placeholder="كلمة المرور الجديدة" minlength="6"
                               class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-coral-500/30">
                        <input type="password" name="password_confirmation" required placeholder="أكد كلمة المرور" minlength="6"
                               class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-coral-500/30">
                        <button type="submit" class="w-full btn-primary !py-3 text-sm">حفظ</button>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            document.querySelectorAll('[data-settings-open="password"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const m = document.getElementById('pw-modal');
                    m.classList.add('open');
                    document.body.style.overflow = 'hidden';
                });
            });
            document.querySelectorAll('#pw-modal [data-close]').forEach(el => {
                el.addEventListener('click', () => {
                    document.getElementById('pw-modal').classList.remove('open');
                    document.body.style.overflow = '';
                });
            });
        </script>
        @endpush
    @endif

</div>
@endsection
