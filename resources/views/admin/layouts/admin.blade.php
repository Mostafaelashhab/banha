<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0B0B0C">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin · بنهاوي' }}</title>
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: var(--color-ink-950) !important; color: #fff; }
        .a-side {
            background: var(--color-ink-900);
            border-color: rgba(255, 255, 255, 0.06);
        }
        .a-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: 14px;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 700; font-size: 14px;
            transition: all .15s ease;
        }
        .a-link:hover { color: #fff; background: rgba(255, 255, 255, 0.05); }
        .a-link.is-active { color: #fff; background: linear-gradient(135deg, var(--color-coral-500), var(--color-honey-400)); box-shadow: 0 8px 18px -8px rgba(255, 122, 77, .55); }
        .a-card {
            background: var(--color-ink-900);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 18px;
        }
        .a-table th, .a-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 13px;
        }
        .a-table th { font-size: 11px; text-transform: uppercase; color: rgba(255, 255, 255, 0.5); font-weight: 700; text-align: start; }
        .a-table tr:hover td { background: rgba(255, 255, 255, 0.02); }
        .a-pill { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 800; }
    </style>
    @stack('head')
</head>
<body class="min-h-screen">

    @php $route = request()->route()->getName() ?? ''; @endphp

    <div class="flex min-h-screen">

        {{-- Sidebar (desktop) --}}
        <aside class="a-side hidden lg:flex flex-col w-64 border-l border-r-0 p-5 sticky top-0 h-screen shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-extrabold text-lg mb-8">
                <span class="w-9 h-9 rounded-xl brand-bg grid place-items-center"><span class="text-white font-black">ب</span></span>
                <span>Admin</span>
            </a>

            <nav class="flex-1 space-y-1">
                <a href="{{ route('admin.dashboard') }}"  class="a-link {{ $route === 'admin.dashboard' ? 'is-active' : '' }}">
                    <x-icon name="home" class="w-4 h-4"/> اللوحة
                </a>
                <a href="{{ route('admin.users') }}"      class="a-link {{ $route === 'admin.users' ? 'is-active' : '' }}">
                    <x-icon name="user" class="w-4 h-4"/> المستخدمين
                </a>
                <a href="{{ route('admin.posts') }}"      class="a-link {{ $route === 'admin.posts' ? 'is-active' : '' }}">
                    <x-icon name="flame" class="w-4 h-4"/> البوستات
                </a>
                <a href="{{ route('admin.reports') }}"    class="a-link {{ $route === 'admin.reports' ? 'is-active' : '' }}">
                    <x-icon name="flag" class="w-4 h-4"/> البلاغات
                </a>
                <a href="{{ route('admin.businesses') }}" class="a-link {{ $route === 'admin.businesses' ? 'is-active' : '' }}">
                    <x-icon name="bag" class="w-4 h-4"/> النشاطات
                </a>
                <a href="{{ route('admin.broadcast') }}"  class="a-link {{ $route === 'admin.broadcast' ? 'is-active' : '' }}">
                    <x-icon name="bell" class="w-4 h-4"/> إرسال إشعار
                </a>
            </nav>

            <a href="{{ route('feed') }}" class="a-link mt-4">
                <x-icon name="arrow-right" class="w-4 h-4"/> ارجع للتطبيق
            </a>
        </aside>

        {{-- Main --}}
        <main class="flex-1 min-w-0 p-4 md:p-6 max-w-7xl mx-auto w-full">

            {{-- Mobile topbar --}}
            <div class="lg:hidden flex items-center justify-between mb-4">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-extrabold">
                    <span class="w-8 h-8 rounded-lg brand-bg grid place-items-center"><span class="text-white font-black text-sm">ب</span></span>
                    Admin
                </a>
                <a href="{{ route('feed') }}" class="text-xs text-white/60 hover:text-white">← التطبيق</a>
            </div>

            {{-- Mobile tabs --}}
            <div class="lg:hidden overflow-x-auto scrollbar-hide -mx-4 mb-4">
                <div class="flex gap-2 px-4 w-max">
                    @foreach([
                        'admin.dashboard'  => 'اللوحة',
                        'admin.users'      => 'مستخدمين',
                        'admin.posts'      => 'بوستات',
                        'admin.reports'    => 'بلاغات',
                        'admin.businesses' => 'نشاطات',
                        'admin.broadcast'  => 'إشعار',
                    ] as $r => $label)
                        <a href="{{ route($r) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition
                                  {{ $route === $r ? 'bg-coral-500 text-white' : 'bg-white/5 text-white/60 hover:text-white border border-white/10' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            @if(session('flash'))
                <div class="mb-4 a-card p-3 border-coral-500/40 border-2 text-sm font-bold flex items-center justify-between" data-flash>
                    <span>{{ session('flash') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
