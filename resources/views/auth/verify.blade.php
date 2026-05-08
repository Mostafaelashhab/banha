@extends('layouts.app', ['title' => 'تفعيل حسابك · بنهاوي'])

@section('content')
<div class="max-w-md mx-auto pt-6">
    <div class="text-center mb-8">
        <div class="w-20 h-20 rounded-3xl brand-bg grid place-items-center mx-auto mb-5 shadow-glow">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-9 h-9">
                <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-black text-ink-950">فعّل حسابك</h1>
        <p class="text-ink-500 mt-2">
            بعتنالك كود تفعيل على واتساب الرقم
            <br>
            <b dir="ltr" class="text-ink-950">{{ $user->phone }}</b>
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

    <form method="POST" action="{{ route('verify.attempt') }}" class="card-light p-5 space-y-4">
        @csrf
        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block text-center">اكتب الكود اللي وصلك</label>
            <input type="tel" name="code" inputmode="numeric" maxlength="6" required autofocus
                   pattern="[0-9]{6}" placeholder="• • • • • •"
                   dir="ltr"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-4 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition tracking-[0.6em] text-center text-2xl font-bold">
            @error('code') <p class="text-blush-500 text-xs mt-2 text-center">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-3">
            تأكيد التفعيل
            <x-icon name="check" class="w-4 h-4"/>
        </button>
    </form>

    <form method="POST" action="{{ route('verify.send') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-coral font-bold text-sm hover:underline">
            مفيش كود؟ ابعت تاني
        </button>
    </form>

    <div class="card-light !shadow-none border-coral-500/20 bg-coral-50 p-4 mt-6">
        <p class="text-xs text-ink-500 leading-relaxed">
            <b class="text-ink-950">⚠️ الكود سرّي:</b>
            متشاركهوش مع حد. فريق بنهاوي مش هيطلبه منك أبداً. الكود صالح ٥ دقايق فقط.
        </p>
    </div>

    <div class="mt-6 text-center">
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-ink-400 text-sm hover:text-ink-950">
                دخلت برقم غلط؟ خروج
            </button>
        </form>
    </div>
</div>
@endsection
