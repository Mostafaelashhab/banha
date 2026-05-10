@extends('layouts.app', ['title' => 'تأكيد ملكية ' . $business->name])

@section('content')
@php
    $cm = $business->categoryMeta();
    $sm = $business->subTypeMeta();
    $sent = (bool) session('claim_otp_sent');
    $hasPhone = ! empty($business->phone);
@endphp

<div class="max-w-md mx-auto pt-2">

    {{-- Header --}}
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.show', $business) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950 hover:bg-cream-100 transition">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-base font-extrabold text-ink-950">تأكيد ملكية النشاط</h1>
    </div>

    {{-- Business preview card --}}
    <div class="card-light p-4 mb-4 flex items-center gap-3">
        <span class="w-12 h-12 rounded-2xl grid place-items-center shrink-0"
              style="background: {{ $cm['color'] }}1a; color: {{ $cm['color'] }};">
            <x-icon :name="$sm['icon'] ?? 'bag'" class="w-6 h-6"/>
        </span>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-extrabold text-ink-950 truncate">{{ $business->name }}</div>
            <div class="text-[11px] text-ink-500 truncate">{{ $sm['label'] }} · {{ $business->zone->name ?? 'بنها' }}</div>
        </div>
    </div>

    @if(! $hasPhone)
        {{-- Manual claim path: no phone on record --}}
        <div class="card-light p-5 mb-3 border-honey-500/20 bg-honey-100/30">
            <h2 class="text-sm font-extrabold text-ink-950 mb-2 inline-flex items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-honey-700">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                النشاط ده مفيش رقم تليفون مسجّل
            </h2>
            <p class="text-xs text-ink-500 leading-relaxed mb-3">
                مينفعش نأكّد الملكية تلقائياً بدون رقم. تواصل مع الدعم وهنراجع الموضوع يدوياً (هتحتاج إثبات: سجل تجاري أو فاتورة باسم النشاط).
            </p>
            <a href="{{ route('support') }}" class="btn-primary justify-center w-full !py-2.5 text-xs">
                تواصل مع الدعم
                <x-icon name="arrow-left" class="w-3.5 h-3.5"/>
            </a>
        </div>
    @elseif(! $sent)
        {{-- Step 1: send OTP --}}
        <div class="card-light p-5 mb-3">
            <h2 class="text-sm font-extrabold text-ink-950 mb-2">١. هنبعت كود تأكيد</h2>
            <p class="text-xs text-ink-500 leading-relaxed mb-4">
                هنبعت كود ٦ أرقام على واتساب رقم النشاط المسجّل. لو الرقم بتاعك فعلاً، هتقدر تستلم الكود وتأكّد إنك صاحب النشاط.
            </p>

            <div class="bg-cream-100 rounded-2xl p-3 mb-4 flex items-center gap-3">
                <span class="w-9 h-9 rounded-full grid place-items-center text-white shrink-0"
                      style="background: linear-gradient(135deg, #25D366, #128C7E);">
                    <x-icon name="whatsapp" class="w-4 h-4"/>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-[11px] text-ink-500">رقم النشاط</div>
                    @php
                        $digits = preg_replace('/\D/', '', $business->phone ?? '');
                        $tail   = substr($digits, -11);
                        $masked = strlen($tail) >= 11 ? substr($tail, 0, 3).'****'.substr($tail, -3) : $tail;
                    @endphp
                    <div class="text-sm font-bold text-ink-950" dir="ltr">{{ $masked }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('directory.claim.request', $business) }}">
                @csrf
                <button type="submit" class="btn-primary justify-center w-full !py-3 text-sm">
                    ابعت كود التأكيد
                    <x-icon name="arrow-left" class="w-4 h-4"/>
                </button>
                @error('code') <p class="text-blush-500 text-xs mt-2 text-center">{{ $message }}</p> @enderror
            </form>
        </div>
    @else
        {{-- Step 2: enter OTP --}}
        <div class="card-light p-5 mb-3">
            <h2 class="text-sm font-extrabold text-ink-950 mb-2">٢. دخّل الكود</h2>
            <p class="text-xs text-ink-500 leading-relaxed mb-4">
                {{ session('flash') }}
            </p>

            <form method="POST" action="{{ route('directory.claim.verify', $business) }}">
                @csrf
                <input type="text" name="code" inputmode="numeric" maxlength="6" minlength="6"
                       autofocus required dir="ltr"
                       placeholder="••••••"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition text-center text-2xl font-black tracking-[0.5em]">
                @error('code') <p class="text-blush-500 text-xs mt-2">{{ $message }}</p> @enderror

                <button type="submit" class="btn-primary justify-center w-full !py-3 text-sm mt-3">
                    أكّد وامتلك النشاط
                    <x-icon name="check" class="w-4 h-4"/>
                </button>
            </form>

            <form method="POST" action="{{ route('directory.claim.request', $business) }}" class="mt-3">
                @csrf
                <button type="submit" class="text-xs text-ink-400 hover:text-coral-600 hover:underline mx-auto block">
                    لم تستلم الكود؟ ابعت تاني
                </button>
            </form>
        </div>
    @endif

    {{-- Trust note --}}
    <div class="card-light p-3 bg-cream-100/50 border-ink-950/5">
        <p class="text-[11px] text-ink-500 leading-relaxed">
            <b class="text-ink-950">إيه اللي بيحصل بعد التأكيد؟</b>
            بعد ما تتأكّد، النشاط ده هيبقى ملكك الكامل — تقدر تعدّل الاسم والبيانات والمنيو والصور وتظهر على الخريطة. وأي إساءة بتقفل حسابك مباشرة.
        </p>
    </div>
</div>
@endsection
