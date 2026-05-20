@extends('layouts.app', ['title' => 'حساب جديد · بنهاوي'])

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
        <h1 class="auth-title">حساب جديد</h1>
        <p class="auth-sub">دقيقة واحدة وتبقى جوّة بنهاوي.</p>

        @if(! empty($refCode))
            <div class="mt-6 inline-flex items-center gap-2 bg-mint-100 text-mint-700 text-[11px] font-extrabold rounded-full px-3 py-1.5">
                ✓ بدعوة من صديق · هتاخدوا نقاط الاتنين
            </div>
        @endif

        <form method="POST" action="{{ route('signup.attempt') }}" class="mt-7 space-y-3">
            @csrf
            @if(! empty($refCode))
                <input type="hidden" name="ref" value="{{ $refCode }}">
            @endif

            <div>
                <input type="tel" name="phone" inputmode="numeric" maxlength="11" required autofocus dir="ltr"
                       value="{{ old('phone') }}" placeholder="رقم الموبايل (واتساب)"
                       class="auth-input text-center tracking-wider">
                @error('phone') <p class="auth-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <input type="text" name="username" required minlength="3" maxlength="30"
                       value="{{ old('username') }}" placeholder="الأسم"
                       class="auth-input">
                @error('username') <p class="auth-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <select name="zone_id" required class="auth-input">
                    <option value="">اختار منطقتك في بنها</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                    @endforeach
                </select>
                @error('zone_id') <p class="auth-error">{{ $message }}</p> @enderror
            </div>

            <div class="auth-input-with-icon">
                <input type="password" name="password" required minlength="6" maxlength="80"
                       placeholder="الباسورد" class="auth-input" id="signup-password">
                <button type="button" class="auth-input-icon is-clickable" onclick="(()=>{const i=document.getElementById('signup-password'); i.type = i.type==='password'?'text':'password';})()" aria-label="إظهار">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>

            <div>
                <input type="password" name="password_confirmation" required minlength="6" maxlength="80"
                       placeholder="أكد الباسورد" class="auth-input">
                @error('password') <p class="auth-error">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-start gap-2 text-[12px] text-ink-500 leading-relaxed cursor-pointer pt-2 px-2">
                <input type="checkbox" name="agree" value="1" required class="mt-0.5 accent-coral-500" {{ old('agree') ? 'checked' : '' }}>
                <span>موافق على <a href="#" class="text-coral-600 font-extrabold underline">شروط الاستخدام</a>.</span>
            </label>
            @error('agree') <p class="auth-error">{{ $message }}</p> @enderror

            <button type="submit" class="auth-btn mt-2">افتح حساب</button>
        </form>

        <a href="{{ route('login') }}" class="auth-cancel">عندك حساب؟ <b>ادخل</b></a>
    </div>
</div>
@endsection
