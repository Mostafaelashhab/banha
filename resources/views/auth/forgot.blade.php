@extends('layouts.app', ['title' => 'نسيت الباسورد · بنهاوي'])

@section('content')
<div class="max-w-md mx-auto pt-6">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-black text-ink-950">نسيت الباسورد؟</h1>
        <p class="text-ink-500 mt-2">اكتب رقم موبايلك، وهنبعتلك كود على واتساب لتغيير الباسورد.</p>
    </div>

    <form method="POST" action="{{ route('forgot.send') }}" class="card-light p-5 space-y-3">
        @csrf
        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">رقم الموبايل</label>
            <input type="tel" name="phone" inputmode="numeric" maxlength="11" autofocus required
                   value="{{ old('phone') }}"
                   placeholder="010xxxxxxxx"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            @error('phone') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-3">
            ابعتلي الكود على واتساب
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9z"/>
            </svg>
        </button>
    </form>

    <p class="text-center text-ink-500 text-sm mt-6">
        فاكر الباسورد؟
        <a href="{{ route('login') }}" class="text-coral font-bold hover:underline">ادخل</a>
    </p>
</div>
@endsection
