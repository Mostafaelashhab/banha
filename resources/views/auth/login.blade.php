@extends('layouts.app', ['title' => 'دخول · بنهاوي'])

@section('content')
<div class="auth-page">
    {{-- Hero band with decorative blue blobs --}}
    <div class="auth-hero" aria-hidden="true">
        <svg class="auth-blob primary" viewBox="0 0 360 360" preserveAspectRatio="xMidYMid meet">
            <path d="M180 30 C 270 30 330 100 330 190 C 330 280 260 330 170 330 C 80 330 30 260 30 170 C 30 90 100 30 180 30 Z"/>
        </svg>
        <svg class="auth-blob tint" viewBox="0 0 220 220" preserveAspectRatio="xMidYMid meet">
            <path d="M110 20 C 170 30 200 90 190 150 C 180 200 110 210 60 190 C 10 170 5 110 30 70 C 50 35 80 15 110 20 Z"/>
        </svg>
    </div>

    <div class="auth-content">
        <h1 class="auth-title">دخول</h1>
        <p class="auth-sub">يا أهلاً بيك تاني في بنهاوي</p>

        <form method="POST" action="{{ route('login.attempt') }}" class="mt-8 space-y-3">
            @csrf

            <div>
                <input type="tel" name="phone" inputmode="numeric" maxlength="11" required autofocus dir="ltr"
                       value="{{ old('phone') }}" placeholder="رقم الموبايل"
                       class="auth-input text-center tracking-wider">
                @error('phone') <p class="auth-error">{{ $message }}</p> @enderror
            </div>

            <div class="auth-input-with-icon">
                <input type="password" name="password" required minlength="6" maxlength="80"
                       placeholder="الباسورد"
                       class="auth-input" id="login-password">
                <button type="button" class="auth-input-icon is-clickable" onclick="(()=>{const i=document.getElementById('login-password'); i.type = i.type==='password'?'text':'password';})()" aria-label="إظهار">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
                @error('password') <p class="auth-error">{{ $message }}</p> @enderror
            </div>

            <div class="text-end">
                <a href="{{ route('forgot') }}" class="text-[12px] font-extrabold text-coral-600 hover:underline">نسيت الباسورد؟</a>
            </div>

            <button type="submit" class="auth-btn mt-2">ادخل</button>
        </form>

        <a href="{{ route('signup') }}" class="auth-cancel">لسه مش معانا؟ <b>اعمل حساب</b></a>
        <a href="{{ route('feed') }}" class="auth-cancel" style="padding-top:0">رجوع</a>
    </div>
</div>
@endsection
