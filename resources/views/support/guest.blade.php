@extends('layouts.app', ['title' => 'الدعم · بنهاوي'])

@section('content')
<div class="max-w-md mx-auto pt-4">

    <div class="text-center mb-6">
        <div class="w-14 h-14 mx-auto rounded-2xl brand-bg grid place-items-center text-white shadow-lg shadow-coral-500/25">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </div>
        <h1 class="text-xl font-black text-ink-950 mt-3.5">إزاي نقدر نساعدك؟</h1>
        <p class="text-ink-500 text-sm mt-1">عندنا فريق دعم بيرد على المستخدمين خلال ساعة.</p>
    </div>

    {{-- Login CTA: live chat needs auth --}}
    <a href="{{ route('login') }}?redirect={{ urlencode(route('support')) }}"
       class="card-light p-4 mb-3 flex items-center gap-3 hover:bg-cream-100 transition">
        <span class="w-11 h-11 rounded-2xl bg-coral-500 grid place-items-center text-white shrink-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z"/>
            </svg>
        </span>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-extrabold text-ink-950">شات لايف مع الدعم</div>
            <p class="text-[11px] text-ink-500">سجّل دخول الأول علشان نقدر نرد عليك ونتابع طلبك.</p>
        </div>
        <x-icon name="arrow-left" class="w-4 h-4 text-ink-400"/>
    </a>

    {{-- WhatsApp --}}
    <a href="https://wa.me/201550047838?text={{ urlencode('عاوز أتواصل مع دعم بنهاوي') }}" target="_blank"
       class="card-light p-4 mb-3 flex items-center gap-3 hover:bg-cream-100 transition">
        <span class="w-11 h-11 rounded-2xl grid place-items-center text-white shrink-0"
              style="background: linear-gradient(135deg, #25D366, #128C7E);">
            <x-icon name="whatsapp" class="w-5 h-5"/>
        </span>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-extrabold text-ink-950">واتساب الدعم</div>
            <p class="text-[11px] text-ink-500" dir="ltr">01550047838</p>
        </div>
        <x-icon name="arrow-left" class="w-4 h-4 text-ink-400"/>
    </a>

    {{-- Phone --}}
    <a href="tel:+201022345504" class="card-light p-4 mb-3 flex items-center gap-3 hover:bg-cream-100 transition">
        <span class="w-11 h-11 rounded-2xl bg-coral-100 text-coral-600 grid place-items-center shrink-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
        </span>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-extrabold text-ink-950">اتصل بالدعم</div>
            <p class="text-[11px] text-ink-500" dir="ltr">01022345504</p>
        </div>
        <x-icon name="arrow-left" class="w-4 h-4 text-ink-400"/>
    </a>

    <p class="text-center mt-6">
        <a href="{{ route('feed') }}" class="text-xs text-ink-400 hover:text-ink-950 inline-flex items-center gap-1">
            <x-icon name="arrow-right" class="w-3 h-3"/>
            رجوع للموقع
        </a>
    </p>
</div>
@endsection
