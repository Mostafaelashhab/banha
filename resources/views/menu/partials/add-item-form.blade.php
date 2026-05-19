@php
    $L = $L ?? \App\Models\Business::menuLabels($business->category);
    $hasPrice         = ! empty($L['price_label']);
    $hasCapacity      = ! empty($L['capacity_label']);
    $hasItemFeatures  = \App\Models\Business::hasItemFeatures($business->category);
    $formId           = 'add-item-' . ($categoryId ?? 'loose');

    // Curated icon set for per-item features (hotels/tourist only).
    $featureIcons = ['wifi','snowflake','coffee','utensils','cup','tv','key','car','phone','bell','gear','sofa','bag','gift','heart','leaf','flame','dumbbell','paw','baby','gem','ticket','tag','map-pin','bolt','briefcase','tools','tooth','shirt'];
@endphp

<form method="POST"
      action="{{ route('menu.item.store', $business) }}"
      enctype="multipart/form-data"
      data-rich-item-form="{{ $formId }}"
      class="bg-white rounded-2xl border border-ink-950/8 shadow-sm overflow-hidden">
    @csrf
    <input type="hidden" name="category_id" value="{{ $categoryId ?? '' }}">
    @if($hasItemFeatures)
        <input type="hidden" name="features" data-features-json value="">
    @endif

    {{-- ── Section 1 · Basics ── --}}
    <div class="p-4 space-y-2.5 bg-coral-50/30">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-1 h-4 rounded-full bg-coral-500"></span>
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-coral-700">البيانات الأساسية</span>
        </div>

        <input type="text" name="name" required maxlength="120"
               placeholder="{{ $L['item_placeholder'] ?? 'الاسم *' }}"
               class="w-full bg-cream-50 rounded-xl px-4 py-3 text-ink-950 text-base font-bold placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white focus:ring-4 focus:ring-coral-500/10 transition">

        <div class="grid {{ $hasPrice && $hasCapacity ? 'grid-cols-2' : 'grid-cols-1' }} gap-2">
            @if($hasPrice)
                <div class="relative">
                    <input type="number" name="price" min="0" max="999999.99" step="0.5"
                           placeholder="{{ $L['price_label'] }}"
                           class="w-full bg-cream-50 rounded-xl ps-4 pe-10 py-2.5 text-ink-950 font-extrabold placeholder-ink-400 placeholder:font-normal outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white focus:ring-4 focus:ring-coral-500/10 transition text-sm">
                    <span class="absolute end-3 top-1/2 -translate-y-1/2 text-[10px] font-extrabold text-coral-600 pointer-events-none">ج.م</span>
                </div>
            @endif
            @if($hasCapacity)
                <div class="relative">
                    <input type="number" name="capacity" min="1" max="255" step="1"
                           placeholder="{{ $L['capacity_label'] }}"
                           class="w-full bg-cream-50 rounded-xl ps-4 pe-10 py-2.5 text-ink-950 font-extrabold placeholder-ink-400 placeholder:font-normal outline-0 border border-ink-950/8 focus:border-mint-500 focus:bg-white focus:ring-4 focus:ring-mint-500/10 transition text-sm">
                    <span class="absolute end-3 top-1/2 -translate-y-1/2 text-mint-600 pointer-events-none">
                        <x-icon name="user" class="w-3.5 h-3.5"/>
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Section 2 · Description ── --}}
    <div class="px-4 pb-3 border-t border-ink-950/5 pt-3">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-1 h-4 rounded-full bg-mint-500"></span>
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-mint-700">الوصف</span>
            <span class="text-[10px] font-bold text-ink-400">اختياري</span>
        </div>
        <textarea name="description" rows="3" maxlength="2000"
                  data-counter
                  placeholder="كل التفاصيل اللي عاوز العميل يعرفها — الحجم، الموقع، المميزات الخاصة…"
                  class="w-full bg-cream-50 rounded-xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-mint-500 focus:bg-white focus:ring-4 focus:ring-mint-500/10 transition text-sm resize-none leading-relaxed"></textarea>
        <div class="flex justify-end mt-1">
            <span class="text-[10px] text-ink-400 font-mono" data-counter-display>0 / 2000</span>
        </div>
    </div>

    {{-- ── Section 3 · Features (per-item, hotels/tourist only) ── --}}
    @if($hasItemFeatures)
        <div class="px-4 pb-3 border-t border-ink-950/5 pt-3" data-features-app>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-1 h-4 rounded-full bg-honey-500"></span>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-honey-700">مميزات {{ $L['item_label'] }}</span>
                <span class="text-[10px] font-bold text-ink-400">اختياري · لحد ١٢</span>
            </div>

            <div class="flex items-center gap-1.5 mb-2">
                <div class="relative shrink-0">
                    <button type="button" data-icon-toggle
                            class="w-10 h-10 rounded-xl bg-honey-50 hover:bg-honey-100 border border-honey-500/20 grid place-items-center text-honey-700 transition"
                            aria-label="اختر أيقونة">
                        <span data-current-icon class="inline-flex">
                            <x-icon name="tag" class="w-4 h-4"/>
                        </span>
                    </button>
                    <div data-icon-grid hidden
                         class="absolute z-30 top-full mt-1 start-0 w-[228px] bg-white rounded-2xl border border-ink-950/10 shadow-xl p-2 grid grid-cols-6 gap-1">
                        @foreach($featureIcons as $ic)
                            <button type="button"
                                    data-icon-pick="{{ $ic }}"
                                    class="w-8 h-8 rounded-lg hover:bg-honey-50 grid place-items-center text-ink-700 transition"
                                    aria-label="{{ $ic }}">
                                <x-icon name="{{ $ic }}" class="w-4 h-4"/>
                            </button>
                        @endforeach
                    </div>
                </div>
                <input type="text" data-feature-label maxlength="40"
                       placeholder="مثلاً: واي فاي / تكييف / إفطار"
                       class="flex-1 bg-cream-50 rounded-xl px-3 py-2.5 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-honey-500 focus:bg-white focus:ring-4 focus:ring-honey-500/10 transition text-sm">
                <button type="button" data-feature-add
                        class="px-4 py-2.5 rounded-xl bg-honey-500 hover:bg-honey-600 text-white text-xs font-extrabold transition shrink-0">
                    ضيف
                </button>
            </div>

            <div data-feature-chips class="flex flex-wrap gap-1.5"></div>
        </div>
    @endif

    {{-- ── Section 4 · Media ── --}}
    <div class="px-4 pb-3 border-t border-ink-950/5 pt-3">
        <div class="flex items-center gap-2 mb-2">
            <span class="w-1 h-4 rounded-full bg-blush-500"></span>
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-blush-600">الصور</span>
            <span class="text-[10px] font-bold text-ink-400">اختياري</span>
        </div>

        <div class="grid grid-cols-2 gap-2">
            {{-- Cover --}}
            <label class="group relative cursor-pointer rounded-xl bg-cream-50 border-2 border-dashed border-ink-950/10 hover:border-coral-500 hover:bg-white transition aspect-square overflow-hidden block">
                <div data-cover-empty class="absolute inset-0 grid place-items-center text-center px-2 pointer-events-none">
                    <div>
                        <span class="w-9 h-9 mx-auto mb-1 rounded-xl bg-coral-100 text-coral-600 grid place-items-center group-hover:bg-coral-500 group-hover:text-white transition">
                            <x-icon name="camera" class="w-4 h-4"/>
                        </span>
                        <div class="text-[11px] font-extrabold text-ink-950">صورة الغلاف</div>
                        <div class="text-[9px] text-ink-400 mt-0.5">الصورة الرئيسية</div>
                    </div>
                </div>
                <img data-cover-preview src="" alt="" class="absolute inset-0 w-full h-full object-cover hidden">
                <button type="button" data-cover-clear
                        class="absolute top-1 end-1 w-6 h-6 rounded-full bg-blush-500 hover:bg-blush-600 text-white text-xs font-black grid place-items-center hidden">×</button>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" data-cover-input>
            </label>

            {{-- Gallery --}}
            <label class="group relative cursor-pointer rounded-xl bg-cream-50 border-2 border-dashed border-ink-950/10 hover:border-mint-500 hover:bg-white transition aspect-square overflow-hidden block">
                <div class="absolute inset-0 grid place-items-center text-center px-2 pointer-events-none">
                    <div>
                        <span class="w-9 h-9 mx-auto mb-1 rounded-xl bg-mint-100 text-mint-700 grid place-items-center group-hover:bg-mint-500 group-hover:text-white transition">
                            <x-icon name="layers" class="w-4 h-4"/>
                        </span>
                        <div class="text-[11px] font-extrabold text-ink-950">
                            <span data-gallery-label>صور إضافية</span>
                        </div>
                        <div class="text-[9px] text-ink-400 mt-0.5">لحد ١٠ صور</div>
                    </div>
                </div>
                <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="hidden" data-gallery-input>
            </label>
        </div>

        {{-- Gallery thumbnails strip (shows after selecting) --}}
        <div data-gallery-strip class="hidden flex-wrap gap-1.5 mt-2"></div>
    </div>

    {{-- ── Submit ── --}}
    <div class="px-4 pb-4 pt-3 bg-coral-50/30 border-t border-ink-950/5">
        <x-button type="submit" size="lg" icon="check" block>احفظ {{ $L['item_label'] }}</x-button>
    </div>
