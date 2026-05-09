<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#FFF7F1">
    <title>أوفلاين · بنهاوي</title>
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen grid place-items-center p-6" style="background: linear-gradient(135deg, #FFF7F1 0%, #FFE8DD 100%);">
    <div class="max-w-sm w-full text-center">
        <div class="w-24 h-24 rounded-3xl mx-auto mb-6 brand-bg grid place-items-center shadow-2xl">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12">
                <line x1="1" y1="1" x2="23" y2="23"/>
                <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"/>
                <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"/>
                <path d="M10.71 5.05A16 16 0 0 1 22.58 9"/>
                <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"/>
                <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                <line x1="12" y1="20" x2="12.01" y2="20"/>
            </svg>
        </div>

        <h1 class="text-3xl font-black text-ink-950 mb-2">مفيش نت دلوقتي</h1>
        <p class="text-ink-500 text-sm leading-relaxed mb-8">
            افتح الـ Wi-Fi أو الموبايل داتا وحاول تاني.
            <br>أو رجع لما النت يجيلك.
        </p>

        <button onclick="window.location.reload()" class="btn-primary w-full justify-center !py-3.5 mb-3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                <polyline points="23 4 23 10 17 10"/>
                <polyline points="1 20 1 14 7 14"/>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
            </svg>
            جرّب تاني
        </button>

        <a href="/" class="block text-xs text-ink-500 hover:text-coral-600 transition">رجوع للرئيسية</a>

        <div class="mt-10 flex items-center justify-center gap-2 text-ink-400">
            <span class="w-7 h-7 rounded-lg brand-bg grid place-items-center text-white font-black text-xs">ب</span>
            <span class="text-xs font-bold">بنهاوي</span>
        </div>
    </div>

    <script>
        // Auto-reload when network comes back
        window.addEventListener('online', () => window.location.reload());
    </script>
</body>
</html>
