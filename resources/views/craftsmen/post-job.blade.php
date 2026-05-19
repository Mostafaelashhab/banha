@extends('layouts.app', [
    'title'       => 'اطلب صنايعي في بنها .بنهاوي  ',
    'description' => 'اطلب سباك، كهربائي، فني تكييف، نقاش أو أي صنايعي في بنها. الصنايعية اللي في تخصصك ومنطقتك يتواصلوا معاك خلال دقايق.',
    'canonical'   => route('craft-jobs.create'),
])

@php
    $preTrade = request('trade');
@endphp

@section('content')
<div class="max-w-3xl mx-auto" data-no-edge-swipe>

    {{-- ───── Header ───── --}}
    <div class="flex items-center gap-2 mb-3">
        <x-icon-tile icon="arrow-right" :href="route('craftsmen.index')" shape="circle" tone="cream" aria-label="رجوع"/>
        <span class="text-xs font-bold text-ink-500">طلب صنايعي</span>
    </div>

    {{-- ───── Intro card (solid mint accent, no gradient) ───── --}}
    <x-card class="mb-4 bg-mint-50 ring-1 ring-mint-500/20">
        <div class="flex items-start gap-3">
            <x-icon-tile icon="clipboard" tone="mint" size="lg"/>
            <div class="flex-1">
                <h1 class="text-xl font-black text-ink-950 mb-1">اكتب اللي محتاجه</h1>
                <p class="text-xs text-ink-500 leading-relaxed">
                    طلبك هيوصل لكل الصنايعية في تخصصك ومنطقتك تلقائياً. أول واحد يرد → بتكلّمه على واتساب.
                </p>
            </div>
        </div>
    </x-card>

    <form method="POST" action="{{ route('craft-jobs.store') }}" class="space-y-4">
        @csrf

        {{-- Step 1: Trade --}}
        <x-card>
            <label class="text-xs font-bold text-ink-500 mb-2 block">١. التخصص اللي محتاجه *</label>
            <div class="grid grid-cols-3 gap-1.5" data-trade-grid>
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

        {{-- Step 2: Zone + address --}}
        <x-card class="space-y-3">
            <label class="text-xs font-bold text-ink-500 block">٢. منطقتك *</label>
            <select name="zone_id" required
                    class="w-full bg-cream-50 rounded-xl px-4 py-3 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white focus:ring-4 focus:ring-coral-500/10 transition">
                <option value="">اختار المنطقة</option>
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" @selected(old('zone_id') == $z->id)>{{ $z->name }}</option>
                @endforeach
            </select>
            @error('zone_id') <p class="text-[11px] font-bold text-blush-500">{{ $message }}</p> @enderror

            <x-input name="address" maxlength="200"
                     :value="old('address')"
                     placeholder="العنوان بالتفصيل (شارع، علامة مميزة...)"/>
        </x-card>

        {{-- Step 3: Description --}}
        <x-card>
            <label class="text-xs font-bold text-ink-500 mb-2 block">٣. اشرح الشغلانة *</label>
            <x-textarea name="description" rows="4" maxlength="1000" required
                        placeholder="مثلاً: محتاج سباك يصلح تسريب في الحمام، الشقة في الدور 4، يفضل النهارده أو بكرة الصبح..."
                        :value="old('description')"
                        :error="$errors->first('description')"/>
        </x-card>

        {{-- Step 4: Urgency --}}
        <x-card>
            <label class="text-xs font-bold text-ink-500 mb-2 block">٤. متى محتاجه؟ *</label>
            <div class="grid grid-cols-2 gap-1.5">
                @foreach(\App\Models\JobRequest::URGENCIES as $key => $label)
                    <label class="block cursor-pointer">
                        <input type="radio" name="urgency" value="{{ $key }}" class="sr-only peer" required
                               @checked(old('urgency', 'today') === $key)>
                        <div class="text-center py-2.5 rounded-xl bg-cream-100 text-xs font-extrabold peer-checked:bg-coral-500 peer-checked:text-white transition">
                            {{ $label }}
                        </div>
                    </label>
                @endforeach
            </div>
            @error('urgency') <p class="text-[11px] font-bold text-blush-500 mt-1">{{ $message }}</p> @enderror
        </x-card>

        {{-- Step 5: Optional budget --}}
        <x-card>
            <label class="text-xs font-bold text-ink-500 mb-2 block">٥. الميزانية (اختياري)</label>
            <div class="grid grid-cols-2 gap-2">
                <x-input type="number" name="budget_min" min="0" max="1000000"
                         :value="old('budget_min')" placeholder="من (ج)"
                         inputmode="numeric" dir="ltr"/>
                <x-input type="number" name="budget_max" min="0" max="1000000"
                         :value="old('budget_max')" placeholder="إلى (ج)"
                         inputmode="numeric" dir="ltr"
                         :error="$errors->first('budget_max')"/>
            </div>
            <p class="text-[10px] text-ink-400 mt-1.5">سيبها فاضية لو هتتفق معاه بعد المعاينة.</p>
        </x-card>

        {{-- Step 6: Contact --}}
        <x-card class="space-y-3">
            <label class="text-xs font-bold text-ink-500 block">٦. بياناتك *</label>
            <x-input name="name" required maxlength="80"
                     :value="old('name', $user?->username)"
                     placeholder="اسمك"
                     :error="$errors->first('name')"/>

            <x-input type="tel" name="phone" required pattern="01[0125]\d{8}" maxlength="11"
                     inputmode="numeric" dir="ltr"
                     :value="old('phone', $user?->phone)"
                     placeholder="01xxxxxxxxx"
                     helper="رقم الموبايل هيظهر بس للصنايعية اللي بيردوا، مش علني."
                     :error="$errors->first('phone')"/>
        </x-card>

        {{-- Submit --}}
        <x-button type="submit" size="lg" icon="arrow-left" iconEnd block>ابعت الطلب</x-button>
        <p class="text-[10px] text-ink-400 text-center leading-relaxed">
            بإرسال الطلب، أنت موافق إن الصنايعية اللي في تخصصك يتواصلوا معاك على رقم الموبايل اللي كتبته.
        </p>
    </form>
</div>
@endsection