</form>

@once
@push('scripts')
<script>
// ── Features chip-builder (per-item for hotels, business-level on its own form). ──
(function () {
    document.querySelectorAll('[data-features-app]').forEach(function (root) {
        const form        = root.closest('form');
        const hidden      = form.querySelector('[data-features-json]');
        const chipsEl     = root.querySelector('[data-feature-chips]');
        const labelInput  = root.querySelector('[data-feature-label]');
        const addBtn      = root.querySelector('[data-feature-add]');
        const iconToggle  = root.querySelector('[data-icon-toggle]');
        const iconGrid    = root.querySelector('[data-icon-grid]');
        const currentIcon = root.querySelector('[data-current-icon]');

        let selectedIcon = 'tag';
        let features = [];

        // Pre-fill from existing JSON (used for business-features-form on first paint).
        try {
            const seed = hidden.value ? JSON.parse(hidden.value) : null;
            if (Array.isArray(seed)) features = seed.filter(f => f && f.label);
        } catch (e) {}

        function renderChips() {
            chipsEl.innerHTML = '';
            features.forEach((f, i) => {
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center gap-1.5 bg-white shadow-sm text-ink-800 text-[11px] font-bold rounded-full ps-2 pe-1 py-1 border border-ink-950/8 transition hover:shadow';
                const iconHost = document.createElement('span');
                iconHost.className = 'w-3.5 h-3.5 inline-flex text-coral-600';
                iconHost.innerHTML = iconSvg(f.icon || 'tag');
                chip.appendChild(iconHost);
                const txt = document.createElement('span');
                txt.textContent = f.label;
                chip.appendChild(txt);
                const x = document.createElement('button');
                x.type = 'button';
                x.className = 'w-4 h-4 rounded-full bg-ink-950/10 hover:bg-blush-500 hover:text-white text-ink-700 text-[10px] grid place-items-center transition';
                x.textContent = '×';
                x.addEventListener('click', () => { features.splice(i, 1); renderChips(); });
                chip.appendChild(x);
                chipsEl.appendChild(chip);
            });
            hidden.value = features.length ? JSON.stringify(features) : '';
        }

        function iconSvg(name) {
            const src = root.querySelector('[data-icon-pick="' + name + '"] svg');
            return src ? src.outerHTML : '';
        }

        addBtn.addEventListener('click', () => {
            const label = (labelInput.value || '').trim();
            if (!label) { labelInput.focus(); return; }
            if (features.length >= 12) return;
            features.push({ icon: selectedIcon, label: label.slice(0, 40) });
            labelInput.value = '';
            renderChips();
            labelInput.focus();
        });

        labelInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); addBtn.click(); }
        });

        iconToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            iconGrid.hidden = !iconGrid.hidden;
        });

        iconGrid.querySelectorAll('[data-icon-pick]').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedIcon = btn.dataset.iconPick;
                const sv = btn.querySelector('svg');
                if (sv) currentIcon.innerHTML = sv.outerHTML;
                iconGrid.hidden = true;
                labelInput.focus();
            });
        });

        document.addEventListener('click', (e) => {
            if (!iconGrid.hidden && !root.contains(e.target)) iconGrid.hidden = true;
        });

        renderChips();
    });
})();

