@extends('layouts.app', [
    'title'       => 'سجّل كصنايعي في بنها · مجاناً · بنها.shop',
    'description' => 'سجّل نشاطك في بنها.shop مجاناً. عملاء بنها والقليوبية يلاقوك بسهولة. شغل في تخصصك يوصلك يومياً.',
    'canonical'   => route('craftsmen.signup'),
])

@php
    $preTrade = request('trade');
@endphp

@section('content')
<div class="max-w-2xl mx-auto" data-no-edge-swipe>

    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('craftsmen.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <span class="text-xs font-bold text-ink-500">تسجيل صنايعي</span>
    </div>

    <div class="card-light p-5 mb-4 bg-gradient-to-br from-coral-500 to-coral-700 text-white">
        <h1 class="text-xl font-black mb-2">سجّل نشاطك في 3 دقايق</h1>
        <ul class="space-y-1.5 text-xs">
            <li class="flex items-center gap-1.5">✓ مجاناً للأبد، مفيش عمولة</li>
            <li class="flex items-center gap-1.5">✓ شغل يوصلك تلقائياً في تخصصك ومنطقتك</li>
            <li class="flex items-center gap-1.5">✓ العميل يكلّمك على واتساب مباشرة</li>
        </ul>
    </div>

    @auth
    <form method="POST" action="{{ route('craftsmen.signup.store') }}" class="space-y-4">
        @csrf

        {{-- Trade --}}
        <div class="card-light p-4">
            <label class="text-xs font-bold text-ink-500 mb-2 block">١. تخصصك *</label>
            <div class="grid grid-cols-3 gap-1.5">
                @foreach($trades as $t)
                    <label class="block cursor-pointer">
                        <input type="radio" name="sub_type" value="{{ $t['key'] }}" class="sr-only peer" required
                               @checked(old('sub_type', $preTrade) === $t['key'])>
                        <div class="text-center py-3 rounded-xl bg-cream-100 ring-1 ring-transparent peer-checked:bg-coral-500 peer-checked:text-white peer-checked:ring-coral-600 transition">
                            <div class="text-xl mb-0.5">{{ $t['emoji'] }}</div>
                            <div class="text-[10px] font-extrabold">{{ $t['label'] }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('sub_type') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Name --}}
        <div class="card-light p-4">
            <label class="text-xs font-bold text-ink-500 mb-2 block">٢. الاسم اللي هيظهر للعملاء *</label>
            <input type="text" name="name" required minlength="3" maxlength="120"
                   value="{{ old('name', $user?->username) }}"
                   placeholder="مثلاً: محمد السبّاك أو ورشة الأمل"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            @error('name') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Home zone --}}
        <div class="card-light p-4">
            <label class="text-xs font-bold text-ink-500 mb-2 block">٣. منطقتك الأساسية *</label>
            <select name="zone_id" required
                    class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                <option value="">اختار المنطقة</option>
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" @selected(old('zone_id') == $z->id)>{{ $z->name }}</option>
                @endforeach
            </select>
            @error('zone_id') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror

            <label class="text-[11px] font-bold text-ink-500 mt-4 mb-2 block">مناطق تانية تخدمها (اختياري)</label>
            <div class="grid grid-cols-3 gap-1.5">
                @foreach($zones as $z)
                    <label class="block cursor-pointer">
                        <input type="checkbox" name="service_zones[]" value="{{ $z->id }}"
                               @checked(in_array($z->id, old('service_zones', [])))
                               class="sr-only peer">
                        <div class="text-center py-2 rounded-lg bg-cream-100 text-[11px] font-bold peer-checked:bg-mint-500 peer-checked:text-white transition">
                            {{ $z->name }}
                        </div>
                    </label>
                @endforeach
            </div>
            <p class="text-[10px] text-ink-400 mt-1.5">طلبات من المناطق دي هتوصلك كمان.</p>
        </div>

        {{-- Contact --}}
        <div class="card-light p-4 space-y-3">
            <label class="text-xs font-bold text-ink-500 block">٤. أرقام التواصل *</label>
            <input type="tel" name="phone" required pattern="01[0125]\d{8}" maxlength="11" inputmode="numeric"
                   dir="ltr"
                   value="{{ old('phone', $user?->phone) }}"
                   placeholder="01xxxxxxxxx — رقم الموبايل"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 outline-0 border border-ink-950/8 focus:border-coral-500 transition font-mono">
            @error('phone') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

            <input type="tel" name="whatsapp" pattern="01[0125]\d{8}" maxlength="11" inputmode="numeric"
                   dir="ltr"
                   value="{{ old('whatsapp', $user?->phone) }}"
                   placeholder="01xxxxxxxxx — واتساب (اختياري، نفس رقم الموبايل بشكل افتراضي)"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 outline-0 border border-ink-950/8 focus:border-coral-500 transition font-mono">
            @error('whatsapp') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror
        </div>

        {{-- Experience + fees --}}
        <div class="card-light p-4 space-y-3">
            <label class="text-xs font-bold text-ink-500 block">٥. خبرتك وأسعارك (اختياري)</label>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <input type="number" name="years_experience" min="0" max="60"
                           value="{{ old('years_experience') }}"
                           placeholder="سنوات الخبرة" inputmode="numeric"
                           class="w-full bg-cream-100 rounded-xl px-3 py-2.5 text-sm outline-0 border border-ink-950/8 font-mono" dir="ltr">
                </div>
                <div>
                    <input type="number" name="min_callout_fee" min="0" max="5000"
                           value="{{ old('min_callout_fee') }}"
                           placeholder="سعر المعاينة (ج)" inputmode="numeric"
                           class="w-full bg-cream-100 rounded-xl px-3 py-2.5 text-sm outline-0 border border-ink-950/8 font-mono" dir="ltr">
                </div>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="accepts_emergency" value="0">
                <input type="checkbox" name="accepts_emergency" value="1" {{ old('accepts_emergency') ? 'checked' : '' }}
                       class="w-5 h-5 accent-coral-500">
                <span class="text-sm font-extrabold text-ink-950">⚡ بقبل طلبات طارئة ٢٤ ساعة</span>
            </label>
        </div>

        {{-- Description --}}
        <div class="card-light p-4">
            <label class="text-xs font-bold text-ink-500 mb-2 block">٦. كلمة عنك (اختياري)</label>
            <textarea name="description" rows="3" maxlength="500"
                      placeholder="اكتب كلمة قصيرة عن خبرتك أو الخدمات اللي بتقدّمها..."
                      class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('description') }}</textarea>
            @error('description') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-4 text-sm shadow-lg shadow-coral-500/30">
            ✓ سجّل وابدأ تستقبل شغل
        </button>
        <p class="text-[10px] text-ink-400 text-center leading-relaxed">
            بالتسجيل، أنت موافق إن بياناتك تظهر في الدليل العام عشان العملاء يلاقوك.
        </p>
    </form>
    @else
        {{-- Force login first — anchors the listing to a user account --}}
        <div class="card-light p-6 text-center">
            <div class="text-4xl mb-3">🛠️</div>
            <h3 class="text-base font-extrabold text-ink-950 mb-2">لازم تسجّل دخول الأول</h3>
            <p class="text-xs text-ink-500 mb-4 leading-relaxed">
                التسجيل سهل وسريع — رقم موبايل + كود تأكيد بس. عشان تقدر تدير نشاطك وترد على الطلبات.
            </p>
            <a href="{{ route('login') . '?redirect=' . urlencode(route('craftsmen.signup', request()->query())) }}"
               class="block py-3 rounded-full bg-coral-500 text-white text-sm font-extrabold mb-2">
                سجّل دخول
            </a>
            <a href="{{ route('signup') . '?redirect=' . urlencode(route('craftsmen.signup', request()->query())) }}"
               class="block py-3 rounded-full bg-white text-ink-950 text-sm font-extrabold ring-1 ring-ink-950/10">
                إنشاء حساب جديد
            </a>
        </div>
    @endauth
</div>
@endsection
