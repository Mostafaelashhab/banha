<!DOCTYPE html>
<html lang="ar-EG" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover, interactive-widget=resizes-content">
    {{-- Light-only theme color (matches the cream-100 page background). When
         iOS-installed the status bar is translucent and the page background
         shows through — so this also tints the URL bar on Android Chrome. --}}
    <meta name="theme-color" content="#F4F5F8">
    <meta name="color-scheme" content="light">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="format-detection" content="telephone=no">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title>{{ $title ?? 'بنهاوي · مدينتك على راحة إيدك · بنها · القليوبية' }}</title>

    {{-- ─── SEO ─────────────────────────────────────────────── --}}
    @php
        $seoDesc = $description ?? 'بنهاوي — التطبيق المحلي لأهل بنها والقليوبية. أحدث الأخبار، الأسعار، التنبيهات، دليل المطاعم والصنايعية، بيع وشراء، ومنيو رقمي.';
        $seoUrl  = $canonical ?? url()->current();
        $seoImg  = $ogImage ?? asset('icons/icon-512.png');
        $seoKw   = $keywords ?? 'بنها, القليوبية, مطاعم بنها, دليل بنها, أسعار بنها, تنبيهات بنها, منيو بنها, بيع وشراء بنها, banha, qalyubia';
        $seoTitle = $title ?? 'بنهاوي · مدينتك على راحة إيدك';
    @endphp
    <meta name="description" content="{{ $seoDesc }}">
    <meta name="keywords"    content="{{ $seoKw }}">
    <meta name="author"      content="بنهاوي">
    <meta name="application-name" content="بنهاوي">
    <meta name="robots"      content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <link rel="canonical"    href="{{ $seoUrl }}">
    <link rel="alternate"    hreflang="ar-EG" href="{{ $seoUrl }}">
    <link rel="alternate"    hreflang="x-default" href="{{ $seoUrl }}">

    {{-- DNS pre-resolution & connection warm-up for our heaviest third parties --}}
    <link rel="dns-prefetch" href="//tile.openstreetmap.org">
    <link rel="dns-prefetch" href="//unpkg.com">
    <link rel="preconnect"   href="https://fonts.gstatic.com" crossorigin>

    {{-- Open Graph --}}
    <meta property="og:type"        content="{{ $ogType ?? 'website' }}">
    <meta property="og:site_name"   content="بنهاوي">
    <meta property="og:title"       content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDesc }}">
    <meta property="og:url"         content="{{ $seoUrl }}">
    <meta property="og:locale"      content="ar_EG">
    @if($seoImg)
        <meta property="og:image"        content="{{ $seoImg }}">
        <meta property="og:image:secure_url" content="{{ $seoImg }}">
        <meta property="og:image:type"   content="image/png">
        <meta property="og:image:width"  content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt"    content="{{ $seoTitle }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDesc }}">
    @if($seoImg)
        <meta name="twitter:image"     content="{{ $seoImg }}">
        <meta name="twitter:image:alt" content="{{ $seoTitle }}">
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

    {{-- Per-page structured data (LocalBusiness, BreadcrumbList, FAQPage…) --}}
    @stack('json-ld')

    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json?v=3">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg?v=3">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png?v=3">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png?v=3">
    {{-- iOS: black-translucent gives a true full-screen feel — the status bar
         overlays the page so our hero/header reaches the top edge. We respect
         safe-area-insets in CSS so nothing hides behind the notch. --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="بنهاوي">
    <meta name="mobile-web-app-capable" content="yes">
    {{-- Windows/Edge tile (cheap, ignored elsewhere) --}}
    <meta name="msapplication-TileColor" content="#FFF7F1">
    <meta name="msapplication-tap-highlight" content="no">

    {{-- iOS launch splash — single SVG that scales to any device. Prevents the
         white flash that breaks the "native app" feel on cold-launch. --}}
    <link rel="apple-touch-startup-image" href="/icons/icon-512.png?v=3">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
@php
    // Unread-notification count, exposed to JS as `body[data-unread]` so the
    // app-badge module can mirror it to the home-screen icon via setAppBadge().
    $bodyUnread = auth()->check()
        ? \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->count()
        : 0;
@endphp
<body class="min-h-screen" data-install-prompt="auto" data-guest="{{ auth()->check() ? '0' : '1' }}" data-login-url="{{ route('login') }}" data-unread="{{ $bodyUnread }}" style="padding-bottom: calc(7rem + env(safe-area-inset-bottom));">

    {{-- Top header removed — brand/search/notifications now live within the page itself --}}

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

    {{-- ─── BOTTOM NAV (Shoppe-style) ───────────────────────── --}}
    @php
        $route    = request()->route()->getName() ?? '';
        $isHome   = $route === 'feed' || $route === 'home';
        $isMap    = $route === 'directory.map';
        $isDir    = str_starts_with($route, 'directory') && ! $isMap;
        $isMe     = str_starts_with($route, 'profile');
        $isNotif  = str_starts_with($route, 'notifications');
        $isAuthPage = in_array($route, ['login', 'login.attempt', 'signup', 'signup.attempt', 'forgot', 'forgot.send', 'forgot.verify', 'forgot.reset', 'verify.show', 'verify.send', 'verify.attempt'], true);

        // Reuse the count we already computed for the <body data-unread>.
        $navUnread = $bodyUnread ?? 0;
    @endphp
    @unless($isAuthPage)
    {{-- Persisted across Turbo navigations — same DOM node survives the body
         swap so users don't see the nav flicker on every page change. --}}
    <nav class="bottom-nav" id="bottom-nav" data-turbo-permanent>
        <div class="bottom-nav-inner">

            {{-- Home --}}
            <a href="{{ route('feed') }}" aria-label="الرئيسية" class="nav-item {{ $isHome ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </a>

            {{-- Categories (directory index) --}}
            <a href="{{ route('directory.index') }}" aria-label="الفئات" class="nav-item {{ $isDir ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
            </a>

            {{-- Map --}}
            <a href="{{ route('directory.map') }}" aria-label="الخريطة" class="nav-item {{ $isMap ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                    <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                    <line x1="9" y1="3" x2="9" y2="18"/>
                    <line x1="15" y1="6" x2="15" y2="21"/>
                </svg>
            </a>

            {{-- Notifications --}}
            @auth
                <a href="{{ route('notifications.index') }}" aria-label="إشعارات" class="nav-item nav-item--bell {{ $isNotif ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                    </svg>
                    @if($navUnread > 0)
                        <span class="nav-badge">{{ $navUnread > 9 ? '9+' : $navUnread }}</span>
                    @endif
                </a>
            @else
                <a href="{{ route('login') }}" aria-label="إشعارات" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                    </svg>
                </a>
            @endauth

            {{-- Profile --}}
            @auth
                <a href="{{ route('profile.me') }}" aria-label="حسابي" class="nav-item {{ $isMe ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </a>
            @else
                <a href="{{ route('login') }}" aria-label="دخول" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </a>
            @endauth
        </div>
    </nav>

 

    @endunless

    @stack('scripts')
</body>
</html>
