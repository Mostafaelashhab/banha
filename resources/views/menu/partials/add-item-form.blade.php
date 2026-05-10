@php
    $L = $L ?? \App\Models\Business::menuLabels($business->category);
    $hasPrice = ! empty($L['price_label']);
@endphp

<form method="POST" action="{{ route('menu.item.store', $business) }}" enctype="multipart/form-data" class="space-y-2 bg-cream-100/70 rounded-xl p-3 border border-ink-950/8">
    @csrf
    <input type="hidden" name="category_id" value="{{ $categoryId ?? '' }}">

    <div class="grid {{ $hasPrice ? 'grid-cols-3' : 'grid-cols-1' }} gap-2">
        <input type="text" name="name" required maxlength="120"
               placeholder="{{ $L['item_placeholder'] ?? 'الاسم *' }}"
               class="{{ $hasPrice ? 'col-span-2' : '' }} bg-white rounded-lg px-3 py-2.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm">
        @if($hasPrice)
            <div class="relative">
                <input type="number" name="price" min="0" max="999999.99" step="0.5"
                       placeholder="{{ $L['price_label'] }}"
                       class="w-full bg-white rounded-lg ps-3 pe-7 py-2.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm">
                <span class="absolute end-2.5 top-1/2 -translate-y-1/2 text-[10px] font-bold text-ink-400 pointer-events-none">ج.م</span>
            </div>
        @endif
    </div>

    <textarea name="description" rows="2" maxlength="500" placeholder="وصف بسيط (اختياري)"
              class="w-full bg-white rounded-lg px-3 py-2.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm resize-none"></textarea>

    <div class="flex items-center gap-2">
        <label class="flex-1 cursor-pointer bg-white rounded-lg px-3 py-2.5 text-xs text-ink-500 border border-ink-950/8 hover:border-coral-500 transition truncate inline-flex items-center gap-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 shrink-0 text-ink-400">
                <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
            </svg>
            <span class="truncate" data-photo-name>صورة (اختياري)</span>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden"
                   onchange="this.parentElement.querySelector('[data-photo-name]').textContent = this.files[0]?.name || 'صورة (اختياري)'">
        </label>
        <button class="bg-coral-500 hover:bg-coral-600 text-white font-extrabold rounded-lg px-4 py-2.5 text-xs inline-flex items-center gap-1 transition shrink-0">
            <x-icon name="check" class="w-3.5 h-3.5"/>
            حفظ
        </button>
    </div>
</form>
