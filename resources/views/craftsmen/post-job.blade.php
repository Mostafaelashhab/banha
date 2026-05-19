@extends('layouts.app', [
    'title'       => 'اطلب صنايعي في بنها · بنها.shop',
    'description' => 'اطلب سباك، كهربائي، فني تكييف، نقاش أو أي صنايعي في بنها. الصنايعية اللي في تخصصك ومنطقتك يتواصلوا معاك خلال دقايق.',
    'canonical'   => route('craft-jobs.create'),
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
        <span class="text-xs font-bold text-ink-500">طلب صنايعي</span>
    </div>

    <div class="card-light p-5 mb-4 bg-gradient-to-br from-mint-50 to-mint-100/30 ring-1 ring-mint-500/20">
        <h1 class="text-xl font-black text-ink-950 mb-1">اكتب اللي محتاجه</h1>
        <p class="text-xs text-ink-500 leading-relaxed">
            طلبك هيوصل لكل الصنايعية في تخصصك ومنطقتك تلقائياً. أول واحد يرد → بتكلّمه على واتساب.
        </p>
    </div>

    <form method="POST" action="{{ route('craft-jobs.store') }}" class="space-y-4">
        @csrf

        {{-- Step 1: Trade --}}
        <div class="card-light p-4">
            <label class="text-xs font-bold text-ink-500 mb-2 block">١. التخصص اللي محتاجه *</label>
            <div class="grid grid-cols-3 gap-1.5" data-trade-grid>
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

        {{-- Step 2: Zone + address --}}
        <div class="card-light p-4 space-y-3">
            <label class="text-xs font-bold text-ink-500 block">٢. منطقتك *</label>
            <select name="zone_id" required
                    class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                <option value="">اختار المنطقة</option>
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" @selected(old('zone_id') == $z->id)>{{ $z->name }}</option>
                @endforeach
            </select>
            @error('zone_id') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

            <input type="text" name="address" maxlength="200"
                   value="{{ old('address') }}"
                   placeholder="العنوان بالتفصيل (شارع، علامة مميزة...)"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
        </div>

        {{-- Step 3: Description --}}
        <div class="card-light p-4">
            <label class="text-xs font-bold text-ink-500 mb-2 block">٣. اشرح الشغلانة *</label>
            <textarea name="description" rows="4" maxlength="1000" required
                      placeholder="مثلاً: محتاج سباك يصلح تسريب في الحمام، الشقة في الدور 4، يفضل النهارده أو بكرة الصبح..."
                      class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('description') }}</textarea>
            @error('description') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Step 4: Urgency --}}
        <div class="card-light p-4">
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
            @error('urgency') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Step 5: Optional budget --}}
        <div class="card-light p-4">
            <label class="text-xs font-bold text-ink-500 mb-2 block">٥. الميزانية (اختياري)</label>
            <div class="grid grid-cols-2 gap-2">
                <input type="number" name="budget_min" min="0" max="1000000"
                       value="{{ old('budget_min') }}" placeholder="من (ج)" inputmode="numeric"
                       class="bg-cream-100 rounded-2xl px-4 py-3 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition" dir="ltr">
                <input type="number" name="budget_max" min="0" max="1000000"
                       value="{{ old('budget_max') }}" placeholder="إلى (ج)" inputmode="numeric"
                       class="bg-cream-100 rounded-2xl px-4 py-3 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition" dir="ltr">
            </div>
            <p class="text-[10px] text-ink-400 mt-1.5">سيبها فاضية لو هتتفق معاه بعد المعاينة.</p>
            @error('budget_max') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Step 6: Contact --}}
        <div class="card-light p-4 space-y-3">
            <label class="text-xs font-bold text-ink-500 block">٦. بياناتك *</label>
            <input type="text" name="name" required maxlength="80"
                   value="{{ old('name', $user?->username) }}"
                   placeholder="اسمك"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            @error('name') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

            <input type="tel" name="phone" required pattern="01[0125]\d{8}" maxlength="11" inputmode="numeric"
                   dir="ltr"
                   value="{{ old('phone', $user?->phone) }}"
                   placeholder="01xxxxxxxxx"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition font-mono">
            <p class="text-[10px] text-ink-400">رقم الموبايل هيظهر بس للصنايعية اللي بيردوا، مش علني.</p>
            @error('phone') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-primary w-full justify-center !py-4 text-sm shadow-lg shadow-coral-500/30">
            📨 ابعت الطلب
        </button>
        <p class="text-[10px] text-ink-400 text-center leading-relaxed">
            بإرسال الطلب، أنت موافق إن الصنايعية اللي في تخصصك يتواصلوا معاك على رقم الموبايل اللي كتبته.
        </p>
    </form>
</div>
@endsection
