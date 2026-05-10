@extends('layouts.app', ['title' => 'دخول · بنهاوي'])

@section('content')
<div class="max-w-sm mx-auto pt-6 pb-10">

    {{-- Brand mark --}}
    <div class="text-center mb-7">
        <div class="w-14 h-14 mx-auto rounded-2xl brand-bg grid place-items-center text-white font-black text-xl shadow-lg shadow-coral-500/25">ب</div>
        <h1 class="text-xl font-black text-ink-950 mt-3.5">أهلاً بيك تاني</h1>
        <p class="text-ink-500 text-sm mt-1">ادخل برقمك وباسوردك علشان تكمّل.</p>
    </div>

    <form method="POST" action="{{ route('login.attempt') }}" class="card-light p-5 space-y-4">
        @csrf

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1.5 block">رقم الموبايل</label>
            <input type="tel" name="phone" inputmode="numeric" maxlength="11" required autofocus dir="ltr"
                   value="{{ old('phone') }}"
                   placeholder="010xxxxxxxx"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition text-center tracking-wider">
            @error('phone') <p class="text-blush-500 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-bold text-ink-500">الباسورد</label>
                <a href="{{ route('forgot') }}" class="text-[11px] font-bold text-coral-600 hover:underline">نسيت الباسورد؟</a>
            </div>
            <input type="password" name="password" required minlength="6" maxlength="80"
                   placeholder="••••••••"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
            @error('password') <p class="text-blush-500 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-3.5 text-sm">
            ادخل
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </button>
    </form>

    <p class="text-center text-ink-500 text-sm mt-6">
        لسه مش معانا؟
        <a href="{{ route('signup') }}" class="text-coral font-extrabold hover:underline">اعمل حساب</a>
    </p>

    <p class="text-center mt-4">
        <a href="{{ route('feed') }}" class="text-xs text-ink-400 hover:text-ink-950 inline-flex items-center gap-1">
            <x-icon name="arrow-right" class="w-3 h-3"/>
            رجوع للموقع
        </a>
    </p>
</div>
@endsection
