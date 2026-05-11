@extends('layouts.app', ['title' => 'حساب جديد · بنهاوي'])

@section('content')
<div class="max-w-sm mx-auto pt-6 pb-10">

    {{-- Brand mark --}}
    <div class="text-center mb-6">
        <div class="w-14 h-14 mx-auto rounded-2xl brand-bg grid place-items-center text-white font-black text-xl shadow-lg shadow-coral-500/25">ب</div>
        <h1 class="text-xl font-black text-ink-950 mt-3.5">انضم لـ <span class="text-coral">بنهاوي</span></h1>
        <p class="text-ink-500 text-sm mt-1">دقيقة واحدة وتبقى جوّة.</p>
    </div>

    {{-- Compact account-review notice (collapsible) --}}
    <details class="card-light !shadow-none border-coral-500/20 bg-coral-50/60 p-3 mb-4 group">
        <summary class="flex items-center gap-2 cursor-pointer list-none">
            <span class="w-7 h-7 rounded-full pill-coral grid place-items-center shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </span>
            <span class="text-xs font-bold text-ink-950 flex-1">قبل ما تسجّل · اضغط للتفاصيل</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4 text-ink-400 transition group-open:rotate-180">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </summary>
        <p class="text-[11px] text-ink-500 leading-relaxed mt-2.5">
            هنبعتلك كود تفعيل على <b class="text-ink-950">واتساب</b>، فاستخدم رقم شغّال.
            الحسابات بتتراجع باستمرار، ولو حصلت مخالفة (تنمّر، إشاعات، spam) بنحظر الحساب مباشرة.
        </p>
    </details>

    <form method="POST" action="{{ route('signup.attempt') }}" class="card-light p-5 space-y-4">
        @csrf
        @if(! empty($refCode))
            <input type="hidden" name="ref" value="{{ $refCode }}">
            <div class="bg-mint-100 text-mint-700 text-[11px] font-bold rounded-xl px-3 py-2 -mt-1">
                ✓ انت داخل بدعوة من صديق · هتاخدوا نقاط الاتنين
            </div>
        @endif

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1.5 block">رقم الموبايل</label>
            <input type="tel" name="phone" inputmode="numeric" maxlength="11" required autofocus dir="ltr"
                   value="{{ old('phone') }}"
                   placeholder="010xxxxxxxx"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition text-center tracking-wider">
            <p class="text-[10px] text-ink-400 mt-1">رقم واتساب شغّال — هنبعت كود التفعيل عليه.</p>
            @error('phone') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1.5 block">اليوزر نيم</label>
            <input type="text" name="username" required minlength="3" maxlength="30"
                   value="{{ old('username') }}"
                   placeholder="بنهاوي_مغمور"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
            @error('username') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1.5 block">منطقتك</label>
            <select name="zone_id" required
                    class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3.5 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                <option value="">اختار منطقة في بنها</option>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                @endforeach
            </select>
            @error('zone_id') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1.5 block">باسورد</label>
                <input type="password" name="password" required minlength="6" maxlength="80"
                       placeholder="••••••••"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
            </div>
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1.5 block">تأكيد</label>
                <input type="password" name="password_confirmation" required minlength="6" maxlength="80"
                       placeholder="••••••••"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
            </div>
        </div>
        @error('password') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

        <label class="flex items-start gap-2 text-xs text-ink-500 leading-relaxed cursor-pointer pt-1">
            <input type="checkbox" name="agree" value="1" required class="mt-0.5 accent-coral-500" {{ old('agree') ? 'checked' : '' }}>
            <span>موافق على <a href="#" class="text-coral-600 font-bold underline">شروط الاستخدام</a> · أي مخالفة بتحظر الحساب فوراً.</span>
        </label>
        @error('agree') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

        <button type="submit" class="btn-primary w-full justify-center !py-3.5 text-sm">
            افتح حساب
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </button>
    </form>

    <p class="text-center text-ink-500 text-sm mt-6">
        عندك حساب؟
        <a href="{{ route('login') }}" class="text-coral font-extrabold hover:underline">ادخل</a>
    </p>

    <p class="text-center mt-4">
        <a href="{{ route('feed') }}" class="text-xs text-ink-400 hover:text-ink-950 inline-flex items-center gap-1">
            <x-icon name="arrow-right" class="w-3 h-3"/>
            رجوع للموقع
        </a>
    </p>
</div>
@endsection
