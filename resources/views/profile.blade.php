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
                <span class="w-10 h-10 rounded-xl bg-coral-50 text-coral-600 grid place-items-center mb-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>
                    </svg>
                </span>
                <div class="text-sm font-extrabold text-ink-950">أوردراتي</div>
                <div class="text-[10px] text-ink-500 mt-0.5">
                    @if($activeOrders > 0)
                        {{ $activeOrders }} أوردر شغّال
                    @else
                        تابع حالة طلباتك
                    @endif
                </div>
            </a>
            <a href="{{ route('bookmark.index') }}"
               class="bg-white rounded-2xl p-3 hover:bg-cream-100 transition">
                <span class="w-10 h-10 rounded-xl bg-honey-100 text-honey-700 grid place-items-center mb-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                    </svg>
                </span>
                <div class="text-sm font-extrabold text-ink-950">المحفوظات</div>
                <div class="text-[10px] text-ink-500 mt-0.5">الأماكن اللي حفظتها</div>
            </a>
        </div>
    @endif

    {{-- ─── Badges row (circular thumbnails) ────────────────── --}}
    @if($circles->isNotEmpty())
        <section class="mb-7">
            <h2 class="text-base font-black text-ink-950 mb-3">شارات</h2>
            <div class="-mx-4 px-4 overflow-x-auto scrollbar-hide">
                <div class="flex items-start gap-3 min-w-max">
                    @foreach($circles as $b)
                        <div class="flex flex-col items-center gap-1.5 w-16 shrink-0">
                            <span class="w-14 h-14 rounded-full grid place-items-center text-2xl ring-2 ring-coral-500/15"
                                  style="background: {{ ($b->color ?? '#EEF2FF') }}22;">
                                {{ $b->icon ?? '🏅' }}
                            </span>
                            <span class="text-[10px] font-bold text-ink-700 text-center leading-tight w-full truncate">
                                {{ $b->name }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ─── إعداداتي ─────────────────────────────────────────── --}}
    @if($isMe)
        <section class="mb-7">
            <h2 class="text-base font-black text-ink-950 mb-3">إعداداتي</h2>

            <div class="bg-white rounded-2xl divide-y divide-ink-950/6 overflow-hidden">
                {{-- Avatar --}}
                <label class="flex items-center gap-3 px-4 py-3.5 cursor-pointer hover:bg-cream-100 transition">
                    <span class="w-9 h-9 rounded-full bg-coral-50 text-coral-600 grid place-items-center shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                    </span>
                    <span class="flex-1 text-sm font-extrabold text-ink-950">صورة البروفايل</span>
                    <x-icon name="arrow-left" class="w-4 h-4 text-ink-400"/>
                    <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" class="hidden" id="avatar-form">
                        @csrf
                        <input type="file" name="avatar" accept="image/*" onchange="this.form.submit()">
                    </form>
                </label>
                <script>document.currentScript.previousElementSibling?.addEventListener('click', () => document.querySelector('#avatar-form input').click());</script>

                {{-- Password --}}
                <button type="button" data-settings-open="password"
                        class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-cream-100 transition text-start">
                    <span class="w-9 h-9 rounded-full bg-coral-50 text-coral-600 grid place-items-center shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <span class="flex-1 text-sm font-extrabold text-ink-950">كلمة المرور</span>
                    <x-icon name="arrow-left" class="w-4 h-4 text-ink-400"/>
                </button>

                {{-- Prayer notifications --}}
                {{-- <form action="{{ route('profile.prayer.notify') }}" method="POST" class="block">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-cream-100 transition text-start">
                        <span class="w-9 h-9 rounded-full bg-coral-50 text-coral-600 grid place-items-center shrink-0">
                            <x-icon name="bell" class="w-4 h-4"/>
                        </span>
                        <span class="flex-1 text-sm font-extrabold text-ink-950">إشعارات الصلاة</span>
                        <span class="text-xs font-extrabold {{ $user->prayer_notify ? 'text-mint-700' : 'text-ink-400' }}">
                            {{ $user->prayer_notify ? 'مفعّلة' : 'مقفولة' }}
                        </span>
                    </button>
                </form> --}}

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-cream-100 transition text-start">
                        <span class="w-9 h-9 rounded-full bg-coral-50 text-coral-600 grid place-items-center shrink-0">
                            <x-icon name="logout" class="w-4 h-4"/>
                        </span>
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
