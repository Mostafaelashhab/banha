<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" media="(prefers-color-scheme: light)" content="#FFF7F1">
    <meta name="theme-color" media="(prefers-color-scheme: dark)"  content="#0B0B0C">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'بنهاوي · مدينتك على راحة إيدك · بنها · القليوبية' }}</title>

    {{-- ─── SEO ─────────────────────────────────────────────── --}}
    @php
        $seoDesc = $description ?? 'بنهاوي — التطبيق المحلي لأهل بنها والقليوبية. أحدث الأخبار، الأسعار، التنبيهات، دليل المطاعم والصنايعية، بيع وشراء، ومنيو رقمي.';
        $seoUrl  = $canonical ?? url()->current();
        $seoImg  = $ogImage ?? asset('icons/icon-512.png');
        $seoKw   = $keywords ?? 'بنها, القليوبية, مطاعم بنها, دليل بنها, أسعار بنها, تنبيهات بنها, منيو بنها, بيع وشراء بنها, banha, qalyubia';
    @endphp
    <meta name="description" content="{{ $seoDesc }}">
    <meta name="keywords"    content="{{ $seoKw }}">
    <meta name="robots"      content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <link rel="canonical"    href="{{ $seoUrl }}">
    <link rel="alternate"    hreflang="ar-EG" href="{{ $seoUrl }}">

    {{-- Open Graph --}}
    <meta property="og:type"        content="{{ $ogType ?? 'website' }}">
    <meta property="og:site_name"   content="بنهاوي">
    <meta property="og:title"       content="{{ $title ?? 'بنهاوي · مدينتك على راحة إيدك' }}">
    <meta property="og:description" content="{{ $seoDesc }}">
    <meta property="og:url"         content="{{ $seoUrl }}">
    <meta property="og:locale"      content="ar_EG">
    @if($seoImg)
        <meta property="og:image"   content="{{ $seoImg }}">
        <meta property="og:image:width"  content="1200">
        <meta property="og:image:height" content="630">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $title ?? 'بنهاوي' }}">
    <meta name="twitter:description" content="{{ $seoDesc }}">
    @if($seoImg)
        <meta name="twitter:image"   content="{{ $seoImg }}">
    @endif

    {{-- Geo (Banha specific) --}}
    <meta name="geo.region"    content="EG-QH">
    <meta name="geo.placename" content="Banha">
    <meta name="geo.position"  content="30.4582;31.1797">
    <meta name="ICBM"          content="30.4582, 31.1797">

    {{-- Site-wide JSON-LD --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => 'بنهاوي',
        'url'      => url('/'),
        'inLanguage' => 'ar',
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => url('/search') . '?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => 'بنهاوي',
        'url'      => url('/'),
        'logo'     => asset('icons/icon-512.png'),
        'sameAs'   => [],
        'address'  => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'بنها',
            'addressRegion'   => 'القليوبية',
            'addressCountry'  => 'EG',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

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
<body class="min-h-screen" data-install-prompt="auto" data-guest="{{ auth()->check() ? '0' : '1' }}" data-login-url="{{ route('login') }}" style="padding-bottom: calc(7rem + env(safe-area-inset-bottom));">

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
                    @php $route = request()->route()?->getName() ?? ''; @endphp
                    @if(! in_array($route, ['login', 'signup', 'forgot', 'forgot.verify'], true))
                        <a href="{{ route('login') }}" class="text-xs font-bold px-3 py-1.5 rounded-full bg-coral-500 text-white hover:bg-coral-600 transition">
                            دخول
                        </a>
                    @endif
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

    {{-- ─── BOTTOM NAV ─────────────────────────────────────── --}}
    @php
        $route    = request()->route()->getName() ?? '';
        $isHome   = $route === 'feed' || $route === 'home';
        $isDir    = str_starts_with($route, 'directory') && $route !== 'directory.map';
        $isMap    = $route === 'directory.map';
        $isCreate = $route === 'directory.create' || $route === 'posts.create';
        $isMarket = str_starts_with($route, 'marketplace');
        $isMe     = str_starts_with($route, 'profile');
        $isAuthPage = in_array($route, ['login', 'login.attempt', 'signup', 'signup.attempt', 'forgot', 'forgot.send', 'forgot.verify', 'forgot.reset', 'verify.show', 'verify.send', 'verify.attempt'], true);
    @endphp
    @unless($isAuthPage)
    <nav class="bottom-nav">
        <div class="bottom-nav-inner">
            <a href="{{ route('feed') }}" aria-label="الرئيسية" class="nav-item {{ $isHome ? 'is-active' : '' }}">
                <x-icon name="home" class="w-5 h-5"/>
                <span class="nav-label">الرئيسية</span>
            </a>
            <a href="{{ route('directory.index') }}" aria-label="الدليل" class="nav-item {{ $isDir ? 'is-active' : '' }}">
                <x-icon name="bag" class="w-5 h-5"/>
                <span class="nav-label">الدليل</span>
            </a>
            @auth
                <a href="{{ route('directory.create') }}" aria-label="أضف نشاطك" class="nav-fab {{ $isCreate ? 'is-active' : '' }}">
                    <x-icon name="plus" class="w-6 h-6"/>
                </a>
            @else
                <a href="{{ route('directory.map') }}" aria-label="الخريطة" class="nav-fab {{ $isMap ? 'is-active' : '' }}">
                    <x-icon name="map-pin" class="w-6 h-6"/>
                </a>
            @endauth
            <a href="{{ route('feed') }}#prayer" aria-label="مواعيد الصلاة" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <path d="M2 22h20"/><path d="M5 22V8h14v14"/><path d="M8 22v-8h8v8"/><circle cx="12" cy="6" r="2"/>
                </svg>
                <span class="nav-label">الصلاة</span>
            </a>
            @auth
                <a href="{{ route('profile.me') }}" aria-label="حسابي" class="nav-item {{ $isMe ? 'is-active' : '' }}">
                    <x-icon name="user" class="w-5 h-5"/>
                    <span class="nav-label">حسابي</span>
                </a>
            @else
                <a href="{{ route('login') }}" aria-label="دخول" class="nav-item">
                    <x-icon name="user" class="w-5 h-5"/>
                    <span class="nav-label">دخول</span>
                </a>
            @endauth
        </div>
    </nav>

    {{-- Floating support button (chat with admin / fallback for guests) --}}
    @if($route !== 'support' && ! str_starts_with($route, 'chat'))
        <a href="{{ route('support') }}" aria-label="الدعم"
           class="fixed bottom-[6.5rem] start-3 z-30 w-11 h-11 rounded-full bg-white border border-ink-950/8 shadow-lg grid place-items-center text-ink-500 hover:text-coral-600 hover:scale-105 transition"
           style="bottom: calc(7rem + env(safe-area-inset-bottom));">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z"/>
            </svg>
        </a>
    @endif
    @endunless

    @stack('scripts')
</body>
</html>
