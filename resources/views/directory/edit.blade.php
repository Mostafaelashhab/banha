@extends('layouts.app', ['title' => 'تعديل ' . $business->name])

@push('head')
<style>
    [data-step] { display: none; }
    [data-step].is-active { display: block; animation: stepFadeIn .35s cubic-bezier(.2,.9,.3,1); }
    @keyframes stepFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }

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

    .step-num {
        width: 26px; height: 26px;
        border-radius: 50%;
        background: var(--cat-color, #2D5BFF);
        color: #fff;
        display: grid; place-items: center;
        font-weight: 900;
        font-size: 12px;
        flex-shrink: 0;
    }

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
    $cm        = $business->categoryMeta();
    $sm        = $business->subTypeMeta();
    $oldSub    = old('sub_type', $business->sub_type);
    $oldCat    = $oldSub ? (\App\Models\Business::SUB_TYPES[$oldSub]['category'] ?? $business->category) : $business->category;
@endphp

<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.show', $business) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950 hover:bg-cream-100 transition">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-black text-ink-950 truncate">تعديل · {{ $business->name }}</h1>
            <p class="text-[11px] text-ink-500">حدّث بياناتك في أي وقت — التغيير يبان فوراً.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('directory.update', $business) }}" enctype="multipart/form-data"
          class="space-y-4" id="edit-form">
        @csrf
        @method('PATCH')
        <input type="hidden" name="sub_type" value="{{ $oldSub }}" data-sub-type-input required>
        <input type="hidden" name="category" value="{{ $oldCat }}" data-category-input>

        {{-- ──── STEP 1: Category picker (hidden by default; shown when "غيّر النوع" clicked) ──── --}}
        <section data-step="category" class="card-light p-5">
            <div class="flex items-center gap-3 mb-4">
                <span class="step-num" style="--cat-color: #2D5BFF">١</span>
                <div>
                    <h2 class="text-sm font-extrabold text-ink-950">غيّر نوع نشاطك</h2>
                    <p class="text-[11px] text-ink-500">دوس على الفئة الجديدة.</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2.5">
                @foreach(\App\Models\Business::CATEGORIES as $catKey => $catMeta)
                    <button type="button" class="cat-card {{ $catKey === $oldCat ? 'is-selected' : '' }}" data-pick-cat="{{ $catKey }}"
                            style="--cat-color: {{ $catMeta['color'] }}">
                        <span class="cat-icon" style="background: {{ $catMeta['color'] }}1a; color: {{ $catMeta['color'] }};">
                            <x-icon :name="$catMeta['icon'] ?? 'bag'" class="w-5 h-5"/>
                        </span>
                        <span class="cat-label">{{ $catMeta['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- ──── STEP 2+: Form (visible by default in edit mode) ──── --}}
        <section data-step="details" class="is-active space-y-4">

            {{-- Type breadcrumb --}}
            <div class="card-light p-3 flex items-center gap-2" data-type-breadcrumb style="--cat-color: {{ $cm['color'] }};">
                <span class="text-xs font-bold text-ink-500">نوع النشاط:</span>
                <span class="type-pill" data-bcrumb-pill>
                    <x-icon :name="$cm['icon'] ?? 'bag'" class="w-3 h-3"/>
                    <span data-bcrumb-label>{{ $cm['label'] }} <span class="opacity-80">· {{ $sm['label'] }}</span></span>
                    <button type="button" class="change" data-back-to-cat>غيّر</button>
                </span>
            </div>

            {{-- Sub-type chips --}}
            <div class="card-light p-5" data-subtype-wrap>
                <div class="flex items-center gap-3 mb-3">
                    <span class="step-num" style="--cat-color: {{ $cm['color'] }};">٢</span>
                    <h2 class="text-sm font-extrabold text-ink-950">التخصص</h2>
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

                <div class="mt-3 {{ str_ends_with($oldSub, '_other') ? '' : 'hidden' }}" data-custom-subtype-wrap>
                    <label class="text-xs font-bold text-ink-500 mb-1 block">اكتب نوع نشاطك بالظبط</label>
                    <input type="text" name="custom_sub_type" maxlength="80"
                           value="{{ old('custom_sub_type', $business->custom_sub_type) }}"
                           placeholder="مثلاً: محل ساعات، مدرّس عربي…"
                           class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition text-sm">
                    @error('custom_sub_type') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @error('sub_type') <p class="text-blush-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- Basic info --}}
            <div class="card-light p-5 space-y-4">
                <div class="flex items-center gap-3 mb-1">
                    <span class="step-num" style="--cat-color: {{ $cm['color'] }};">٣</span>
                    <h2 class="text-sm font-extrabold text-ink-950">البيانات الأساسية</h2>
                </div>

                {{-- Photo: shows current photo or fallback --}}
                <div>
                    <label class="text-xs font-bold text-ink-500 mb-2 block">صورة النشاط</label>
                    <div class="flex items-center gap-3">
                        @if($business->photo_url)
                            <img src="{{ $business->photo_url }}" alt="" class="w-16 h-16 rounded-xl object-cover shrink-0">
                        @else
                            <span class="w-16 h-16 rounded-xl grid place-items-center shrink-0"
                                  style="background: {{ $cm['color'] }}14; color: {{ $cm['color'] }};">
                                <x-icon :name="$sm['icon'] ?? 'bag'" class="w-7 h-7"/>
                            </span>
                        @endif
                        <label class="flex-1 cursor-pointer bg-cream-100 rounded-2xl p-3 border border-ink-950/8 hover:border-coral-500/40 transition">
                            <span class="text-sm font-bold text-ink-950 block" data-photo-name>
                                {{ $business->photo_url ? 'استبدل الصورة' : 'ارفع صورة' }}
                            </span>
                            <span class="block text-[10px] text-ink-500 mt-0.5">JPG / PNG / WEBP · حتى ٣ ميجا</span>
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden"
                                   onchange="this.parentElement.querySelector('[data-photo-name]').textContent = this.files[0]?.name || '{{ $business->photo_url ? 'استبدل الصورة' : 'ارفع صورة' }}'">
                        </label>
                    </div>
                    @error('photo') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1.5 block">اسم النشاط *</label>
                    <input type="text" name="name" required minlength="3" maxlength="120"
                           value="{{ old('name', $business->name) }}"
                           class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                    @error('name') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1.5 block">المنطقة *</label>
                    <select name="zone_id" required
                            class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                        @foreach($zones as $z)
                            <option value="{{ $z->id }}" {{ old('zone_id', $business->zone_id) == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                        @endforeach
                    </select>
                    @error('zone_id') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1.5 block">العنوان</label>
                    <input type="text" name="address" maxlength="200"
                           value="{{ old('address', $business->address) }}"
                           placeholder="شارع · حي · معلم قريب"
                           class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-ink-500 mb-1.5 block">رقم تليفون</label>
                        <input type="tel" name="phone" inputmode="numeric" maxlength="11" dir="ltr"
                               value="{{ old('phone', $business->phone) }}"
                               placeholder="010xxxxxxxx"
                               class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                        @error('phone') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-ink-500 mb-1.5 block">رقم واتساب</label>
                        <input type="tel" name="whatsapp" inputmode="numeric" maxlength="11" dir="ltr"
                               value="{{ old('whatsapp', $business->whatsapp) }}"
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
                           value="{{ old('hotline', $business->hotline) }}"
                           placeholder="مثلاً: 19999 / 16789 / 0800-XXX-XXX"
                           class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition">
                    <p class="text-[10px] text-ink-400 mt-1">للأماكن اللي شغّالة بـ خط ساخن بدل موبايل عادي (سلاسل، بنوك، مستشفيات…)</p>
                    @error('hotline') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <x-hours-picker :schedule="$business->hours_schedule" :hours-text="$business->hours"/>

                <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer border border-ink-950/8 has-[:checked]:bg-mint-100/50 has-[:checked]:border-mint-500/40 transition">
                    <input type="checkbox" name="is_24h" value="1" {{ old('is_24h', $business->is_24h) ? 'checked' : '' }} class="sr-only peer">
                    <span class="w-12 h-7 rounded-full bg-ink-300 relative transition peer-checked:bg-mint-500">
                        <span class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white shadow transition peer-checked:translate-x-[-1.25rem] rtl:peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-bold text-ink-950">شغّال ٢٤ ساعة</span>
                </label>

                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1.5 block">وصف مختصر</label>
                    <textarea name="description" rows="3" maxlength="1000"
                              class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 focus:bg-white transition resize-none">{{ old('description', $business->description) }}</textarea>
                </div>
            </div>

            {{-- Map picker --}}
            <div class="card-light p-5">
                <div class="flex items-center gap-3 mb-3">
                    <span class="step-num" style="--cat-color: {{ $cm['color'] }};">٤</span>
                    <h2 class="text-sm font-extrabold text-ink-950">المكان على الخريطة</h2>
                </div>
                <x-map-picker
                    :lat="$business->lat"
                    :lng="$business->lng"
                    label=""
                    help="**لو شِلت المكان، نشاطك هيختفي من خريطة بنها** — تقدر ترجعه في أي وقت."/>
            </div>

            {{-- Type-specific extras --}}
            <div class="card-light p-5" data-extras-section>
                <div class="flex items-center gap-3 mb-3">
                    <span class="step-num" style="--cat-color: {{ $cm['color'] }};">٥</span>
                    <div>
                        <h2 class="text-sm font-extrabold text-ink-950" data-extras-title>تفاصيل {{ $cm['label'] }}</h2>
                        <p class="text-[11px] text-ink-500">الحقول هنا بتتغيّر حسب النوع.</p>
                    </div>
                </div>

                <x-business-extras :sub-type="$oldSub" :values="$business->extra ?? []"/>
            </div>

            {{-- Booking settings --}}
            <div class="card-light p-5">
                <div class="flex items-center gap-3 mb-3">
                    <span class="step-num" style="--cat-color: {{ $cm['color'] }};">٦</span>
                    <div>
                        <h2 class="text-sm font-extrabold text-ink-950">حجز المواعيد الإلكتروني</h2>
                        <p class="text-[11px] text-ink-500">اليوزر يقدر يحجز موعد من على الموبايل — هتشوف الحجوزات في لوحتك.</p>
                    </div>
                </div>

                <label class="flex items-start gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer hover:bg-cream-200 transition mb-3">
                    <input type="hidden" name="booking_enabled" value="0">
                    <input type="checkbox" name="booking_enabled" value="1"
                           {{ old('booking_enabled', $business->booking_enabled) ? 'checked' : '' }}
                           class="mt-1 w-5 h-5 accent-coral-500">
                    <div class="flex-1">
                        <div class="text-sm font-extrabold text-ink-950">فعّل الحجز الإلكتروني</div>
                        <div class="text-[11px] text-ink-500">اليوزر هيشوف زرّ "احجز موعد" في صفحة نشاطك.</div>
                    </div>
                </label>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="text-[11px] font-bold text-ink-500 mb-1 block">مدة الموعد</label>
                        <select name="booking_slot_minutes"
                                class="w-full bg-cream-100 rounded-xl px-3 py-2.5 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                            @foreach([15, 20, 30, 45, 60, 90, 120] as $min)
                                <option value="{{ $min }}" @selected(old('booking_slot_minutes', $business->booking_slot_minutes ?? 30) == $min)>
                                    {{ $min }} دقيقة
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-ink-500 mb-1 block">حجز قبل</label>
                        <select name="booking_lead_hours"
                                class="w-full bg-cream-100 rounded-xl px-3 py-2.5 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                            @foreach([0, 1, 2, 4, 12, 24] as $h)
                                <option value="{{ $h }}" @selected(old('booking_lead_hours', $business->booking_lead_hours ?? 2) == $h)>
                                    {{ $h == 0 ? 'بدون' : $h . ' ساعة' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-ink-500 mb-1 block">سعة الموعد</label>
                        <select name="booking_capacity"
                                class="w-full bg-cream-100 rounded-xl px-3 py-2.5 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                            @foreach([1, 2, 3, 5, 10] as $c)
                                <option value="{{ $c }}" @selected(old('booking_capacity', $business->booking_capacity ?? 1) == $c)>
                                    {{ $c }} {{ $c == 1 ? 'حجز' : 'حجوزات' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="text-[10px] text-ink-400 mt-2 leading-relaxed">
                    💡 السعة = كام شخص ممكن يحجزوا في نفس الميعاد. لـ دكتور خلّيها 1، لـ صالون فيه كراسي كتير اعملها 2-3.
                </p>

                @if($business->booking_enabled)
                    <a href="{{ route('booking.owner.index', $business) }}"
                       class="mt-3 inline-flex items-center gap-2 text-xs font-bold text-coral-600 hover:underline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        شوف الحجوزات الحالية ←
                    </a>
                @endif
            </div>

            {{-- Sticky submit --}}
            <div class="sticky-submit">
                <button type="submit" class="btn-primary w-full justify-center !py-3.5 text-sm shadow-xl shadow-coral-500/30">
                    احفظ التعديلات
                    <x-icon name="check" class="w-4 h-4"/>
                </button>
            </div>
        </section>
    </form>

    {{-- Delete (separate form, danger zone) --}}
    <form method="POST" action="{{ route('directory.destroy', $business) }}"
          class="mt-6"
          data-confirm="حذف النشاط؟"
          data-confirm-body="هينحذف خالص ومش هيرجع تاني."
          data-confirm-action="احذف"
          data-confirm-tone="danger">
        @csrf
        @method('DELETE')
        <button type="submit" class="card-light p-3 w-full text-blush-500 font-bold text-sm hover:bg-blush-100/50 transition flex items-center justify-center gap-2 border border-blush-500/20">
            <x-icon name="trash" class="w-4 h-4"/>
            احذف النشاط نهائياً
        </button>
    </form>
</div>

<script>
    (function () {
        const form         = document.getElementById('edit-form');
        const subInput     = form.querySelector('[data-sub-type-input]');
        const catInput     = form.querySelector('[data-category-input]');
        const stepCategory = form.querySelector('[data-step="category"]');
        const stepDetails  = form.querySelector('[data-step="details"]');
        const subWrap      = form.querySelector('[data-subtype-wrap]');
        const customWrap   = form.querySelector('[data-custom-subtype-wrap]');
        const breadcrumb   = form.querySelector('[data-type-breadcrumb]');
        const bcrumbLabel  = form.querySelector('[data-bcrumb-label]');

        const CATEGORIES = @json(\App\Models\Business::CATEGORIES);
        const SUB_TYPES  = @json(\App\Models\Business::SUB_TYPES);

        function pickCategory(catKey) {
            const meta = CATEGORIES[catKey];
            if (!meta) return;
            catInput.value = catKey;

            breadcrumb.style.setProperty('--cat-color', meta.color);

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
                bcrumbLabel.textContent = meta.label;
                form.querySelectorAll('.sub-chip.is-selected').forEach(c => c.classList.remove('is-selected'));
                customWrap.classList.add('hidden');
            } else if (SUB_TYPES[subInput.value]) {
                bcrumbLabel.innerHTML = meta.label + ' <span class="opacity-80">· ' + SUB_TYPES[subInput.value].label + '</span>';
            }

            // Move to step 2
            stepCategory.classList.remove('is-active');
            stepDetails.classList.add('is-active');
            window.scrollTo({ top: stepDetails.offsetTop - 60, behavior: 'smooth' });

            document.dispatchEvent(new CustomEvent('biz-type-changed'));
        }

        function pickSubType(key) {
            subInput.value = key;
            form.querySelectorAll('.sub-chip').forEach(c => {
                c.classList.toggle('is-selected', c.dataset.pickSub === key);
            });
            const isOther = key.endsWith('_other');
            customWrap.classList.toggle('hidden', !isOther);
            if (isOther) customWrap.querySelector('input')?.focus();

            const sm = SUB_TYPES[key];
            if (sm) {
                const cat = CATEGORIES[sm.category];
                bcrumbLabel.innerHTML = cat.label + ' <span class="opacity-80">· ' + sm.label + '</span>';

                const extrasTitle = form.querySelector('[data-extras-title]');
                if (extrasTitle) extrasTitle.textContent = 'تفاصيل ' + (cat?.label || 'النشاط');
            }

            subInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        stepCategory.querySelectorAll('[data-pick-cat]').forEach(card => {
            card.addEventListener('click', () => pickCategory(card.dataset.pickCat));
        });

        subWrap.addEventListener('click', (e) => {
            const chip = e.target.closest('[data-pick-sub]');
            if (!chip) return;
            pickSubType(chip.dataset.pickSub);
        });

        // "غيّر" — go back to step 1
        form.querySelector('[data-back-to-cat]')?.addEventListener('click', () => {
            stepDetails.classList.remove('is-active');
            stepCategory.classList.add('is-active');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        form.addEventListener('submit', (e) => {
            if (!subInput.value) {
                e.preventDefault();
                alert('اختار نوع نشاطك الأول.');
                stepDetails.classList.remove('is-active');
                stepCategory.classList.add('is-active');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    })();
</script>
@endsection
