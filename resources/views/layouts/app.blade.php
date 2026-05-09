<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" media="(prefers-color-scheme: light)" content="#FFF7F1">
    <meta name="theme-color" media="(prefers-color-scheme: dark)"  content="#0B0B0C">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'بنهاوي' }}</title>

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="بنهاوي">
    <meta name="mobile-web-app-capable" content="yes">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen" style="padding-bottom: calc(7rem + env(safe-area-inset-bottom));">

    {{-- ─── TOP BAR ─────────────────────────────────────── --}}
    <header class="sticky top-0 z-40 bg-cream-100/85 backdrop-blur border-b border-ink-950/5">
        <div class="mx-auto max-w-3xl px-4 h-14 flex items-center justify-between gap-3">
            <a href="{{ route('feed') }}" class="flex items-center gap-2 font-extrabold text-ink-950">
                <span class="w-8 h-8 rounded-lg brand-bg grid place-items-center text-white font-black text-sm">ب</span>
                <span>بنهاوي</span>
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('search') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-500 hover:text-ink-950 transition" aria-label="بحث">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </a>
                @auth
                    @php $unread = \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->count(); @endphp
                    <a href="{{ route('notifications.index') }}" class="relative w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-500 hover:text-ink-950 transition" aria-label="إشعارات">
                        <x-icon name="bell" class="w-4 h-4"/>
                        @if($unread > 0)
                            <span class="absolute -top-0.5 -end-0.5 min-w-[18px] h-[18px] rounded-full bg-coral-500 text-white text-[10px] font-extrabold grid place-items-center px-1">
                                {{ $unread > 9 ? '9+' : $unread }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('profile.me') }}" class="block">
                        <x-avatar :user="auth()->user()" size="md"/>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-bold px-3 py-1.5 rounded-full bg-coral-500 text-white hover:bg-coral-600 transition">
                        دخول
                    </a>
                @endauth
            </div>
        </div>
    </header>

    @if(session('flash'))
        <div class="fixed top-16 inset-x-0 z-50 mx-auto max-w-md px-4">
            <div class="card-light p-3 border-coral-500/40 border-2 text-ink-950 text-sm font-bold flex items-center justify-between" data-flash>
                <span>{{ session('flash') }}</span>
                <button type="button" onclick="this.closest('[data-flash]').remove()" class="text-ink-400 hover:text-ink-950" aria-label="إغلاق">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <main class="mx-auto max-w-3xl px-4 py-4">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- ─── BOTTOM NAV ───────────────────────────────────── --}}
    @auth
        @php
            $route    = request()->route()->getName() ?? '';
            $isHome   = $route === 'feed';
            $isDisc   = $route === 'discover';
            $isZones  = $route === 'zones';
            $isCreate = $route === 'posts.create';
            $isMe     = str_starts_with($route, 'profile');
        @endphp
        <nav class="bottom-nav">
            <div class="bottom-nav-inner">
                <a href="{{ route('feed') }}" aria-label="الرئيسية" class="nav-item {{ $isHome ? 'is-active' : '' }}">
                    <x-icon name="home" class="w-5 h-5"/>
                    <span class="nav-label">الرئيسية</span>
                </a>
                <a href="{{ route('discover') }}" aria-label="اكتشف" class="nav-item {{ $isDisc ? 'is-active' : '' }}">
                    <x-icon name="flame" class="w-5 h-5"/>
                    <span class="nav-label">اكتشف</span>
                </a>
                <a href="{{ route('posts.create') }}" aria-label="بوست جديد" class="nav-fab {{ $isCreate ? 'is-active' : '' }}">
                    <x-icon name="plus" class="w-6 h-6"/>
                </a>
                <a href="{{ route('zones') }}" aria-label="المناطق" class="nav-item {{ $isZones ? 'is-active' : '' }}">
                    <x-icon name="map-pin" class="w-5 h-5"/>
                    <span class="nav-label">المناطق</span>
                </a>
                <a href="{{ route('profile.me') }}" aria-label="حسابي" class="nav-item {{ $isMe ? 'is-active' : '' }}">
                    <x-icon name="user" class="w-5 h-5"/>
                    <span class="nav-label">حسابي</span>
                </a>
            </div>
        </nav>
    @endauth

    @stack('scripts')
</body>
</html>
