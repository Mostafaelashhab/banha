<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#FFF7F1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin · بنهاوي' }}</title>
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .a-side {
            background: #fff;
            border-color: rgb(15 15 20 / .06);
        }
        .a-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: 14px;
            color: var(--color-ink-500);
            font-weight: 700; font-size: 14px;
            transition: all .15s ease;
        }
        .a-link:hover { color: var(--color-ink-950); background: var(--color-cream-200); }
        .a-link.is-active {
            color: #fff;
            background: linear-gradient(135deg, var(--color-coral-500), var(--color-honey-400));
            box-shadow: 0 8px 18px -8px rgba(255, 122, 77, .55);
        }
        .a-card {
            background: #fff;
            border: 1px solid rgb(15 15 20 / .06);
            border-radius: 18px;
            box-shadow: var(--shadow-card);
        }
        .a-table th, .a-table td {
            padding: 10px 12px;
            border-bottom: 1px solid rgb(15 15 20 / .05);
            font-size: 13px;
            color: var(--color-ink-950);
        }
        .a-table th { font-size: 11px; text-transform: uppercase; color: var(--color-ink-500); font-weight: 700; text-align: start; }
        .a-table tr:hover td { background: var(--color-cream-100); }
        .a-pill { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 800; }

        /* Table inputs */
        .a-card select, .a-card input, .a-card textarea {
            color: var(--color-ink-950);
            background: var(--color-cream-100);
            border-color: rgb(15 15 20 / .08);
        }
    </style>
    @stack('head')
</head>
<body class="min-h-screen">

    @php $route = request()->route()->getName() ?? ''; @endphp

    <div class="flex min-h-screen">

        {{-- Sidebar (desktop) --}}
        <aside class="a-side hidden lg:flex flex-col w-64 border-l border-r-0 p-5 sticky top-0 h-screen shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-extrabold text-lg mb-8 text-ink-950">
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
                <a href="{{ route('admin.outages') }}"  class="a-link {{ $route === 'admin.outages' ? 'is-active' : '' }}">
                    <x-icon name="bolt" class="w-4 h-4"/> انقطاعات
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
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-extrabold text-ink-950">
                    <span class="w-8 h-8 rounded-lg brand-bg grid place-items-center"><span class="text-white font-black text-sm">ب</span></span>
                    Admin
                </a>
                <a href="{{ route('feed') }}" class="text-xs text-ink-500 hover:text-ink-950 font-bold">← التطبيق</a>
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
                        'admin.outages'    => 'انقطاعات',
                    ] as $r => $label)
                        <a href="{{ route($r) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition
                                  {{ $route === $r ? 'bg-coral-500 text-white border border-coral-500' : 'bg-white text-ink-500 hover:text-ink-950 border border-ink-950/8' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            @if(session('flash'))
                <div class="mb-4 card-light p-3 border-coral-500/40 border-2 text-ink-950 text-sm font-bold flex items-center justify-between" data-flash>
                    <span>{{ session('flash') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
