@extends('layouts.app', ['title' => 'دخول · بنهاوي'])

@section('content')
<div class="max-w-md mx-auto pt-6">
    <div class="text-center mb-8">
        <h1 class="text-3xl md:text-4xl font-black text-ink-950">أهلاً يا بنهاوي 👋</h1>
        <p class="text-ink-500 mt-2">ادخل برقمك والباسورد علشان تكمّل.</p>
    </div>

    <form method="POST" action="{{ route('login.attempt') }}" class="card-light p-5 space-y-3">
        @csrf
        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">رقم الموبايل</label>
            <input type="tel" name="phone" inputmode="numeric" maxlength="11" autofocus
                   value="{{ old('phone') }}"
                   placeholder="010xxxxxxxx"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            @error('phone') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">الباسورد</label>
            <input type="password" name="password" required minlength="6" maxlength="80"
                   placeholder="••••••••"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            @error('password') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-3">
            ادخل
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </button>

        <div class="text-center pt-1">
            <a href="{{ route('forgot') }}" class="text-ink-500 text-xs hover:text-ink-950 hover:underline">
                نسيت الباسورد؟
            </a>
        </div>
    </form>

    <p class="text-center text-ink-500 text-sm mt-6">
        أول مرة هنا؟
        <a href="{{ route('signup') }}" class="text-coral font-bold hover:underline">اعمل حساب</a>
    </p>
</div>
@endsection
