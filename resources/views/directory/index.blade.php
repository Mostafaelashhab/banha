@extends('layouts.app', ['title' => 'دليل بنها · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto pb-8">

    {{-- ─── "Add your business" banner ────────────────────────── --}}
    <div class="mb-5 rise rise-1">
        @include('partials.promo-banner', [
            'href'    => Auth::check() ? route('directory.create') : route('signup'),
            'variant' => 'add',
            'tag'     => 'مجاناً · أضف نشاطك',
            'title'   => 'نشاطك في بنهاوي',
            'desc'    => 'ضيف مكانك يطلع للناس اللي بتدوّر في بنها.',
            'cta'     => 'ضيف نشاطك',
        ])
    </div>

    {{-- ─── Search + Filter ────────────────────────────────────── --}}
    <div class="mb-5 rise rise-2">
        <div class="flex items-center gap-2">
            <div class="flex-1 relative">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     class="absolute top-1/2 -translate-y-1/2 start-3.5 w-4 h-4 text-ink-400 pointer-events-none">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input id="dir-search" type="search" autocomplete="off"
                       placeholder="دوّر على فئة..."
                       class="w-full bg-cream-200 rounded-2xl ps-9 pe-4 py-3 text-sm placeholder-ink-400 focus:outline-none transition" />
            </div>
            <button type="button" id="dir-filter-btn"
                    class="relative w-12 h-12 rounded-2xl bg-cream-200 grid place-items-center text-ink-950 transition shrink-0"
                    aria-label="فلتر">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-4 h-4">
                    <line x1="4"  y1="6"  x2="20" y2="6"/>
                    <line x1="7"  y1="12" x2="17" y2="12"/>
                    <line x1="10" y1="18" x2="14" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ─── All categories — clean list ─────────────────────────── --}}
    <section class="mb-7 rise rise-4">
        <div class="flex items-baseline justify-between mb-3 px-1">
            <h2 class="text-base font-extrabold text-ink-950">كل الفئات</h2>
            <span class="text-[11px] font-bold text-ink-400" id="dir-cat-count">{{ count($categories) }}</span>
        </div>

        <div id="dir-cat-list" class="grid grid-cols-2 gap-3">
            @foreach($categories as $key => $meta)
                @php $count = $counts[$key] ?? 0; @endphp
                <a href="{{ route('directory.category', $key) }}"
                   data-cat-card
                   data-label="{{ $meta['label'] }}"
                   class="dir-cat-card bg-white rounded-2xl ring-1 ring-ink-950/8 p-4 flex flex-col items-center text-center hover:ring-coral-500/30 transition">
                    <span class="w-16 h-16 rounded-2xl grid place-items-center"
                          style="background: {{ $meta['color'] }}14; color: {{ $meta['color'] }};">
                        <x-icon :name="$meta['icon'] ?? 'bag'" class="w-8 h-8"/>
                    </span>
                    <div class="mt-3 text-sm font-extrabold text-ink-950 leading-tight w-full truncate">{{ $meta['label'] }}</div>
                    <div class="text-[11px] text-ink-400 mt-0.5">
                        {{ $count > 0 ? number_format($count).' نشاط' : 'لسه فاضي' }}
                    </div>
                </a>
            @endforeach
        </div>

        <div id="dir-empty" class="hidden text-center py-10 text-sm text-ink-400">
            مفيش فئة بالاسم ده
        </div>
    </section>

</div>

{{-- ─── Filter bottom sheet ─────────────────────────────────── --}}
<div id="dir-filter-sheet" class="modal-wrap" role="dialog" aria-modal="true" aria-labelledby="dir-filter-title">
    <div class="modal-backdrop" data-close></div>
    <div class="modal-sheet">
        <div class="p-5 pt-3">
            <div class="mx-auto w-10 h-1 rounded-full bg-ink-950/15 mb-4"></div>

            <div class="flex items-center justify-between mb-5">
                <h3 id="dir-filter-title" class="text-lg font-black text-ink-950">فلتر</h3>
                <button type="button" data-close
                        class="w-9 h-9 rounded-full grid place-items-center text-ink-500 hover:bg-ink-950/5"
                        aria-label="إغلاق">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" class="w-5 h-5">
                        <line x1="18" y1="6" x2="6"  y2="18"/>
                        <line x1="6"  y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            @if($topZones->isNotEmpty())
                <div class="mb-1">
                    <div class="flex items-baseline justify-between mb-3">
                        <h4 class="text-sm font-extrabold text-ink-950">المناطق</h4>
                        <a href="{{ route('zones') }}" class="text-xs font-bold text-coral-600">شوف الكل ←</a>
                    </div>
                    <div class="flex flex-wrap gap-2 max-h-[55vh] overflow-y-auto pb-3">
                        @foreach($topZones as $z)
                            <a href="{{ route('directory.index', ['zone' => $z->id]) }}"
                               class="inline-flex items-center gap-1.5 bg-cream-100 hover:bg-coral-500 hover:text-white px-3.5 py-2 rounded-full text-ink-950 transition">
                                <span class="text-[13px] font-extrabold">{{ $z->name }}</span>
                                @if($z->businesses_count)
                                    <span class="text-[10px] font-bold opacity-60">{{ $z->businesses_count }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
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
    }

    // ── Filter bottom sheet ─────────────────────────────────
    const btn   = document.getElementById('dir-filter-btn');
    const sheet = document.getElementById('dir-filter-sheet');

    if (btn && sheet) {
        const open  = () => { sheet.classList.add('open');    document.body.style.overflow = 'hidden'; };
        const close = () => { sheet.classList.remove('open'); document.body.style.overflow = ''; };

        btn.addEventListener('click', open);
        sheet.querySelectorAll('[data-close]').forEach((el) => el.addEventListener('click', close));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sheet.classList.contains('open')) close();
        });
    }
})();
</script>
@endpush
