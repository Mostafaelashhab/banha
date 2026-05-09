@extends('layouts.app', ['title' => 'ستوري جديدة'])

@section('content')
<div class="max-w-md mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('stories.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950">ستوري جديدة</h1>
        <span class="ms-auto text-xs font-bold text-ink-500">
            {{ ($alive ?? 0) }}/{{ ($max ?? 10) }}
        </span>
    </div>

    @if(($alive ?? 0) >= ($max ?? 10))
        <div class="card-light !shadow-none border-blush-500/30 bg-blush-100/50 p-4 mb-3 text-center">
            <p class="text-blush-500 font-bold text-sm">وصلت للحد الأقصى — استنى لما واحدة تخلص أو احذف واحدة.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('stories.store') }}" class="card-light p-5 space-y-4" enctype="multipart/form-data">
        @csrf

        <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-4 cursor-pointer border-2 border-dashed border-ink-950/20 hover:border-coral-500 transition">
            <span class="w-12 h-12 rounded-xl pill-coral grid place-items-center shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-6 h-6">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-bold text-ink-950" data-photo-name>اختار صورة</div>
                <div class="text-[10px] text-ink-500">JPG / PNG / WEBP · هتتمسح بعد ٢٤ ساعة</div>
            </div>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="hidden" required
                   onchange="this.parentElement.querySelector('[data-photo-name]').textContent = this.files[0]?.name || 'اختار صورة'">
        </label>
        @error('image') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

        <textarea name="caption" rows="3" maxlength="200" placeholder="ضيف كلمتين (اختياري)…"
                  class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('caption') }}</textarea>

        <button type="submit" class="btn-primary w-full justify-center !py-3" {{ ($alive ?? 0) >= ($max ?? 10) ? 'disabled' : '' }}>
            انشر الستوري
        </button>

        <p class="text-[10px] text-ink-400 text-center">حد أقصى {{ $max ?? 10 }} ستوريز نشطة في نفس الوقت.</p>
    </form>
</div>
@endsection
