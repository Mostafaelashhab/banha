@extends('admin.layouts.admin', ['title' => 'تنبيهات انقطاع · Admin'])

@section('content')
<h1 class="text-2xl font-black mb-1">تنبيهات الانقطاع</h1>
<p class="text-ink-500 text-sm mb-6">انشر انقطاع كهرباء أو مياه — هيظهر في الـfeed ويبعت push notification.</p>

<div class="grid lg:grid-cols-3 gap-4">
    {{-- Form --}}
    <div class="lg:col-span-2 a-card p-5">
        <form method="POST" action="{{ route('admin.outages.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="text-xs font-bold text-ink-500 mb-2 block">نوع الانقطاع *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="electricity" required class="peer sr-only" {{ old('type', 'electricity') === 'electricity' ? 'checked' : '' }}>
                        <div class="bg-cream-100 border border-ink-950/8 rounded-2xl p-4 text-center peer-checked:bg-honey-100 peer-checked:border-honey-500/40 transition">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7 mx-auto text-honey-700">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                            </svg>
                            <div class="text-sm font-extrabold mt-2">انقطاع كهرباء</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="water" required class="peer sr-only" {{ old('type') === 'water' ? 'checked' : '' }}>
                        <div class="bg-cream-100 border border-ink-950/8 rounded-2xl p-4 text-center peer-checked:bg-mint-100 peer-checked:border-mint-500/40 transition">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7 mx-auto text-mint-700">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                            </svg>
                            <div class="text-sm font-extrabold mt-2">انقطاع مياه</div>
                        </div>
                    </label>
                </div>
                @error('type') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">المنطقة المتأثرة</label>
                <select name="zone_id" class="select-styled w-full bg-cream-100 text-ink-950 rounded-2xl px-4 py-3 border border-ink-950/8">
                    <option value="">كل القليوبية</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" {{ old('zone_id') == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-ink-400 mt-1">سيب فاضي لو الانقطاع شامل، أو حدّد منطقة معينة.</p>
            </div>

            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">التفاصيل *</label>
                <textarea name="description" rows="3" maxlength="500" required
                          placeholder="مثلاً: قطع كهرباء في كفر طحلة شارع الجلاء بسبب صيانة"
                          class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('description') }}</textarea>
                @error('description') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1 block">يبدأ</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                           class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition" dir="ltr">
                </div>
                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1 block">ينتهي</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
                           class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition" dir="ltr">
                </div>
            </div>
            @error('ends_at') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

            <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer border border-ink-950/8 has-[:checked]:bg-mint-100/50 has-[:checked]:border-mint-500/40 transition">
                <input type="checkbox" name="send_push" value="1" checked class="sr-only peer">
                <span class="w-12 h-7 rounded-full bg-ink-300 relative transition peer-checked:bg-mint-500">
                    <span class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white shadow transition peer-checked:translate-x-[-1.25rem] rtl:peer-checked:translate-x-5"></span>
                </span>
                <span class="text-sm font-bold text-ink-950">ابعت push notification</span>
            </label>

            <button type="submit" class="btn-primary w-full justify-center !py-3">
                انشر التنبيه
                <x-icon name="bell" class="w-4 h-4"/>
            </button>
        </form>
    </div>

    {{-- Active outages list --}}
    <div class="a-card p-5">
        <h3 class="font-extrabold mb-3 inline-flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-coral-500 animate-pulse"></span>
            التنبيهات النشطة ({{ count($recent) }})
        </h3>
        @forelse($recent as $a)
            @php $meta = $a->typeMeta(); @endphp
            <div class="bg-cream-100 rounded-2xl p-3 mb-2">
                <div class="flex items-start gap-2 mb-1">
                    <span class="w-7 h-7 rounded-lg pill-{{ $meta['tone'] }} grid place-items-center shrink-0">
                        <x-icon :name="$meta['icon']" class="w-3.5 h-3.5"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-bold text-ink-500">{{ $meta['label'] }} · {{ $a->zone->name ?? 'كل القليوبية' }}</div>
                        <p class="text-xs text-ink-950 leading-relaxed mt-0.5 whitespace-pre-line">{{ $a->description }}</p>
                        <div class="text-[10px] text-ink-400 mt-1">{{ $a->created_at->diffForHumans() }} · ينتهي {{ $a->expires_at->diffForHumans() }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.outages.resolve', $a) }}"
                      data-confirm="إنهاء التنبيه؟" data-confirm-tone="danger">
                    @csrf
                    <button class="text-[10px] font-extrabold text-blush-500 hover:underline mt-1">إنهاء فوراً</button>
                </form>
            </div>
        @empty
            <p class="text-xs text-ink-400 text-center py-6">مفيش انقطاعات نشطة دلوقتي.</p>
        @endforelse
    </div>
</div>
@endsection
