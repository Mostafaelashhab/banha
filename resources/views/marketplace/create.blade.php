@extends('layouts.app', ['title' => 'إعلان جديد · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('marketplace.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950">إعلان جديد</h1>
    </div>

    <form method="POST" action="{{ route('marketplace.store') }}" class="card-light p-5 space-y-4" enctype="multipart/form-data">
        @csrf

        {{-- Kind --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">نوع الإعلان</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach($kinds as $k => $meta)
                    <label class="cursor-pointer">
                        <input type="radio" name="kind" value="{{ $k }}" class="peer sr-only" {{ old('kind', 'sale') === $k ? 'checked' : '' }} required>
                        <span class="block p-3 rounded-2xl bg-cream-100 border border-ink-950/8 peer-checked:bg-coral-500 peer-checked:text-white peer-checked:border-coral-500 transition text-sm font-bold inline-flex items-center gap-2 w-full">
                            <x-icon :name="$meta['icon']" class="w-4 h-4"/>
                            {{ $meta['label'] }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Category --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">القسم *</label>
            <div class="flex flex-wrap gap-2">
                @foreach($categories as $key => $cm)
                    <label class="cursor-pointer">
                        <input type="radio" name="category" value="{{ $key }}" class="peer sr-only" {{ old('category') === $key ? 'checked' : '' }} required>
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-cream-100 border border-ink-950/8 peer-checked:bg-coral-500 peer-checked:text-white peer-checked:border-coral-500 transition inline-flex items-center gap-1.5">
                            <x-icon :name="$cm['icon']" class="w-3.5 h-3.5"/>
                            {{ $cm['label'] }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Photo --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">صورة (اختياري — هتتضغط تلقائي)</label>
            <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer border border-ink-950/8 hover:border-coral-500/40 transition">
                <span class="w-12 h-12 rounded-xl pill-coral grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-ink-950" data-photo-name>ارفع صورة</div>
                    <div class="text-[10px] text-ink-500">JPG / PNG / WEBP</div>
                </div>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden"
                       onchange="this.parentElement.querySelector('[data-photo-name]').textContent = this.files[0]?.name || 'ارفع صورة'">
            </label>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">عنوان الإعلان *</label>
            <input type="text" name="title" required minlength="3" maxlength="120" value="{{ old('title') }}"
                   placeholder="مثلاً: لاب توب HP i7 جيل ١٢"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            @error('title') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">السعر (بالجنيه)</label>
            <input type="number" name="price" min="0" max="99999999" value="{{ old('price') }}"
                   placeholder="مثلاً: 12000 — سيبه فاضي لو بسعر مفاوض"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            <label class="flex items-center gap-2 mt-2 text-xs font-bold text-ink-500 cursor-pointer">
                <input type="checkbox" name="negotiable" value="1" {{ old('negotiable', true) ? 'checked' : '' }} class="accent-coral-500">
                قابل للمفاوضة
            </label>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">المنطقة</label>
            <select name="zone_id" class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" {{ old('zone_id', auth()->user()->zone_id) == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">وصف</label>
            <textarea name="description" rows="4" maxlength="2000" placeholder="حالته، تفاصيل، ضمان…"
                      class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">رقم تليفون</label>
                <input type="tel" name="contact_phone" inputmode="numeric" maxlength="11" value="{{ old('contact_phone', auth()->user()->phone) }}"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            </div>
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">رقم واتساب</label>
                <input type="tel" name="contact_whatsapp" inputmode="numeric" maxlength="11" value="{{ old('contact_whatsapp') }}"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            </div>
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-3">انشر الإعلان</button>
        <p class="text-ink-400 text-xs text-center pt-1">الإعلان بيتم إغلاقه تلقائي بعد ٦٠ يوم.</p>
    </form>
</div>
@endsection
