@extends('admin.layouts.admin', ['title' => 'بانرات الإعلانات · Admin'])

@section('content')
<div class="flex items-center justify-between mb-1">
    <h1 class="text-2xl font-black">بانرات الإعلانات</h1>
    <span class="text-xs font-bold text-ink-500">{{ $banners->count() }} بانر</span>
</div>
<p class="text-ink-500 text-sm mb-6">دي البانرات اللي بتظهر في سلايدر الصفحة الرئيسية. الترتيب من الأصغر للأكبر.</p>

<div class="grid lg:grid-cols-3 gap-4">

    {{-- ─── Create new ─────────────────────────────── --}}
    <div class="a-card p-5 lg:col-span-1 lg:sticky lg:top-4 self-start">
        <h3 class="text-sm font-extrabold mb-4">ضيف بانر جديد</h3>
        <form method="POST" action="{{ route('admin.promo.banners.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf

            {{-- ── Primary path: image + business picker ── --}}
            <div class="bg-mint-50 ring-1 ring-mint-500/20 rounded-2xl p-3">
                <p class="text-[11px] font-bold text-mint-700 mb-2">الطريقة الجديدة: صورة + نشاط مرتبط</p>
                <p class="text-[10px] text-ink-500 leading-relaxed">
                    ارفع صورة البانر، اختار النشاط اللي هتودّيه عليه — والصورة بتفتح صفحة النشاط لما حد يضغط عليها.
                    مش محتاج عنوان ولا وصف.
                </p>
            </div>

            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">صورة البانر <span class="text-blush-500">*</span></label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                       class="w-full text-xs file:me-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-coral-500 file:text-white file:font-bold file:cursor-pointer">
                <p class="text-[10px] text-ink-400 mt-1">JPG / PNG / WEBP · أقل من 4MB · مقاس مقترح 1200×600</p>
                @error('image') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">يودّي على نشاط</label>
                <input list="biz-list-new" placeholder="اكتب اسم النشاط…"
                       data-biz-picker-input
                       class="w-full rounded-2xl px-3 py-2.5 text-sm outline-0 focus:border-coral-500 transition">
                <datalist id="biz-list-new">
                    @foreach($businesses as $b)
                        <option value="{{ $b->name }} · {{ $b->id }}" data-id="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </datalist>
                <input type="hidden" name="business_id" value="" data-biz-picker-id>
                <p class="text-[10px] text-ink-400 mt-1">ابدأ تكتب الاسم وهيظهرلك المتاح. سيبها فاضية لو هتحط رابط يدوي.</p>
                @error('business_id') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">ترتيب</label>
                <input type="number" name="sort_order" min="0" max="9999" value="0"
                       class="w-full rounded-2xl px-3 py-2.5 text-sm outline-0 focus:border-coral-500 transition">
                <p class="text-[10px] text-ink-400 mt-1">الأصغر يظهر أول. متساوي = الأحدث أول.</p>
            </div>

            {{-- ── Legacy / Advanced: keep for backward compat, hidden by default ── --}}
            <details class="bg-cream-50 rounded-2xl ring-1 ring-ink-950/8 p-3">
                <summary class="text-[11px] font-bold text-ink-500 cursor-pointer">إعدادات متقدّمة (نص + ألوان + جدولة) ▾</summary>
                <div class="mt-3 space-y-3">
                    <div>
                        <label class="text-[10px] font-bold text-ink-500 mb-1 block">تاج (Badge)</label>
                        <input type="text" name="tag" maxlength="60" placeholder="جديد · خصم"
                               class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-ink-500 mb-1 block">عنوان (اختياري لو حابب overlay)</label>
                        <input type="text" name="title" maxlength="120" placeholder="سيبها فاضية لو الصورة كافية"
                               class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        @error('title') <p class="text-blush-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-ink-500 mb-1 block">وصف</label>
                        <textarea name="description" rows="2" maxlength="500"
                                  class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition resize-none"></textarea>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-ink-500 mb-1 block">نص الزرار</label>
                        <input type="text" name="cta_text" maxlength="40" placeholder="اضغط هنا"
                               class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-ink-500 mb-1 block">رابط يدوي (لو مش مربوط بنشاط)</label>
                        <input type="text" name="href" maxlength="255" placeholder="https://… أو /directory"
                               class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="bg_from" maxlength="16" placeholder="#2D5BFF"
                               class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        <input type="text" name="bg_to" maxlength="16" placeholder="#FFD440"
                               class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                    </div>
                </div>
            </details>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1 block">يبدأ من</label>
                    <input type="datetime-local" name="starts_at"
                           class="w-full rounded-2xl px-3 py-2.5 text-sm outline-0 focus:border-coral-500 transition">
                </div>
                <div>
                    <label class="text-xs font-bold text-ink-500 mb-1 block">ينتهي في</label>
                    <input type="datetime-local" name="ends_at"
                           class="w-full rounded-2xl px-3 py-2.5 text-sm outline-0 focus:border-coral-500 transition">
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4">
                <span class="font-bold">شغّال</span>
            </label>

            <button type="submit" class="btn-primary w-full justify-center !py-3">
                <x-icon name="plus" class="w-4 h-4"/>
                أضف البانر
            </button>
        </form>
    </div>

    {{-- ─── Existing banners ───────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">
        @forelse($banners as $b)
            <div class="a-card p-4">
                {{-- Preview --}}
                <div class="rounded-2xl overflow-hidden mb-3 relative" style="aspect-ratio: 16/9;">
                    @if($b->image_url)
                        <img src="{{ $b->image_url }}" alt="{{ $b->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full"
                             style="background: linear-gradient(135deg, {{ $b->bg_from ?: '#2D5BFF' }}, {{ $b->bg_to ?: '#FFD440' }})"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-ink-950/70 via-ink-950/20 to-transparent p-4 flex flex-col justify-end text-white">
                        @if($b->tag)
                            <span class="text-[10px] font-extrabold tracking-wider uppercase opacity-90 mb-1">{{ $b->tag }}</span>
                        @endif
                        <div class="font-black text-lg leading-tight">{{ $b->title }}</div>
                        @if($b->description)
                            <p class="text-white/90 text-[12px] mt-1 leading-snug font-bold line-clamp-2">{{ $b->description }}</p>
                        @endif
                        @if($b->cta_text)
                            <span class="mt-2 inline-flex items-center gap-1 self-start bg-white/95 text-ink-950 px-3 py-1.5 rounded-xl text-xs font-extrabold">
                                {{ $b->cta_text }}
                            </span>
                        @endif
                    </div>

                    {{-- Status pill --}}
                    <span class="absolute top-2 end-2 a-pill {{ $b->is_active ? 'bg-mint-500 text-white' : 'bg-ink-950/60 text-white' }}">
                        {{ $b->is_active ? 'شغّال' : 'متوقف' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.promo.banners.update', $b) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf

                    {{-- Business link (primary) — datalist for quick search across all businesses --}}
                    <div>
                        <label class="text-[10px] font-bold text-ink-500 mb-1 block">يودّي على نشاط</label>
                        @php $linkedBiz = $b->business; @endphp
                        <input list="biz-list-{{ $b->id }}" data-biz-picker-input
                               value="{{ $linkedBiz ? $linkedBiz->name . ' · ' . $linkedBiz->id : '' }}"
                               placeholder="اكتب اسم النشاط…"
                               class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        <datalist id="biz-list-{{ $b->id }}">
                            @foreach($businesses as $biz)
                                <option value="{{ $biz->name }} · {{ $biz->id }}">{{ $biz->name }}</option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="business_id" data-biz-picker-id value="{{ $b->business_id }}">
                    </div>

                    <div class="grid md:grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-ink-500 mb-1 block">العنوان (اختياري)</label>
                            <input type="text" name="title" maxlength="120" value="{{ $b->title }}"
                                   class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-ink-500 mb-1 block">تاج</label>
                            <input type="text" name="tag" maxlength="60" value="{{ $b->tag }}"
                                   class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-ink-500 mb-1 block">الوصف</label>
                        <textarea name="description" rows="2" maxlength="500"
                                  class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition resize-none">{{ $b->description }}</textarea>
                    </div>

                    <div class="grid md:grid-cols-3 gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-ink-500 mb-1 block">نص الزرار</label>
                            <input type="text" name="cta_text" maxlength="40" value="{{ $b->cta_text }}"
                                   class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-[10px] font-bold text-ink-500 mb-1 block">الرابط</label>
                            <input type="text" name="href" maxlength="255" value="{{ $b->href }}"
                                   class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-ink-500 mb-1 block">ترتيب</label>
                            <input type="number" name="sort_order" min="0" max="9999" value="{{ $b->sort_order }}"
                                   class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-ink-500 mb-1 block">لون من</label>
                            <input type="text" name="bg_from" maxlength="16" value="{{ $b->bg_from }}"
                                   class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-ink-500 mb-1 block">لون لـ</label>
                            <input type="text" name="bg_to" maxlength="16" value="{{ $b->bg_to }}"
                                   class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10px] font-bold text-ink-500 mb-1 block">يبدأ من</label>
                            <input type="datetime-local" name="starts_at"
                                   value="{{ $b->starts_at?->format('Y-m-d\TH:i') }}"
                                   class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-ink-500 mb-1 block">ينتهي في</label>
                            <input type="datetime-local" name="ends_at"
                                   value="{{ $b->ends_at?->format('Y-m-d\TH:i') }}"
                                   class="w-full rounded-xl px-3 py-2 text-xs outline-0 focus:border-coral-500 transition">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-2 items-end">
                        <div>
                            <label class="text-[10px] font-bold text-ink-500 mb-1 block">تغيير الصورة</label>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                   class="w-full text-xs file:me-2 file:py-1.5 file:px-2 file:rounded-lg file:border-0 file:bg-cream-200 file:font-bold file:cursor-pointer">
                        </div>
                        @if($b->image_url)
                            <label class="flex items-center gap-2 text-xs">
                                <input type="checkbox" name="clear_image" value="1" class="w-4 h-4">
                                <span class="font-bold">امسح الصورة الحالية</span>
                            </label>
                        @endif
                    </div>

                    <input type="hidden" name="is_active" value="{{ $b->is_active ? 1 : 0 }}">

                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-ink-950/8">
                        <button type="submit" class="btn-primary !py-2 !px-4 text-xs">
                            <x-icon name="check" class="w-3.5 h-3.5"/>
                            احفظ
                        </button>
                    </div>
                </form>

                <div class="flex items-center gap-2 mt-2">
                    <form method="POST" action="{{ route('admin.promo.banners.toggle', $b) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="a-pill px-3 py-1.5 {{ $b->is_active ? 'bg-ink-950/10 text-ink-950 hover:bg-ink-950/20' : 'bg-mint-100 text-mint-700 hover:bg-mint-500 hover:text-white' }} transition">
                            {{ $b->is_active ? 'وقّف' : 'شغّل' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.promo.banners.destroy', $b) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" data-confirm="متأكد تمسح البانر؟" data-confirm-action="امسح"
                                class="a-pill px-3 py-1.5 bg-blush-100 text-blush-500 hover:bg-blush-500 hover:text-white transition">
                            <x-icon name="x" class="w-3.5 h-3.5"/>
                            امسح
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="a-card p-8 text-center">
                <p class="text-ink-500 text-sm font-bold">مفيش بانرات لسه — ضيف أول واحد من الجنب.</p>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
// Datalist-based business picker: each option's value is "Name · ID".
// We extract the trailing integer and stash it in the sibling hidden input
// named "business_id" before submit. Empty string clears the link.
(function () {
    document.querySelectorAll('[data-biz-picker-input]').forEach(input => {
        const form = input.closest('form');
        if (!form) return;
        const hidden = form.querySelector('[data-biz-picker-id]');
        if (!hidden) return;

        function sync() {
            const m = input.value.match(/·\s*(\d+)\s*$/);
            hidden.value = m ? m[1] : '';
        }
        input.addEventListener('input', sync);
        input.addEventListener('change', sync);
        form.addEventListener('submit', sync);
    });
})();
</script>
@endpush
@endsection
