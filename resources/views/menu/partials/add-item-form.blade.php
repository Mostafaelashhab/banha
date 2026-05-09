<details>
    <summary class="text-xs font-bold text-coral-600 cursor-pointer hover:underline list-none">+ أضف صنف</summary>
    <form method="POST" action="{{ route('menu.item.store', $business) }}" enctype="multipart/form-data" class="mt-3 space-y-2 bg-cream-100 rounded-xl p-3">
        @csrf
        <input type="hidden" name="category_id" value="{{ $categoryId ?? '' }}">

        <div class="grid grid-cols-3 gap-2">
            <input type="text" name="name" required maxlength="120" placeholder="اسم الصنف *"
                   class="col-span-2 bg-white rounded-lg px-3 py-2 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm">
            <input type="number" name="price" min="0" max="99999.99" step="0.5" placeholder="السعر"
                   class="bg-white rounded-lg px-3 py-2 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm">
        </div>

        <textarea name="description" rows="2" maxlength="500" placeholder="وصف بسيط (اختياري)"
                  class="w-full bg-white rounded-lg px-3 py-2 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm resize-none"></textarea>

        <div class="flex items-center gap-2">
            <label class="flex-1 cursor-pointer bg-white rounded-lg px-3 py-2 text-xs text-ink-500 border border-ink-950/8 hover:border-coral-500 transition truncate">
                <span data-photo-name>📷 صورة (اختياري)</span>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden"
                       onchange="this.parentElement.querySelector('[data-photo-name]').textContent = this.files[0]?.name || '📷 صورة (اختياري)'">
            </label>
            <button class="btn-primary !py-2 !px-4 text-xs">حفظ</button>
        </div>
    </form>
</details>
