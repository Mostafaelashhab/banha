<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#FFF7F1">
    <meta name="robots" content="noindex">
    <title>{{ $title ?? 'خطأ' }} · بنهاوي</title>
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen grid place-items-center p-6" style="background: linear-gradient(135deg, #FFF7F1 0%, #FFE8DD 100%);">
    <div class="max-w-sm w-full text-center">
        <div class="w-24 h-24 rounded-3xl mx-auto mb-6 grid place-items-center shadow-2xl"
             style="background: linear-gradient(135deg, {{ $bg ?? '#2D5BFF' }}, {{ $bg2 ?? '#FFD440' }});">
            <div class="text-white">
                {!! $icon ?? '' !!}
            </div>
        </div>

        <div class="text-6xl font-black mb-1" style="color: {{ $bg ?? '#2D5BFF' }}">{{ $code ?? '' }}</div>
        <h1 class="text-2xl font-black text-ink-950 mb-2">{{ $heading ?? '' }}</h1>
        <p class="text-ink-500 text-sm leading-relaxed mb-8">{{ $message ?? '' }}</p>

        <div class="flex gap-2">
            <button onclick="window.history.back()" class="card-light flex-1 !py-3 text-sm font-bold text-ink-950 hover:bg-cream-100 transition">
                رجوع
            </button>
            <a href="/" class="btn-primary flex-1 justify-center !py-3">
                الرئيسية
            </a>
        </div>

        <div class="mt-10 flex items-center justify-center gap-2 text-ink-400">
            <span class="w-7 h-7 rounded-lg brand-bg grid place-items-center text-white font-black text-xs">ب</span>
            <span class="text-xs font-bold">بنهاوي</span>
        </div>
    </div>
</body>
</html>
