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

    {{-- ─── HERO ─── --}}
    <div class="card-orange p-5 md:p-6 mb-4 relative overflow-hidden">
        <div class="absolute -top-16 -end-16 w-56 h-56 rounded-full bg-white/15 blur-3xl"></div>
        <div class="absolute -bottom-16 -start-16 w-56 h-56 rounded-full bg-honey-400/40 blur-3xl"></div>

        <div class="relative flex items-start gap-4">
            <div class="relative shrink-0">
                <x-avatar :user="$user" size="xl" :ring="true" class="shadow-xl"/>
                @if($isMe)
                    <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" data-no-progress="1">
                        @csrf
                        <label class="avatar-cam-badge" title="غيّر الصورة">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="this.form.requestSubmit()">
                        </label>
                    </form>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl md:text-2xl font-black text-white truncate">{{ $user->username }}</h1>
                    @if($isMe)
                        <a href="{{ route('profile.me', ['tab' => 'settings']) }}#edit-profile"
                           data-edit-profile
                           class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-full bg-white/20 text-white border border-white/30 hover:bg-white/30 transition">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z"/>
                            </svg>
                            عدّل
                        </a>
                        @push('scripts')
                        <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            // Smooth scroll + highlight the form on click (works even if already on settings tab)
                            document.querySelectorAll('[data-edit-profile]').forEach((a) => {
                                a.addEventListener('click', (e) => {
                                    const target = document.getElementById('edit-profile');
                                    if (target && new URL(a.href).pathname === window.location.pathname && new URL(a.href).search === window.location.search) {
                                        e.preventDefault();
                                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                        target.classList.add('ring-4','ring-coral-500/40');
                                        setTimeout(() => target.classList.remove('ring-4','ring-coral-500/40'), 1500);
                                        target.querySelector('input[name="username"]')?.focus();
                                    }
                                });
                            });
                            // Auto-focus + highlight if landed via the #edit-profile hash
                            if (location.hash === '#edit-profile') {
                                const t = document.getElementById('edit-profile');
                                if (t) {
                                    t.classList.add('ring-4','ring-coral-500/40');
                                    setTimeout(() => t.classList.remove('ring-4','ring-coral-500/40'), 1500);
                                    t.querySelector('input[name="username"]')?.focus();
                                }
                            }
                        });
                        </script>
                        @endpush
                    @elseif(auth()->check())
                        @php $isFollowing = auth()->user()->isFollowing($user->id); @endphp
                        <form method="POST" action="{{ route('users.follow', $user) }}" class="inline">
                            @csrf
                            <button class="inline-flex items-center gap-1 text-[11px] font-bold px-3 py-1 rounded-full {{ $isFollowing ? 'bg-white/20 text-white border border-white/30' : 'bg-white text-coral-600' }} hover:scale-105 transition">
                                {{ $isFollowing ? '✓ متابع' : '+ تابع' }}
                            </button>
                        </form>
                    @endif
                    @if($user->verification_tier === 'gold')
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full text-white" style="background: #1D9BF0">
                            <x-icon name="check" class="w-3 h-3"/> موثّق
                        </span>
                    @elseif($user->verification_tier === 'silver')
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/25 text-white border border-white/30">
                            🥈 فضي
                        </span>
                    @elseif($user->verification_tier === 'bronze')
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white/90 border border-white/20">
                            مفعّل
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-blush-100 text-blush-500">
                            غير مفعّل
                        </span>
                    @endif
                </div>
                <div class="text-white/85 text-xs md:text-sm mt-1 flex items-center gap-1.5 flex-wrap">
                    @if($user->isOnline())
                        <span class="inline-flex items-center gap-1 text-mint-300 font-bold">
                            <span class="w-2 h-2 rounded-full bg-mint-400 animate-pulse"></span>
                            أونلاين الآن
                        </span>
                    @elseif($user->last_seen_at)
                        <span class="inline-flex items-center gap-1 text-white/70">
                            <span class="w-2 h-2 rounded-full bg-white/40"></span>
                            آخر ظهور {{ $user->last_seen_at->diffForHumans() }}
                        </span>
                    @endif
                    @if($user->zone)
                        <span class="text-white/40">·</span>
                        <x-icon name="map-pin" class="w-3 h-3 inline -mt-0.5"/>
                        {{ $user->zone->name }}
                    @endif
                    <span class="text-white/40">·</span>
                    <span>انضم من {{ $user->created_at->diffForHumans(['parts' => 1, 'short' => true]) }}</span>
                </div>

                {{-- Top earned badges row (max 5) --}}
                @if($earnedBadges->isNotEmpty())
                    <div class="flex items-center gap-1.5 mt-3">
                        @foreach($earnedBadges->take(5) as $b)
                            <span title="{{ $b->name }}"
                                  class="w-9 h-9 rounded-xl bg-white/15 border border-white/25 grid place-items-center text-base shadow-sm">
                                {{ $b->emoji }}
                            </span>
                        @endforeach
                        @if($earnedBadges->count() > 5)
                            <span class="text-white/85 text-xs font-bold ms-1">+{{ $earnedBadges->count() - 5 }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── STATS ─── --}}
    <div class="grid grid-cols-4 gap-2 mb-4">
        @php
            $statTiles = [
                ['ريبيوتيشن', $stats['reputation'], 'flame'],
                ['بوست',      $stats['posts'],      'home'],
                ['كومنت',     $stats['comments'],   'bell'],
                ['يوم نشاط',  max($stats['days'], 1), 'check'],
            ];
        @endphp
        @foreach($statTiles as [$lbl, $val, $ic])
            <div class="card-light px-3 py-3 text-center">
                <div class="text-coral-500 mx-auto mb-1 w-6 h-6 grid place-items-center">
                    <x-icon :name="$ic" class="w-4 h-4"/>
                </div>
                <div class="text-lg md:text-xl font-black text-ink-950 leading-none">{{ $val }}</div>
                <div class="text-[10px] text-ink-500 mt-1">{{ $lbl }}</div>
            </div>
        @endforeach
    </div>

    {{-- ─── TABS ─── --}}
    <div class="flex items-center gap-2 mb-4 -mx-4 px-4 overflow-x-auto scrollbar-hide">
        <a href="{{ route('profile.me', ['tab' => 'posts']) }}"
           class="chip {{ $tab === 'posts' ? 'chip-active' : '' }}">
            <x-icon name="home" class="w-3.5 h-3.5"/> بوستات
            <span class="opacity-60 text-xs">{{ $stats['posts'] }}</span>
        </a>
        <a href="{{ route('profile.me', ['tab' => 'listings']) }}"
           class="chip {{ $tab === 'listings' ? 'chip-active' : '' }}">
            <x-icon name="tag" class="w-3.5 h-3.5"/> إعلانات
            <span class="opacity-60 text-xs">{{ $stats['listings'] ?? 0 }}</span>
        </a>
        <a href="{{ route('profile.me', ['tab' => 'badges']) }}"
           class="chip {{ $tab === 'badges' ? 'chip-active' : '' }}">
            🏅 شارات
            <span class="opacity-60 text-xs">{{ $earnedBadges->count() }}</span>
        </a>
        @if($isMe)
            <a href="{{ route('profile.me', ['tab' => 'verification']) }}"
               class="chip {{ $tab === 'verification' ? 'chip-active' : '' }}">
                <x-icon name="check" class="w-3.5 h-3.5"/> توثيق
            </a>
            <a href="{{ route('profile.me', ['tab' => 'settings']) }}"
               class="chip {{ $tab === 'settings' ? 'chip-active' : '' }}">
                <x-icon name="more" class="w-3.5 h-3.5"/> إعداد
            </a>
        @endif
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
