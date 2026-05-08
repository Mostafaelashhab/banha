@extends('layouts.app', ['title' => 'بلّغ · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('alerts.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950">بلّغ عن حاجة</h1>
    </div>

    <form method="POST" action="{{ route('alerts.store') }}" class="card-light p-5 space-y-4">
        @csrf

        {{-- Type radio cards --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">نوع التنبيه *</label>
            <div class="grid grid-cols-3 gap-2">
                @foreach($types as $key => $meta)
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $key }}" class="peer sr-only" {{ old('type', 'traffic') === $key ? 'checked' : '' }} required>
                        <div class="flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-cream-100 border border-ink-950/8 peer-checked:bg-coral-500 peer-checked:text-white peer-checked:border-coral-500 transition">
                            <x-icon :name="$meta['icon']" class="w-5 h-5"/>
                            <span class="text-xs font-bold">{{ $meta['label'] }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('type') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">إيه اللي حصل؟ *</label>
            <textarea name="description" required rows="3" minlength="5" maxlength="280"
                      placeholder="اكتب وصف سريع وواضح…"
                      class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('description') }}</textarea>
            @error('description') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">المنطقة *</label>
            <select name="zone_id" required
                    class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" {{ auth()->user()->zone_id == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="card-light p-3 !shadow-none border-coral-500/20 bg-coral-50">
            <p class="text-xs text-ink-500 leading-relaxed">
                <b class="text-ink-950">ملاحظة:</b>
                التنبيهات بتنتهي بعد ٦ ساعات تلقائياً. لو ٣ بنهاوية أكّدوا، التنبيه يتوثّق ويفضل ١٢ ساعة.
                التبليغ الكاذب بيخفّض سمعتك.
            </p>
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-3">
            انشر التنبيه
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </button>
    </form>
</div>
@endsection
