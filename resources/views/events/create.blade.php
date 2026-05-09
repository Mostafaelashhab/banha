@extends('layouts.app', ['title' => 'حدث جديد · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('events.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950">حدث جديد</h1>
    </div>

    <form method="POST" action="{{ route('events.store') }}" class="card-light p-5 space-y-4" enctype="multipart/form-data">
        @csrf

        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">نوع الحدث</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach($kinds as $k => $meta)
                    <label class="cursor-pointer">
                        <input type="radio" name="kind" value="{{ $k }}" class="peer sr-only" {{ old('kind', 'community') === $k ? 'checked' : '' }} required>
                        <span class="block p-2.5 rounded-2xl bg-cream-100 border border-ink-950/8 peer-checked:bg-coral-500 peer-checked:text-white peer-checked:border-coral-500 transition text-xs font-bold text-center inline-flex flex-col items-center gap-1 w-full">
                            <x-icon :name="$meta['icon']" class="w-4 h-4"/>
                            {{ $meta['label'] }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">صورة (اختياري)</label>
            <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer border border-ink-950/8 hover:border-coral-500/40 transition">
                <span class="w-11 h-11 rounded-xl pill-coral grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-ink-950" data-photo-name>ارفع صورة الكوفر</div>
                </div>
                <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" class="hidden"
                       onchange="this.parentElement.querySelector('[data-photo-name]').textContent = this.files[0]?.name || 'ارفع صورة الكوفر'">
            </label>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">عنوان الحدث *</label>
            <input type="text" name="title" required minlength="3" maxlength="150" value="{{ old('title') }}"
                   placeholder="مثلاً: حفل تخرج كلية الهندسة"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            @error('title') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">يبتدي *</label>
                <input type="datetime-local" name="starts_at" required value="{{ old('starts_at') }}"
                       class="w-full bg-cream-100 rounded-2xl px-3 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm">
                @error('starts_at') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">ينتهي</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
                       class="w-full bg-cream-100 rounded-2xl px-3 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm">
            </div>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">المكان</label>
            <input type="text" name="location" maxlength="200" value="{{ old('location') }}"
                   placeholder="مثلاً: قاعة كلية التجارة - جامعة بنها"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">المنطقة</label>
            <select name="zone_id" class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                <option value="">اختار</option>
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" {{ old('zone_id', auth()->user()->zone_id) == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">رقم تواصل</label>
            <input type="tel" name="contact_phone" inputmode="numeric" maxlength="11" value="{{ old('contact_phone') }}"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">وصف</label>
            <textarea name="description" rows="4" maxlength="2000"
                      class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-3">انشر الحدث</button>
    </form>
</div>
@endsection
