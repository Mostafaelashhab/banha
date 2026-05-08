@extends('layouts.app', ['title' => 'تأكيد الكود · بنهاوي'])

@section('content')
<div class="max-w-md mx-auto pt-6">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-black text-ink-950">اكتب الكود الجديد</h1>
        <p class="text-ink-500 mt-2">
            لو الرقم مسجّل، الكود وصلك على واتساب
            <br>
            <b dir="ltr" class="text-ink-950">{{ $phone }}</b>
        </p>
    </div>

    @if(session('debug_otp'))
        <div class="card-light p-3 mb-4 border-2 border-honey-400/30 bg-honey-400/10">
            <p class="text-xs text-ink-500">
                <b class="text-ink-950">[Local Debug]</b>
                الكود: <span dir="ltr" class="font-bold text-coral text-lg tracking-wider">{{ session('debug_otp') }}</span>
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('forgot.reset') }}" class="card-light p-5 space-y-3">
        @csrf
        <input type="hidden" name="phone" value="{{ $phone }}">

        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block text-center">الكود (6 أرقام)</label>
            <input type="tel" name="code" inputmode="numeric" maxlength="6" required autofocus
                   pattern="[0-9]{6}" placeholder="• • • • • •" dir="ltr"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-4 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition tracking-[0.6em] text-center text-2xl font-bold">
            @error('code') <p class="text-blush-500 text-xs mt-1 text-center">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3 pt-2">
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">الباسورد الجديد</label>
                <input type="password" name="password" required minlength="6" placeholder="••••••••"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            </div>
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">تأكيد</label>
                <input type="password" name="password_confirmation" required minlength="6" placeholder="••••••••"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            </div>
        </div>
        @error('password') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

        <button type="submit" class="btn-primary w-full justify-center !py-3">
            غيّر الباسورد
            <x-icon name="check" class="w-4 h-4"/>
        </button>
    </form>

    <p class="text-center text-ink-500 text-sm mt-6">
        مفيش كود؟
        <a href="{{ route('forgot') }}" class="text-coral font-bold hover:underline">ابعت تاني</a>
    </p>
</div>
@endsection
