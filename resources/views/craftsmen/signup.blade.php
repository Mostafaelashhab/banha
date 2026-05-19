@extends('layouts.app', [
    'title'       => 'سجّل كصنايعي في بنها · مجاناً · بنهاوي',
    'description' => 'سجّل نشاطك في بنهاوي مجاناً. عملاء بنها والقليوبية يلاقوك بسهولة. شغل في تخصصك يوصلك يومياً.',
    'canonical'   => route('craftsmen.signup'),
])

@php
    $preTrade = request('trade');
@endphp

@section('content')
<div class="max-w-3xl mx-auto" data-no-edge-swipe>

    {{-- ───── Header ───── --}}
    <div class="flex items-center gap-2 mb-3">
        <x-icon-tile icon="arrow-right" :href="route('craftsmen.index')" shape="circle" tone="cream" aria-label="رجوع"/>
        <span class="text-xs font-bold text-ink-500">تسجيل صنايعي</span>
    </div>

    {{-- ───── Intro card (solid surface, no gradient) ───── --}}
    <x-card class="mb-4">
        <div class="flex items-start gap-3 mb-3">
            <x-icon-tile icon="tools" size="lg"/>
            <div class="flex-1 min-w-0">
                <h1 class="text-xl font-black text-ink-950">سجّل نشاطك في 3 دقايق</h1>
                <p class="text-xs text-ink-500 mt-1">مجاناً للأبد، شغل يوصلك تلقائياً.</p>
            </div>
        </div>
        <ul class="space-y-2">
            <li class="flex items-center gap-2 text-xs text-ink-700">
                <x-icon-tile icon="check" tone="mint" size="sm"/>
                مجاناً للأبد، مفيش عمولة
            </li>
            <li class="flex items-center gap-2 text-xs text-ink-700">
                <x-icon-tile icon="check" tone="mint" size="sm"/>
                شغل يوصلك تلقائياً في تخصصك ومنطقتك
            </li>
            <li class="flex items-center gap-2 text-xs text-ink-700">
                <x-icon-tile icon="whatsapp" tone="mint" size="sm"/>
                العميل يكلّمك على واتساب مباشرة
            </li>
        </ul>
    </x-card>

    @auth
    <form method="POST" action="{{ route('craftsmen.signup.store') }}" class="space-y-4">
        @csrf

        {{-- 1. Trade --}}
        <x-card>
            <label class="text-xs font-bold text-ink-500 mb-2 block">١. تخصصك *</label>
            <div class="grid grid-cols-3 gap-1.5">
                @foreach($trades as $t)
                    <label class="block cursor-pointer">
                        <input type="radio" name="sub_type" value="{{ $t['key'] }}" class="sr-only peer" required
                               @checked(old('sub_type', $preTrade) === $t['key'])>
                        <div class="text-center py-3 rounded-xl bg-cream-100 ring-1 ring-transparent peer-checked:bg-coral-500 peer-checked:text-white peer-checked:ring-coral-600 transition">
                            <span class="inline-grid place-items-center mb-0.5">
                                <x-icon :name="$t['icon'] ?? 'tools'" class="w-5 h-5"/>
                            </span>
                            <div class="text-[10px] font-extrabold">{{ $t['label'] }}</div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('sub_type') <p class="text-[11px] font-bold text-blush-500 mt-1">{{ $message }}</p> @enderror
        </x-card>

        {{-- 2. Name --}}
        <x-card>
            <x-input name="name" required minlength="3" maxlength="120"
                     label="٢. الاسم اللي هيظهر للعملاء *"
                     :value="old('name', $user?->username)"
                     placeholder="مثلاً: محمد السبّاك أو ورشة الأمل"
                     :error="$errors->first('name')"/>
        </x-card>

        {{-- 3. Home zone + service zones --}}
        <x-card class="space-y-3">
            <label class="text-xs font-bold text-ink-500 block">٣. منطقتك الأساسية *</label>
            <select name="zone_id" required
                    class="w-full bg-cream-50 rounded-xl px-4 py-3 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white focus:ring-4 focus:ring-coral-500/10 transition">
                <option value="">اختار المنطقة</option>
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" @selected(old('zone_id') == $z->id)>{{ $z->name }}</option>
                @endforeach
            </select>
            @error('zone_id') <p class="text-[11px] font-bold text-blush-500">{{ $message }}</p> @enderror

            <label class="text-[11px] font-bold text-ink-500 mt-2 mb-2 block">مناطق تانية تخدمها (اختياري)</label>
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
        </x-card>

        {{-- 4. Contact --}}
        <x-card class="space-y-3">
            <label class="text-xs font-bold text-ink-500 block">٤. أرقام التواصل *</label>
            <x-input type="tel" name="phone" required pattern="01[0125]\d{8}" maxlength="11"
                     inputmode="numeric" dir="ltr"
                     :value="old('phone', $user?->phone)"
                     placeholder="01xxxxxxxxx — رقم الموبايل"
                     :error="$errors->first('phone')"/>

            <x-input type="tel" name="whatsapp" pattern="01[0125]\d{8}" maxlength="11"
                     inputmode="numeric" dir="ltr"
                     :value="old('whatsapp', $user?->phone)"
                     placeholder="01xxxxxxxxx — واتساب (اختياري)"
                     :error="$errors->first('whatsapp')"/>
        </x-card>

        {{-- 5. Experience + fees --}}
        <x-card class="space-y-3">
            <label class="text-xs font-bold text-ink-500 block">٥. خبرتك وأسعارك (اختياري)</label>
            <div class="grid grid-cols-2 gap-2">
                <x-input type="number" name="years_experience" min="0" max="60"
                         :value="old('years_experience')"
                         placeholder="سنوات الخبرة"
                         inputmode="numeric" dir="ltr"/>
                <x-input type="number" name="min_callout_fee" min="0" max="5000"
                         :value="old('min_callout_fee')"
                         placeholder="سعر المعاينة"
                         suffix="ج.م"
                         inputmode="numeric" dir="ltr"/>
            </div>
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="hidden" name="accepts_emergency" value="0">
                <input type="checkbox" name="accepts_emergency" value="1" {{ old('accepts_emergency') ? 'checked' : '' }}
                       class="w-5 h-5 accent-coral-500 shrink-0">
                <span class="inline-flex items-center gap-1.5 text-sm font-extrabold text-ink-950">
                    <x-icon name="bolt" class="w-4 h-4 text-coral-600"/>
                    بقبل طلبات طارئة ٢٤ ساعة
                </span>
            </label>
        </x-card>

        {{-- 6. Description --}}
        <x-card>
            <x-textarea name="description" rows="3" maxlength="500"
                        label="٦. كلمة عنك (اختياري)"
                        :value="old('description')"
                        placeholder="اكتب كلمة قصيرة عن خبرتك أو الخدمات اللي بتقدّمها..."
                        :error="$errors->first('description')"/>
        </x-card>

        {{-- Submit --}}
        <x-button type="submit" size="lg" icon="check" block>سجّل وابدأ تستقبل شغل</x-button>
        <p class="text-[10px] text-ink-400 text-center leading-relaxed">
            بالتسجيل، أنت موافق إن بياناتك تظهر في الدليل العام عشان العملاء يلاقوك.
        </p>
    </form>
    @else
        {{-- Force login first — anchors the listing to a user account --}}
        <x-card class="text-center" padding="lg">
            <x-icon-tile icon="tools" size="xl" class="mx-auto mb-3"/>
            <h3 class="text-base font-extrabold text-ink-950 mb-2">لازم تسجّل دخول الأول</h3>
            <p class="text-xs text-ink-500 mb-4 leading-relaxed max-w-sm mx-auto">
                التسجيل سهل وسريع — رقم موبايل + كود تأكيد بس. عشان تقدر تدير نشاطك وترد على الطلبات.
            </p>
            <div class="space-y-2">
                <x-button :href="route('login') . '?redirect=' . urlencode(route('craftsmen.signup', request()->query()))" size="lg" block>سجّل دخول</x-button>
                <x-button :href="route('signup') . '?redirect=' . urlencode(route('craftsmen.signup', request()->query()))" variant="secondary" size="lg" block>إنشاء حساب جديد</x-button>
            </div>
        </x-card>
    @endauth
</div>
@endsection
