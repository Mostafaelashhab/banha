@extends('layouts.app', ['title' => 'بوست جديد · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ url()->previous() }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950">بوست جديد</h1>
    </div>

    <form method="POST" action="{{ route('posts.store') }}" class="card-light p-5 space-y-4" enctype="multipart/form-data">
        @csrf

        {{-- Categories pills --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">نوع البوست</label>
            <div class="flex flex-wrap gap-2">
                @foreach($categories as $key => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="category" value="{{ $key }}" class="peer sr-only" {{ old('category', 'question') === $key ? 'checked' : '' }} required>
                        <span class="block px-3 py-1.5 rounded-full text-xs font-bold bg-cream-100 border border-ink-950/8 peer-checked:bg-coral-500 peer-checked:text-white peer-checked:border-coral-500 transition">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>
            @error('category') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Title --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">عنوان (اختياري)</label>
            <input type="text" name="title" maxlength="180" value="{{ old('title') }}"
                   placeholder="عنوان قصير وجذّاب…"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
        </div>

        {{-- Body --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">محتوى البوست *</label>
            <textarea name="body" required rows="6" minlength="3" maxlength="2000"
                      placeholder="اكتب اللي عايز تقوله… ممكن تستخدم #هاشتاج"
                      class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('body') }}</textarea>
            @error('body') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Photo upload (optional) --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">صورة (اختياري)</label>
            <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer border border-ink-950/8 hover:border-coral-500/40 transition">
                <span class="w-11 h-11 rounded-xl pill-coral grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-ink-950" data-photo-name>ارفع صورة</div>
                    <div class="text-[10px] text-ink-500">JPG / PNG / WEBP · هتتضغط لأقل من ٣٠٠ ك.ب تلقائي</div>
                </div>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="hidden"
                       onchange="this.parentElement.querySelector('[data-photo-name]').textContent = this.files[0]?.name || 'ارفع صورة'">
            </label>
            @error('image') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Poll (optional, collapsed by default) --}}
        <details class="bg-cream-100 rounded-2xl border border-ink-950/8 overflow-hidden">
            <summary class="px-4 py-3 cursor-pointer text-sm font-bold text-ink-950 inline-flex items-center gap-2 list-none">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                    <line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>
                </svg>
                ضيف تصويت (اختياري)
            </summary>
            <div class="p-4 pt-0 space-y-2">
                <input type="text" name="poll_question" maxlength="200" value="{{ old('poll_question') }}"
                       placeholder="السؤال — مثلاً: ايه أحسن مطعم؟"
                       class="w-full bg-white rounded-xl px-3 py-2 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm">
                @for($i = 0; $i < 4; $i++)
                    <input type="text" name="poll_options[]" maxlength="80"
                           value="{{ old('poll_options.'.$i) }}"
                           placeholder="اختيار {{ $i + 1 }}{{ $i < 2 ? '' : ' (اختياري)' }}"
                           class="w-full bg-white rounded-xl px-3 py-2 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm">
                @endfor
                <p class="text-[10px] text-ink-400">التصويت بيقفل بعد ٧ أيام تلقائي.</p>
            </div>
        </details>

        {{-- Zone --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">المنطقة</label>
            <select name="zone_id"
                    class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}" {{ old('zone_id', auth()->user()->zone_id) == $zone->id ? 'selected' : '' }}>
                        {{ $zone->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Anonymous toggle --}}
        <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-4 cursor-pointer border border-ink-950/8 has-[:checked]:bg-coral-100 has-[:checked]:border-coral-500/40 transition">
            <input type="checkbox" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }} class="sr-only peer">
            <span class="w-12 h-7 rounded-full bg-ink-300 relative transition peer-checked:bg-coral-500">
                <span class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white shadow transition peer-checked:translate-x-[-1.25rem] rtl:peer-checked:translate-x-5"></span>
            </span>
            <span class="flex-1">
                <span class="block font-bold text-ink-950 inline-flex items-center gap-2">
                    <x-icon name="mask" class="w-4 h-4 text-coral-600"/> انشر بشكل مجهول
                </span>
                <span class="block text-xs text-ink-500 mt-0.5">يوزر نيمك مش هيظهر، بس الأدمن مع الـ AI بيراقبوا للتنمر/الإشاعات.</span>
            </span>
        </label>

        <button type="submit" class="btn-primary w-full justify-center !py-3">
            انشر البوست
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </button>
    </form>
</div>
@endsection
