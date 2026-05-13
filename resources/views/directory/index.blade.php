@extends('layouts.app', ['title' => 'دليل بنها · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto pb-8">

    {{-- ─── Title row ────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-4 rise rise-1">
        <div>
            <h1 class="text-2xl font-black text-ink-950 leading-none">الفئات</h1>
            <p class="text-xs text-ink-500 mt-1.5">دوّر على نشاطات بنها حسب الفئة</p>
        </div>
        <a href="{{ Auth::check() ? route('directory.create') : route('signup') }}"
           class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-coral-500 text-white text-xs font-extrabold transition hover:opacity-90"
           aria-label="ضيف نشاطك">
            <x-icon name="plus" class="w-3.5 h-3.5"/>
            ضيف نشاطك
        </a>
    </div>

    {{-- ─── Search + Filter (matches home pill style) ─────────── --}}
    <div class="mb-6 rise rise-2">
        <div class="flex items-center gap-2">
            <div class="flex-1 flex items-center gap-2 bg-cream-200 rounded-full ps-5 pe-5 py-3 ring-1 ring-ink-950/5 focus-within:ring-coral-500/40 transition">
                <x-icon name="search" class="w-4 h-4 text-ink-400 shrink-0"/>
                <input id="dir-search" type="search" autocomplete="off"
                       placeholder="دوّر على فئة..."
                       class="flex-1 bg-transparent text-sm text-ink-950 placeholder-ink-400 outline-none border-0"/>
            </div>
            <button type="button" id="dir-filter-btn"
                    class="w-12 h-12 rounded-full bg-coral-50 grid place-items-center text-coral-600 transition hover:bg-coral-200 shrink-0"
                    aria-label="فلتر">
                <x-icon name="filter" class="w-4 h-4"/>
            </button>
        </div>
    </div>

    {{-- ─── All categories grid ─────────────────────────── --}}
    <section class="mb-7 rise rise-4">
        <div class="flex items-center justify-between mb-4 px-1">
            <h2 class="text-xl font-black text-ink-950">كل الفئات</h2>
            <span class="text-xs font-bold text-ink-400" id="dir-cat-count">{{ count($categories) }}</span>
        </div>

        <div id="dir-cat-list" class="grid grid-cols-2 gap-3">
            @foreach($categories as $key => $meta)
                @php $count = $counts[$key] ?? 0; @endphp
                <a href="{{ route('directory.category', $key) }}"
                   data-cat-card
                   data-label="{{ $meta['label'] }}"
                   class="dir-cat-card group bg-white rounded-2xl p-4 flex flex-col items-center text-center transition hover:bg-cream-100">
                    <span class="cat-circle-disc">
                        <x-icon :name="$meta['icon'] ?? 'bag'" class="w-7 h-7"/>
                    </span>
                    <div class="mt-3 text-sm font-extrabold text-ink-950 leading-tight w-full truncate">{{ $meta['label'] }}</div>
                    <div class="text-[11px] text-ink-400 mt-0.5">
                        {{ $count > 0 ? number_format($count).' نشاط' : 'لسه فاضي' }}
                    </div>
                </a>
            @endforeach
        </div>

        <div id="dir-empty" class="hidden bg-white rounded-3xl px-6 pt-12 pb-20 text-center">
            <div class="relative w-24 h-24 mx-auto mb-5">
                <span class="absolute inset-0 rounded-full bg-coral-50"></span>
                <span class="absolute inset-3 rounded-full bg-coral-100/70"></span>
                <span class="absolute inset-0 grid place-items-center text-coral-600">
                    <x-icon name="search" class="w-9 h-9"/>
                </span>
            </div>
            <h3 class="text-lg font-black text-ink-950 mb-1.5">مفيش فئة بالاسم ده</h3>
            <p class="text-ink-500 text-sm leading-relaxed max-w-xs mx-auto">جرّب اسم تاني أو امسح البحث عشان تشوف كل الفئات.</p>
            <button type="button" id="dir-clear-search"
                    class="mt-5 inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-coral-50 text-coral-600 text-xs font-extrabold hover:bg-coral-100 transition">
                مسح البحث
            </button>
        </div>
    </section>

</div>

{{-- ─── Filter bottom sheet ─────────────────────────────────── --}}
<div id="dir-filter-sheet" class="modal-wrap" role="dialog" aria-modal="true" aria-labelledby="dir-filter-title">
    <div class="modal-backdrop" data-close></div>
    <div class="modal-sheet">
        <div class="px-5 pt-3 pb-5">
            {{-- Drag handle — wraps the top area so user can swipe down to dismiss --}}
            <div class="modal-drag-handle" data-drag-handle>
                <span class="modal-drag-bar"></span>
            </div>

            {{-- Header --}}
            <div class="flex items-center justify-between mb-1">
                <h3 id="dir-filter-title" class="text-lg font-black text-ink-950 inline-flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-coral-500 text-white grid place-items-center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-3.5 h-3.5">
                            <line x1="4"  y1="6"  x2="20" y2="6"/>
                            <line x1="7"  y1="12" x2="17" y2="12"/>
                            <line x1="10" y1="18" x2="14" y2="18"/>
                        </svg>
                    </span>
                    فلتر
                </h3>
                <button type="button" data-close
                        class="w-9 h-9 rounded-full grid place-items-center text-ink-500 hover:bg-ink-950/5 transition"
                        aria-label="إغلاق">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" class="w-5 h-5">
                        <line x1="18" y1="6" x2="6"  y2="18"/>
                        <line x1="6"  y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <p class="text-[11px] text-ink-500 mb-5">اختار منطقة لتفلتر النشاطات اللي فيها.</p>

            @if($topZones->isNotEmpty())
                <h4 class="text-xs font-extrabold text-ink-500 uppercase tracking-wider mb-3">المناطق</h4>
                <div class="flex flex-wrap gap-2 max-h-[55vh] overflow-y-auto pb-2">
                    @foreach($topZones as $z)
                        <a href="{{ route('directory.index', ['zone' => $z->id]) }}"
                           class="group inline-flex items-center gap-2 bg-white ring-1 ring-ink-950/8 hover:ring-coral-500 hover:bg-coral-500 hover:text-white px-3.5 py-2 rounded-full text-ink-950 transition">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-coral-500 group-hover:text-white transition">
                                <path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span class="text-[13px] font-extrabold">{{ $z->name }}</span>
                            @if($z->businesses_count)
                                <span class="text-[10px] font-extrabold bg-coral-100 text-coral-700 group-hover:bg-white/25 group-hover:text-white rounded-full px-1.5 py-0.5 transition">
                                    {{ number_format($z->businesses_count) }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-ink-400 py-4 text-center">مفيش مناطق متاحة دلوقتي.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    // ── Realtime category filter ────────────────────────────
    const input   = document.getElementById('dir-search');
    const list    = document.getElementById('dir-cat-list');
    const counter = document.getElementById('dir-cat-count');
    const empty   = document.getElementById('dir-empty');

    if (input && list) {
        const cards = Array.from(list.querySelectorAll('[data-cat-card]'));
        const norm  = (s) => (s || '').toString().toLowerCase().trim();

        const apply = () => {
            const q = norm(input.value);
            let visible = 0;

            cards.forEach((c) => {
                const match = !q || norm(c.dataset.label).includes(q);
                c.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            if (counter) counter.textContent = visible;
            if (empty)   empty.classList.toggle('hidden', visible !== 0);
            list.classList.toggle('hidden', visible === 0);
        };

        input.addEventListener('input', apply);

        const clearBtn = document.getElementById('dir-clear-search');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                input.value = '';
                apply();
                input.focus();
            });
        }
    }

    // ── Filter bottom sheet ─────────────────────────────────
    const btn   = document.getElementById('dir-filter-btn');
    const sheet = document.getElementById('dir-filter-sheet');

    if (btn && sheet) {
        const sheetEl = sheet.querySelector('.modal-sheet');
        const handle  = sheet.querySelector('[data-drag-handle]');

        const open  = () => { sheet.classList.add('open');    document.body.style.overflow = 'hidden'; };
        const close = () => {
            sheet.classList.remove('open');
            document.body.style.overflow = '';
            // Reset any inline drag-translate so the next open animates from rest
            if (sheetEl) sheetEl.style.transform = '';
        };

        btn.addEventListener('click', open);
        sheet.querySelectorAll('[data-close]').forEach((el) => el.addEventListener('click', close));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sheet.classList.contains('open')) close();
        });

        // ── Drag-to-dismiss ──────────────────────────────────────────
        // Pointer events cover mouse + touch + pen. The handle has
        // touch-action: none so the browser won't fight us by scrolling.
        if (handle && sheetEl) {
            let startY = 0, lastY = 0, dy = 0, startT = 0, dragging = false;

            const onDown = (e) => {
                if (! sheet.classList.contains('open')) return;
                dragging = true;
                startY = lastY = e.clientY ?? (e.touches && e.touches[0].clientY) ?? 0;
                startT = performance.now();
                dy = 0;
                sheetEl.classList.add('is-dragging');
                handle.setPointerCapture?.(e.pointerId);
            };

            const onMove = (e) => {
                if (! dragging) return;
                const y = e.clientY ?? (e.touches && e.touches[0].clientY) ?? 0;
                dy = Math.max(0, y - startY);   // never allow upward drag
                lastY = y;
                sheetEl.style.transform = `translateY(${dy}px)`;
            };

            const onUp = () => {
                if (! dragging) return;
                dragging = false;
                sheetEl.classList.remove('is-dragging');

                const elapsed   = performance.now() - startT;
                const velocity  = dy / Math.max(elapsed, 1);          // px/ms
                const sheetH    = sheetEl.offsetHeight || 400;
                const flicked   = velocity > 0.6;                     // fast swipe
                const dragged30 = dy > sheetH * 0.30;                  // dragged > 30% down

                if (flicked || dragged30) {
                    // Slide it the rest of the way out, then close
                    sheetEl.style.transform = `translateY(${sheetH + 40}px)`;
                    setTimeout(close, 180);
                } else {
                    // Snap back
                    sheetEl.style.transform = '';
                }
            };

            handle.addEventListener('pointerdown', onDown);
            handle.addEventListener('pointermove', onMove);
            handle.addEventListener('pointerup',   onUp);
            handle.addEventListener('pointercancel', onUp);
        }
    }
})();
</script>
@endpush
