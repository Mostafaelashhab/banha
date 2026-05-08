@extends('layouts.app', ['title' => 'حساب جديد · بنهاوي'])

@section('content')
<div class="max-w-md mx-auto pt-6">
    <div class="text-center mb-6">
        <h1 class="text-3xl md:text-4xl font-black text-ink-950">انضم لـ <span class="text-coral">بنهاوي</span></h1>
        <p class="text-ink-500 mt-2">دقيقة واحدة وهتبقى جوة.</p>
    </div>

    {{-- Account-review notice --}}
    <div class="card-light !shadow-none border-coral-500/20 bg-coral-50 p-4 mb-4 flex gap-3">
        <span class="w-9 h-9 rounded-full pill-mint grid place-items-center shrink-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9z"/>
            </svg>
        </span>
        <p class="text-xs text-ink-500 leading-relaxed">
            <b class="text-ink-950">قبل ما تسجّل:</b>
            بعد ما تكمّل بياناتك هنبعتلك كود تفعيل على
            <b class="text-ink-950">واتساب</b> — استخدم رقم واتساب شغّال.
            الحسابات بتتراجع باستمرار، ولو حصلت مخالفة (تنمّر، إشاعات، spam، تبليغ كاذب) بنحظر الحساب مباشرة.
        </p>
    </div>

    <form method="POST" action="{{ route('signup.attempt') }}" class="card-light p-5 space-y-3">
        @csrf

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">رقم الموبايل</label>
            <input type="tel" name="phone" inputmode="numeric" maxlength="11" required autofocus
                   value="{{ old('phone') }}"
                   placeholder="010xxxxxxxx"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            @error('phone') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">اليوزر نيم</label>
            <input type="text" name="username" required minlength="3" maxlength="30"
                   value="{{ old('username') }}"
                   placeholder="بنهاوي_مغمور"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            @error('username') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">منطقتك</label>
            <select name="zone_id" required
                    class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                <option value="">اختار</option>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                @endforeach
            </select>
            @error('zone_id') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">إنت إيه؟</label>
            <div class="grid grid-cols-3 gap-2">
                @php
                    $personas = [
                        'student'   => 'طالب',
                        'worker'    => 'موظف',
                        'merchant'  => 'تاجر',
                        'homemaker' => 'في البيت',
                        'resident'  => 'ساكن',
                    ];
                @endphp
                @foreach($personas as $key => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="persona" value="{{ $key }}" class="peer sr-only" {{ old('persona', 'resident') === $key ? 'checked' : '' }}>
                        <div class="bg-cream-100 border border-ink-950/8 rounded-2xl px-3 py-2.5 text-center text-sm font-bold peer-checked:bg-coral-500 peer-checked:text-white peer-checked:border-coral-500 transition">
                            {{ $label }}
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">باسورد *</label>
                <input type="password" name="password" required minlength="6" maxlength="80"
                       placeholder="••••••••"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            </div>
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">تأكيد *</label>
                <input type="password" name="password_confirmation" required minlength="6" maxlength="80"
                       placeholder="••••••••"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            </div>
        </div>
        @error('password') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

        <label class="flex items-start gap-2 text-xs text-ink-500 leading-relaxed cursor-pointer pt-1">
            <input type="checkbox" name="agree" value="1" required class="mt-0.5 accent-coral-500" {{ old('agree') ? 'checked' : '' }}>
            <span>
                موافق إن حسابي يتراجع باستمرار، ولو خالفت الشروط هتتحظر مباشرة.
            </span>
        </label>
        @error('agree') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

        <button type="submit" class="btn-primary w-full justify-center !py-3">
            افتح حساب
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </button>
    </form>

    <p class="text-center text-ink-500 text-sm mt-6">
        عندك حساب؟
        <a href="{{ route('login') }}" class="text-coral font-bold hover:underline">ادخل</a>
    </p>
</div>
@endsection
