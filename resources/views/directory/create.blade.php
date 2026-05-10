@extends('layouts.app', ['title' => 'سجّل نشاطك · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950">سجّل نشاطك</h1>
    </div>

    <div class="card-light !shadow-none border-coral-500/20 bg-coral-50 p-3 mb-4">
        <p class="text-xs text-ink-500 leading-relaxed">
            <b class="text-ink-950">قبل ما تسجّل:</b>
            بنحط نشاطك في الدليل ويبقى متاح فوراً للناس. علامة "موثّق" بنحطها يدوياً بعد ما الفريق يراجع البيانات.
        </p>
    </div>

    <form method="POST" action="{{ route('directory.store') }}" class="card-light p-5 space-y-4" enctype="multipart/form-data">
        @csrf

        {{-- Photo upload --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">صورة (اختياري)</label>
            <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer border border-ink-950/8 hover:border-coral-500/40 transition">
                <span class="w-12 h-12 rounded-xl pill-coral grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                    </svg>
                </span>
                <div class="flex-1">
                    <div class="text-sm font-bold text-ink-950">ارفع صورة للنشاط</div>
                    <div class="text-[11px] text-ink-500" data-photo-name>JPG / PNG / WEBP · حتى ٣ ميجا</div>
                </div>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="this.form.querySelector('[data-photo-name]').textContent = this.files[0]?.name || 'JPG / PNG / WEBP · حتى ٣ ميجا'">
            </label>
            @error('photo') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Sub-type pickers grouped by category --}}
        <div data-subtype-picker>
            <label class="text-xs font-bold text-ink-500 mb-2 block">نوع النشاط *</label>
            @foreach(\App\Models\Business::CATEGORIES as $catKey => $catMeta)
                <div class="mb-4">
                    <div class="text-[11px] font-bold text-ink-400 mb-2">
                        {{ $catMeta['label'] }}
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($subTypes as $key => $st)
                            @if($st['category'] === $catKey)
                                @php $isOther = str_ends_with($key, '_other'); @endphp
                                <label class="cursor-pointer">
                                    <input type="radio" name="sub_type" value="{{ $key }}" class="peer sr-only"
                                           {{ old('sub_type') === $key ? 'checked' : '' }} required
                                           data-is-other="{{ $isOther ? '1' : '0' }}">
                                    <span class="block px-3 py-1.5 rounded-full text-xs font-bold bg-cream-100 border border-ink-950/8 peer-checked:bg-coral-500 peer-checked:text-white peer-checked:border-coral-500 transition inline-flex items-center gap-1.5">
                                        <x-icon :name="$st['icon'] ?? 'briefcase'" class="w-3.5 h-3.5"/>
                                        {{ $st['label'] }}
                                    </span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
            @error('sub_type') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

            {{-- Custom free-text (only when "*_other" picked) --}}
            <div class="mt-2 {{ str_ends_with(old('sub_type', ''), '_other') ? '' : 'hidden' }}" data-custom-subtype-wrap>
                <label class="text-xs font-bold text-ink-500 mb-1 block">اكتب نوع نشاطك</label>
                <input type="text" name="custom_sub_type" maxlength="80"
                       value="{{ old('custom_sub_type') }}"
                       placeholder="مثلاً: محل ساعات، مدرّس عربي، مكتبة قرطاسية…"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                @error('custom_sub_type') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">اسم النشاط *</label>
            <input type="text" name="name" required minlength="3" maxlength="120"
                   value="{{ old('name') }}"
                   placeholder="مثلاً: عم محمد السباك"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            @error('name') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">المنطقة *</label>
            <select name="zone_id" required
                    class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                <option value="">اختار</option>
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" {{ old('zone_id', auth()->user()->zone_id) == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                @endforeach
            </select>
            @error('zone_id') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">العنوان (شارع · حي · معلم قريب)</label>
            <input type="text" name="address" maxlength="200" value="{{ old('address') }}"
                   placeholder="مثلاً: شارع الجيش — قسم أول بنها"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
        </div>

        <x-map-picker
            label="حدّد مكان النشاط على الخريطة (اختياري)"
            help="دوس على الخريطة في مكان نشاطك أو استخدم موقعك. **لو ما حددتش مكان، نشاطك مش هيظهر على خريطة بنها** — بس تقدر تعدّل دلوقتي أو في أي وقت."/>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">رقم تليفون</label>
                <input type="tel" name="phone" inputmode="numeric" maxlength="11" value="{{ old('phone') }}"
                       placeholder="010xxxxxxxx"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                @error('phone') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">رقم واتساب</label>
                <input type="tel" name="whatsapp" inputmode="numeric" maxlength="11" value="{{ old('whatsapp') }}"
                       placeholder="010xxxxxxxx"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                @error('whatsapp') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">المواعيد</label>
            <input type="text" name="hours" maxlength="100" value="{{ old('hours') }}"
                   placeholder="مثلاً: يومي ٩ص-١١م"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
        </div>

        <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer border border-ink-950/8 has-[:checked]:bg-mint-100/50 has-[:checked]:border-mint-500/40 transition">
            <input type="checkbox" name="is_24h" value="1" {{ old('is_24h') ? 'checked' : '' }} class="sr-only peer">
            <span class="w-12 h-7 rounded-full bg-ink-300 relative transition peer-checked:bg-mint-500">
                <span class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white shadow transition peer-checked:translate-x-[-1.25rem] rtl:peer-checked:translate-x-5"></span>
            </span>
            <span class="text-sm font-bold text-ink-950">شغّال ٢٤ ساعة</span>
        </label>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">وصف مختصر</label>
            <textarea name="description" rows="3" maxlength="1000"
                      placeholder="إيه اللي بتقدّمه؟ ايه اللي يميّزك؟"
                      class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-3">
            سجّل النشاط
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </button>

        <p class="text-ink-400 text-xs text-center pt-1">
            بياناتك بتتراجع. ممنوع نشاطات وهمية، تنطيط، أو spam.
        </p>
    </form>
</div>
@endsection
