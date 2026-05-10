@extends('layouts.app', ['title' => 'سجّل نشاطك · بنهاوي'])

@push('head')
<style>
    /* Step transitions */
    [data-step] { display: none; }
    [data-step].is-active { display: block; animation: stepFadeIn .35s cubic-bezier(.2,.9,.3,1); }
    @keyframes stepFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }

    /* Category picker cards */
    .cat-card {
        background: #fff;
        border: 2px solid rgba(11,11,12,.06);
        border-radius: 22px;
        padding: 14px 10px;
        text-align: center;
        cursor: pointer;
        transition: transform .15s, border-color .15s, box-shadow .15s;
        position: relative;
        overflow: hidden;
    }
    .cat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px -8px rgba(11,11,12,.18); }
    .cat-card:active { transform: scale(.97); }
    .cat-card.is-selected {
        border-color: var(--cat-color);
        background: linear-gradient(135deg, var(--cat-color), color-mix(in srgb, var(--cat-color) 85%, white));
        color: #fff;
        box-shadow: 0 10px 24px -8px var(--cat-color);
    }
    .cat-card.is-selected .cat-icon { background: rgba(255,255,255,.2); color: #fff; }
    .cat-icon {
        width: 42px; height: 42px; margin: 0 auto 8px;
        border-radius: 14px;
        display: grid; place-items: center;
        transition: all .2s;
    }
    .cat-label { font-size: 11px; font-weight: 800; line-height: 1.2; }

    /* Sub-type chips */
    .sub-chip {
        background: #fff;
        border: 1.5px solid rgba(11,11,12,.08);
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 700;
        color: #0B0B0C;
        cursor: pointer;
        display: inline-flex; align-items: center; gap: 5px;
        transition: all .15s;
    }
    .sub-chip:hover { border-color: var(--cat-color); }
    .sub-chip.is-selected {
        background: var(--cat-color);
        color: #fff;
        border-color: var(--cat-color);
        box-shadow: 0 4px 12px -2px var(--cat-color);
    }

    /* Selected type breadcrumb (shown at top once category picked) */
    .type-pill {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--cat-color);
        color: #fff;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
    }
    .type-pill .change {
        margin-inline-start: 4px;
        background: rgba(255,255,255,.25);
        border-radius: 999px;
        padding: 2px 8px;
        font-size: 10px;
        cursor: pointer;
    }

    /* Step counter */
    .step-num {
        width: 26px; height: 26px;
        border-radius: 50%;
        background: var(--cat-color, #FF7A4D);
        color: #fff;
        display: grid; place-items: center;
        font-weight: 900;
        font-size: 12px;
        flex-shrink: 0;
    }

    /* Sticky submit */
    .sticky-submit {
        position: sticky;
        bottom: calc(6rem + env(safe-area-inset-bottom));
        z-index: 30;
        backdrop-filter: blur(8px);
    }
</style>
<script>
    window.__BIZ_SUB_TO_CAT__ = @json(collect(\App\Models\Business::SUB_TYPES)->map(fn($s) => $s['category']));
</script>
@endpush

@section('content')
@php
    $oldSub = old('sub_type');
    $oldCat = $oldSub ? (\App\Models\Business::SUB_TYPES[$oldSub]['category'] ?? null) : null;
@endphp

<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950 hover:bg-cream-100 transition">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-black text-ink-950">سجّل نشاطك</h1>
            <p class="text-[11px] text-ink-500">دقيقتين · مجاناً · يظهر فوراً للناس</p>
        </div>
    </div>

    <form method="POST" action="{{ route('directory.store') }}" enctype="multipart/form-data"
          class="space-y-4" data-create-form id="create-form">
        @csrf
        <input type="hidden" name="sub_type" value="{{ $oldSub }}" data-sub-type-input required>
        <input type="hidden" name="category" value="{{ $oldCat }}" data-category-input>

        {{-- ──── STEP 1: Category picker ──── --}}
        <section data-step="category" class="{{ $oldCat ? '' : 'is-active' }} card-light p-5">
            <div class="flex items-center gap-3 mb-4">
                <span class="step-num" style="--cat-color: #FF7A4D">١</span>
                <div>
                    <h2 class="text-sm font-extrabold text-ink-950">اختار نوع نشاطك</h2>
                    <p class="text-[11px] text-ink-500">دوس على الفئة، بنوريك بعدها أنواع أدق.</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2.5">
                @foreach(\App\Models\Business::CATEGORIES as $catKey => $catMeta)
                    <button type="button" class="cat-card" data-pick-cat="{{ $catKey }}"
                            style="--cat-color: {{ $catMeta['color'] }}">
                        <span class="cat-icon" style="background: {{ $catMeta['color'] }}1a; color: {{ $catMeta['color'] }};">
                            <x-icon :name="$catMeta['icon'] ?? 'bag'" class="w-5 h-5"/>
                        </span>
                        <span class="cat-label">{{ $catMeta['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- ──── STEP 2+: Form (after category picked) ──── --}}
        <section data-step="details" class="{{ $oldCat ? 'is-active' : '' }} space-y-4">

            {{-- Type breadcrumb + change link --}}
            <div class="card-light p-3 flex items-center gap-2" data-type-breadcrumb style="--cat-color: {{ \App\Models\Business::CATEGORIES[$oldCat]['color'] ?? '#FF7A4D' }};">
                <span class="text-xs font-bold text-ink-500">نوع النشاط:</span>
                <span class="type-pill" data-bcrumb-pill>
                    <x-icon :name="\App\Models\Business::CATEGORIES[$oldCat]['icon'] ?? 'bag'" class="w-3 h-3" data-bcrumb-icon/>
                    <span data-bcrumb-label>{{ \App\Models\Business::CATEGORIES[$oldCat]['label'] ?? '' }}</span>
                    @if($oldSub && isset(\App\Models\Business::SUB_TYPES[$oldSub]))
                        <span class="opacity-80">· {{ \App\Models\Business::SUB_TYPES[$oldSub]['label'] }}</span>
                    @endif
                    <button type="button" class="change" data-back-to-cat>غيّر</button>
                </span>
            </div>

            {{-- Sub-type picker (chips for picked category) --}}
            <div class="card-light p-5" data-subtype-wrap>
                <div class="flex items-center gap-3 mb-3">
                    <span class="step-num">٢</span>
                    <h2 class="text-sm font-extrabold text-ink-950">حدّد التخصص</h2>
                </div>

                @foreach(\App\Models\Business::CATEGORIES as $catKey => $catMeta)
                    <div data-cat-subs="{{ $catKey }}" class="{{ $catKey === $oldCat ? '' : 'hidden' }} flex flex-wrap gap-2"
                         style="--cat-color: {{ $catMeta['color'] }};">
                        @foreach(\App\Models\Business::SUB_TYPES as $key => $st)
                            @if($st['category'] === $catKey)
                                @php $isOther = str_ends_with($key, '_other'); @endphp
                                <button type="button" class="sub-chip {{ $oldSub === $key ? 'is-selected' : '' }}"
                                        data-pick-sub="{{ $key }}"
                                        data-is-other="{{ $isOther ? '1' : '0' }}">
                                    <x-icon :name="$st['icon'] ?? 'briefcase'" class="w-3.5 h-3.5"/>
                                    {{ $st['label'] }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endforeach

                {{-- Custom free text (only shown when "_other" picked) --}}
                <div class="mt-3 {{ $oldSub && str_ends_with($oldSub, '_other') ? '' : 'hidden' }}" data-custom-subtype-wrap>
                    <label class="text-xs font-bold text-ink-500 mb-1 block">اكتب نوع نشاطك بالظبط</label>
                    <input type="text" name="custom_sub_type" maxlength="80"
                           value="{{ old('custom_sub_type') }}"
                           placeholder="مثلاً: محل ساعات، مدرّس عربي…"
                           class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition text-sm">
                    @error('custom_sub_type') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @error('sub_type') <p class="text-blush-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Basic info --}}
            <div class="card-light p-5 space-y-4">
                <div class="flex items-center gap-3 mb-1">
                    <span class="step-num">٣</span>
                    <h2 class="text-sm font-extrabold text-ink-950">البيانات الأساسية</h2>
                </div>

                {{-- Photo --}}
                <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer border border-ink-950/8 hover:border-coral-500/40 transition">
                    <span class="w-12 h-12 rounded-xl pill-coral grid place-items-center shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-ink-950" data-photo-label>صورة (اختياري)</div>
                        <div class="text-[11px] text-ink-500" data-photo-name>JPG / PNG / WEBP · حتى ٣ ميجا</div>
                    </div>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden"
                           onchange="this.parentElement.querySelector('[data-photo-name]').textContent = this.files[0]?.name || 'JPG / PNG / WEBP · حتى ٣ ميجا'">
                </label>
                @error('photo') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1.5 block">اسم النشاط *</label>
                    <input type="text" name="name" required minlength="3" maxlength="120"
                           value="{{ old('name') }}"
                           placeholder="مثلاً: مطعم النيل، فندق العاصمة، عم محمد السباك"
                           class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                    @error('name') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1.5 block">المنطقة *</label>
                    <select name="zone_id" required
                            class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                        <option value="">اختار</option>
                        @foreach($zones as $z)
                            <option value="{{ $z->id }}" {{ old('zone_id', auth()->user()->zone_id) == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                        @endforeach
                    </select>
                    @error('zone_id') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1.5 block">العنوان</label>
                    <input type="text" name="address" maxlength="200" value="{{ old('address') }}"
                           placeholder="شارع · حي · معلم قريب"
                           class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-ink-500 mb-1.5 block">رقم تليفون</label>
                        <input type="tel" name="phone" inputmode="numeric" maxlength="11" dir="ltr"
                               value="{{ old('phone') }}"
                               placeholder="010xxxxxxxx"
                               class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                        @error('phone') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-ink-500 mb-1.5 block">رقم واتساب</label>
                        <input type="tel" name="whatsapp" inputmode="numeric" maxlength="11" dir="ltr"
                               value="{{ old('whatsapp') }}"
                               placeholder="010xxxxxxxx"
                               class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                        @error('whatsapp') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1.5 block inline-flex items-center gap-1.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-3.5 h-3.5 text-coral-500">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        خط ساخن (Hotline)
                    </label>
                    <input type="tel" name="hotline" inputmode="tel" maxlength="20" dir="ltr"
                           value="{{ old('hotline') }}"
                           placeholder="مثلاً: 19999 / 16789 / 0800-XXX-XXX"
                           class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                    <p class="text-[10px] text-ink-400 mt-1">للأماكن اللي شغّالة بـ خط ساخن بدل موبايل عادي (سلاسل، بنوك، مستشفيات…)</p>
                    @error('hotline') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <x-hours-picker :hours-text="old('hours')"/>

                <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer border border-ink-950/8 has-[:checked]:bg-mint-100/50 has-[:checked]:border-mint-500/40 transition">
                    <input type="checkbox" name="is_24h" value="1" {{ old('is_24h') ? 'checked' : '' }} class="sr-only peer">
                    <span class="w-12 h-7 rounded-full bg-ink-300 relative transition peer-checked:bg-mint-500">
                        <span class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white shadow transition peer-checked:translate-x-[-1.25rem] rtl:peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-bold text-ink-950">شغّال ٢٤ ساعة</span>
                </label>

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1.5 block">وصف مختصر</label>
                    <textarea name="description" rows="3" maxlength="1000"
                              placeholder="إيه اللي بتقدّمه؟ ايه اللي يميّزك؟"
                              class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition resize-none">{{ old('description') }}</textarea>
                </div>
            </div>

            {{-- Map picker (location) --}}
            <div class="card-light p-5">
                <div class="flex items-center gap-3 mb-3">
                    <span class="step-num">٤</span>
                    <h2 class="text-sm font-extrabold text-ink-950">المكان على الخريطة</h2>
                </div>
                <x-map-picker
                    label=""
                    help="**لو ما حددتش مكان، نشاطك مش هيظهر على خريطة بنها** — تقدر تعدّل في أي وقت."/>
            </div>

            {{-- Type-specific extras --}}
            <div class="card-light p-5" data-extras-section>
                <div class="flex items-center gap-3 mb-3">
                    <span class="step-num">٥</span>
                    <div>
                        <h2 class="text-sm font-extrabold text-ink-950" data-extras-title>تفاصيل خاصة بنوع النشاط</h2>
                        <p class="text-[11px] text-ink-500">الحقول هنا بتتغيّر حسب النوع اللي اخترته.</p>
                    </div>
                </div>

                <x-business-extras :sub-type="$oldSub"/>
            </div>

            {{-- Admin-only: create as "unowned" so the real owner can claim it later --}}
            @if(auth()->user()->is_admin)
                <div class="card-light p-4 border-coral-500/20 bg-coral-50/40">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="unowned" value="1" {{ old('unowned') ? 'checked' : '' }} class="sr-only peer">
                        <span class="w-12 h-7 rounded-full bg-ink-300 relative transition peer-checked:bg-coral-500 shrink-0 mt-0.5">
                            <span class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white shadow transition peer-checked:translate-x-[-1.25rem] rtl:peer-checked:translate-x-5"></span>
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-1.5">
                                <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded-full bg-coral-500 text-white">ADMIN</span>
                                نشاط بدون صاحب
                            </div>
                            <p class="text-[11px] text-ink-500 mt-0.5 leading-relaxed">
                                فعّل ده لو بتـcurate مكان عام (جامعة، جامع، حديقة…). صاحب النشاط الفعلي يقدر يـclaim النشاط بعدين عبر "ده نشاطي" + OTP على رقم التليفون.
                            </p>
                        </div>
                    </label>
                </div>
            @endif

            {{-- Sticky submit --}}
            <div class="sticky-submit">
                <button type="submit" class="btn-primary w-full justify-center !py-3.5 text-sm shadow-xl shadow-coral-500/30">
                    سجّل النشاط دلوقتي
                    <x-icon name="arrow-left" class="w-4 h-4"/>
                </button>
                <p class="text-[10px] text-ink-400 text-center mt-2">
                    بياناتك بتتراجع. ممنوع نشاطات وهمية أو spam.
                </p>
            </div>
        </section>
    </form>
</div>

<script>
    (function () {
        const form         = document.getElementById('create-form');
        const subInput     = form.querySelector('[data-sub-type-input]');
        const catInput     = form.querySelector('[data-category-input]');
        const stepCategory = form.querySelector('[data-step="category"]');
        const stepDetails  = form.querySelector('[data-step="details"]');
        const subWrap      = form.querySelector('[data-subtype-wrap]');
        const customWrap   = form.querySelector('[data-custom-subtype-wrap]');
        const breadcrumb   = form.querySelector('[data-type-breadcrumb]');
        const bcrumbLabel  = form.querySelector('[data-bcrumb-label]');
        const subToCat     = window.__BIZ_SUB_TO_CAT__ || {};

        const CATEGORIES = @json(\App\Models\Business::CATEGORIES);
        const SUB_TYPES  = @json(\App\Models\Business::SUB_TYPES);

        function pickCategory(catKey) {
            const meta = CATEGORIES[catKey];
            if (!meta) return;
            catInput.value = catKey;

            // Update breadcrumb pill color
            breadcrumb.style.setProperty('--cat-color', meta.color);
            bcrumbLabel.textContent = meta.label;

            // Show this category's sub-types only
            subWrap.querySelectorAll('[data-cat-subs]').forEach(div => {
                div.classList.toggle('hidden', div.dataset.catSubs !== catKey);
            });

            // Mark selected category card
            stepCategory.querySelectorAll('[data-pick-cat]').forEach(c => {
                c.classList.toggle('is-selected', c.dataset.pickCat === catKey);
            });

            // Reset sub-type if it doesn't belong to this category
            if (subInput.value && SUB_TYPES[subInput.value]?.category !== catKey) {
                subInput.value = '';
                form.querySelectorAll('.sub-chip.is-selected').forEach(c => c.classList.remove('is-selected'));
                customWrap.classList.add('hidden');
            }

            // Move to step 2
            stepCategory.classList.remove('is-active');
            stepDetails.classList.add('is-active');
            window.scrollTo({ top: stepDetails.offsetTop - 60, behavior: 'smooth' });

            // Trigger extras-component to re-evaluate
            document.dispatchEvent(new CustomEvent('biz-type-changed'));
        }

        function pickSubType(key) {
            subInput.value = key;
            form.querySelectorAll('.sub-chip').forEach(c => {
                c.classList.toggle('is-selected', c.dataset.pickSub === key);
            });
            // Toggle custom free-text
            const isOther = key.endsWith('_other');
            customWrap.classList.toggle('hidden', !isOther);
            if (isOther) customWrap.querySelector('input')?.focus();

            // Update breadcrumb to show sub_type label
            const sm = SUB_TYPES[key];
            if (sm) {
                const cat = CATEGORIES[sm.category];
                bcrumbLabel.innerHTML = cat.label + ' <span class="opacity-80">· ' + sm.label + '</span>';
            }

            // Trigger extras component to update via the standard event listener
            // (the component watches input[name="sub_type"] change events)
            subInput.dispatchEvent(new Event('change', { bubbles: true }));

            // Update extras section title
            const extrasTitle = form.querySelector('[data-extras-title]');
            if (extrasTitle && sm) {
                const cat = CATEGORIES[sm.category];
                extrasTitle.textContent = 'تفاصيل ' + (cat?.label || 'النشاط');
            }
        }

        // Wire category cards
        stepCategory.querySelectorAll('[data-pick-cat]').forEach(card => {
            card.addEventListener('click', () => pickCategory(card.dataset.pickCat));
        });

        // Wire sub-type chips
        subWrap.addEventListener('click', (e) => {
            const chip = e.target.closest('[data-pick-sub]');
            if (!chip) return;
            pickSubType(chip.dataset.pickSub);
        });

        // "Change" link — go back to step 1
        form.querySelector('[data-back-to-cat]')?.addEventListener('click', () => {
            stepDetails.classList.remove('is-active');
            stepCategory.classList.add('is-active');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Validate before submit: must have a sub_type
        form.addEventListener('submit', (e) => {
            if (!subInput.value) {
                e.preventDefault();
                alert('اختار نوع نشاطك الأول.');
                stepDetails.classList.remove('is-active');
                stepCategory.classList.add('is-active');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        // If we have an old (validation-error) value, restore the breadcrumb color
        if (catInput.value && CATEGORIES[catInput.value]) {
            breadcrumb.style.setProperty('--cat-color', CATEGORIES[catInput.value].color);
        }
    })();
</script>
@endsection