// ── Description char counter ──
(function () {
    document.querySelectorAll('[data-counter]').forEach(ta => {
        const display = ta.closest('div').querySelector('[data-counter-display]');
        if (!display) return;
        const max = ta.maxLength || 2000;
        const update = () => { display.textContent = ta.value.length + ' / ' + max; };
        ta.addEventListener('input', update);
        update();
    });
})();

// ── Cover photo preview + clear ──
(function () {
    document.querySelectorAll('[data-cover-input]').forEach(input => {
        const wrap    = input.closest('label');
        const preview = wrap.querySelector('[data-cover-preview]');
        const empty   = wrap.querySelector('[data-cover-empty]');
        const clear   = wrap.querySelector('[data-cover-clear]');

        input.addEventListener('change', () => {
            const f = input.files && input.files[0];
            if (!f) return;
            const reader = new FileReader();
            reader.onload = () => {
                preview.src = reader.result;
                preview.classList.remove('hidden');
                empty.classList.add('hidden');
                clear.classList.remove('hidden');
            };
            reader.readAsDataURL(f);
        });

        clear.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            input.value = '';
            preview.src = '';
            preview.classList.add('hidden');
            empty.classList.remove('hidden');
            clear.classList.add('hidden');
        });
    });
})();

// ── Gallery multi-photo thumbnails strip ──
(function () {
    document.querySelectorAll('[data-gallery-input]').forEach(input => {
        const form  = input.closest('form');
        const strip = form.querySelector('[data-gallery-strip]');
        const label = form.querySelector('[data-gallery-label]');
        // We keep an in-memory File list so the × button can drop individual files.
        let files = [];

        function syncInput() {
            const dt = new DataTransfer();
            files.forEach(f => dt.items.add(f));
            input.files = dt.files;
            label.textContent = files.length ? (files.length + ' صورة') : 'صور إضافية';
            renderStrip();
        }

        function renderStrip() {
            strip.innerHTML = '';
            if (!files.length) { strip.classList.add('hidden'); strip.classList.remove('flex'); return; }
            strip.classList.remove('hidden'); strip.classList.add('flex');
            files.forEach((f, i) => {
                const tile = document.createElement('div');
                tile.className = 'relative w-14 h-14 rounded-lg overflow-hidden ring-1 ring-ink-950/8 shrink-0';
                const img = document.createElement('img');
                img.className = 'w-full h-full object-cover';
                const reader = new FileReader();
                reader.onload = () => { img.src = reader.result; };
                reader.readAsDataURL(f);
                tile.appendChild(img);
                const x = document.createElement('button');
                x.type = 'button';
                x.className = 'absolute top-0.5 end-0.5 w-4 h-4 rounded-full bg-blush-500 hover:bg-blush-600 text-white text-[10px] font-black grid place-items-center shadow';
                x.textContent = '×';
                x.addEventListener('click', () => { files.splice(i, 1); syncInput(); });
                tile.appendChild(x);
                strip.appendChild(tile);
            });
        }

        input.addEventListener('change', () => {
            files = files.concat(Array.from(input.files || [])).slice(0, 10);
            syncInput();
        });
    });
})();
</script>
@endpush
@endonce
