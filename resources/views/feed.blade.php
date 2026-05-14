@extends('layouts.app', ['title' => 'بنهاوي · دليلك الكامل لمدينة بنها'])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ───── Top row: Greeting + notification bell ─────── --}}
    <div class="flex items-center justify-between gap-3 mb-4 rise rise-1">
        <h1 class="text-xl font-black text-ink-950 leading-tight truncate">
            @auth
                أهلاً {{ auth()->user()->username }}
            @else
                بنهاوي
            @endauth
        </h1>

        @auth
            @php $unread = \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->count(); @endphp
            <a href="{{ route('notifications.index') }}"
               class="relative w-10 h-10 rounded-full bg-coral-50 grid place-items-center text-coral-600 hover:bg-coral-100 transition shrink-0"
               aria-label="إشعارات">
                <x-icon name="bell" class="w-5 h-5"/>
                @if($unread > 0)
                    <span class="absolute -top-0.5 -end-0.5 min-w-[18px] h-[18px] rounded-full bg-coral-500 text-white text-[10px] font-extrabold grid place-items-center px-1 ring-2 ring-cream-100">
                        {{ $unread > 9 ? '9+' : $unread }}
                    </span>
                @endif
            </a>
        @endauth
    </div>
    {{-- Live search — debounced suggest dropdown --}}
    <div class="relative mb-6 rise rise-1" id="home-search" data-suggest-url="{{ route('search.suggest') }}">
        <form action="{{ route('search') }}" method="GET" class="flex items-center gap-2 bg-cream-200 rounded-full ps-5 pe-1.5 py-1.5 ring-1 ring-ink-950/5 focus-within:ring-coral-500/40 transition">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4 text-ink-400 shrink-0">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="search" name="q" autocomplete="off"
                   placeholder="ابحث في بنهاوي…"
                   class="flex-1 bg-transparent text-sm text-ink-950 placeholder-ink-400 outline-none border-0"
                   data-suggest-input>
            <button type="button" class="hidden text-ink-400 hover:text-ink-950 transition text-lg leading-none" data-suggest-clear aria-label="مسح">×</button>
            <button type="submit" class="w-9 h-9 rounded-full bg-coral-500 text-white grid place-items-center shrink-0 hover:bg-coral-600 transition" aria-label="ابحث">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </button>
        </form>

        {{-- Dropdown results --}}
        <div class="absolute inset-x-0 top-full mt-2 bg-white rounded-2xl ring-1 ring-ink-950/8 shadow-xl overflow-hidden z-30 hidden"
             data-suggest-panel>
            <div class="max-h-[60vh] overflow-y-auto" data-suggest-list></div>
            <a href="{{ route('search') }}"
               class="hidden items-center justify-center gap-2 px-4 py-3 text-xs font-extrabold text-coral-600 bg-cream-100 hover:bg-coral-50 transition border-t border-ink-950/5"
               data-suggest-all>
                شوف كل النتايج ←
            </a>
        </div>
    </div>

    {{-- ───── Promo banners — 4-card slider (map, QR menu, add biz, post ad) ──── --}}
      @if($promoBanners->isNotEmpty())
        <div class="mb-10 rise rise-2">
            <div class="promo-slider-wrap">
                <div class="promo-slider" data-auto-rotate="4500">
                    @foreach($promoBanners as $banner)
                        @include('partials.promo-banner', [
                            'href'    => $banner->href ?: '#',
                            'variant' => 'custom',
                            'tag'     => $banner->tag,
                            'title'   => $banner->title,
                            'desc'    => $banner->description,
                            'cta'     => $banner->cta_text,
                            'image'   => $banner->image_url,
                            'bgFrom'  => $banner->bg_from,
                            'bgTo'    => $banner->bg_to,
                        ])
                    @endforeach
                </div>
                <div class="promo-slider-dots" aria-hidden="true"></div>
            </div>
        </div>
    @endif

    {{-- ───── Categories — circle icons row ──────────────────────────── --}}
    <section class="mb-10 rise rise-3">
        <div class="flex items-center justify-between mb-4 px-1">
            <h2 class="text-xl font-black text-ink-950">الفئات</h2>
            <a href="{{ route('directory.index') }}"
               class="inline-flex items-center gap-2 text-sm font-extrabold text-ink-950 hover:text-coral-600 transition">
                شوف الكل
                <span class="w-9 h-9 rounded-full bg-coral-50 text-coral-600 grid place-items-center hover:bg-coral-100 transition">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </span>
            </a>
        </div>
        <div class="overflow-x-auto scrollbar-hide -mx-4 px-4 py-2">
            <div class="flex items-start gap-3 min-w-max">
                @foreach($homeCats as $cat)
                    <a href="{{ route('directory.category', $cat['key']) }}" class="cat-circle">
                        <span class="cat-circle-disc">
                            <x-icon :name="$cat['icon'] ?? 'bag'" class="w-7 h-7"/>
                        </span>
                        <span class="cat-circle-label">{{ $cat['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ───── Sponsored ────────────────────────────────────────────────── --}}
    <section class="mb-10 rise rise-2">
        <div class="flex items-center justify-between mb-4 px-1">
            <h2 class="text-xl font-black text-ink-950">مميّزة الأسبوع</h2>
            <a href="{{ route('directory.index') }}"
               class="inline-flex items-center gap-2 text-sm font-extrabold text-ink-950 hover:text-coral-600 transition">
                شوف الكل
                <span class="w-9 h-9 rounded-full bg-coral-50 text-coral-600 grid place-items-center hover:bg-coral-100 transition">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </span>
            </a>
        </div>

        @if($promoted->isNotEmpty())
            <div class="overflow-x-auto scrollbar-hide -mx-4 px-4 py-2">
                <div class="flex items-start gap-4 min-w-max">
                    @foreach($promoted as $b)
                        @php $cm = $b->categoryMeta(); @endphp
                        <a href="{{ route('directory.show', $b) }}" class="promoted-logo group">
                            <span class="promoted-logo-disc">
                                @if($b->photo_url)
                                    <img src="{{ $b->photo_url }}" alt="{{ $b->name }}" loading="lazy"
                                         onerror="this.style.display='none'">
                                @else
                                    <span class="promoted-logo-fallback"
                                          style="background: linear-gradient(135deg, {{ $cm['color'] ?? '#2D5BFF' }}, {{ $cm['color'] ?? '#2D5BFF' }}cc);">
                                        {{ mb_substr($b->name, 0, 1) }}
                                    </span>
                                @endif
                            </span>
                            <span class="promoted-logo-label">{{ $b->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Advertiser slot (empty state = pitch) --}}
            <a href="{{ Auth::check() ? route('directory.create') : route('signup') }}"
               class="block relative overflow-hidden rounded-2xl p-5 ring-1 ring-honey-500/30 hover:ring-honey-500/60 transition group"
               style="background: #EEF2FF;">
                <div class="absolute -top-10 -end-10 w-40 h-40 rounded-full bg-honey-400/30 blur-2xl pulse-soft pointer-events-none"></div>
                <div class="relative flex items-center gap-3">
                    <span class="w-12 h-12 rounded-2xl grid place-items-center text-white shadow-lg shadow-honey-500/40"
                          style="background: #2D5BFF;">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                            <polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/>
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-extrabold text-ink-950">حِجز إعلانك هنا</div>
                        <div class="text-[11px] text-ink-500 mt-0.5 leading-snug">
                            نشاطك يطلع في الصفحة الرئيسية لكل زوار بنهاوي
                        </div>
                    </div>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-5 h-5 text-honey-700 shrink-0 group-hover:-translate-x-1 transition">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </div>
            </a>
        @endif
    </section>

    {{-- ───── 2) Top rated ─────────────────────────────────────────────── --}}
    @if($featured->isNotEmpty())
        <section class="rise rise-3 mb-10">
            <div class="flex items-center justify-between mb-4 px-1">
                <h2 class="text-xl font-black text-ink-950">الأكتر تقييم في بنها</h2>
                <a href="{{ route('directory.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-extrabold text-ink-950 hover:text-coral-600 transition">
                    شوف الكل
                    <span class="w-9 h-9 rounded-full bg-coral-50 text-coral-600 grid place-items-center hover:bg-coral-100 transition">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </span>
                </a>
            </div>

            <div class="biz-card-scroll">
                @foreach($featured as $b)
                    @include('directory.partials.biz-card', ['business' => $b])
                @endforeach
            </div>
        </section>
    @endif
    {{-- ───── Promo banners — 4-card slider (map, QR menu, add biz, post ad) ──── --}}
    <div class="mb-10 rise rise-2">
        <div class="promo-slider-wrap">
            <div class="promo-slider" data-auto-rotate="4500">
                @include('partials.promo-banner', [
                    'href'    => route('directory.map'),
                    'variant' => 'map',
                    'tag'     => 'جديد · خريطة بنها',
                    'title'   => 'لاقي أحسن أماكن بنها قربك',
                    'desc'    => 'مطاعم، صيدليات، خدمات — كلهم على خريطة واحدة.',
                    'cta'     => 'افتح الخريطة',
                ])
                @include('partials.promo-banner', [
                    'href'    => Auth::check() ? route('directory.mine') : route('signup'),
                    'variant' => 'menu',
                    'tag'     => 'جديد · منيو رقمي',
                    'title'   => 'منيو نشاطك على QR',
                    'desc'    => 'حدّث الأسعار في ثانية، الضيف يقرا المنيو من موبايله.',
                    'cta'     => 'جرّب QR Menu',
                ])
                @include('partials.promo-banner', [
                    'href'    => Auth::check() ? route('directory.create') : route('signup'),
                    'variant' => 'add',
                    'tag'     => 'مجاناً · أضف نشاطك',
                    'title'   => 'نشاطك في بنهاوي',
                    'desc'    => 'ضيف مكانك يطلع للناس اللي بتدوّر في بنها.',
                    'cta'     => 'ضيف نشاطك',
                ])
                @include('partials.promo-banner', [
                    'href'    => Auth::check() ? route('marketplace.create') : route('signup'),
                    'variant' => 'ad',
                    'tag'     => 'سوق · إعلانات',
                    'title'   => 'بيع، اشتري، إعلن',
                    'desc'    => 'انشر إعلانك في سوق بنها ووصلّه لآلاف الزوار.',
                    'cta'     => 'انشر إعلان',
                ])
            </div>
            <div class="promo-slider-dots" aria-hidden="true"></div>
        </div>
    </div>


    {{-- ───── 3) Open now ──────────────────────────────────────────────── --}}
    @if($openNow->isNotEmpty())
        <section class="rise rise-4">
            <div class="flex items-center justify-between mb-4 px-1">
                <h2 class="text-xl font-black text-ink-950 inline-flex items-center gap-2">
                    مفتوح دلوقتي
                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-mint-100 text-mint-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-mint-500 pulse-soft"></span>
                        LIVE
                    </span>
                </h2>
                <a href="{{ route('directory.index', ['open_now' => 1]) }}"
                   class="inline-flex items-center gap-2 text-sm font-extrabold text-ink-950 hover:text-coral-600 transition">
                    شوف الكل
                    <span class="w-9 h-9 rounded-full bg-coral-50 text-coral-600 grid place-items-center hover:bg-coral-100 transition">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-4 h-4">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                    </span>
                </a>
            </div>

            <div class="biz-card-scroll">
                @foreach($openNow as $b)
                    @include('directory.partials.biz-card', ['business' => $b])
                @endforeach
            </div>
        </section>
    @endif

</div>

@push('scripts')
<script>
    function initPromoSlider(slider) {
        const cards = Array.from(slider.children).filter(el => el.classList.contains('promo-card'));
        if (cards.length < 1) return;

        // Build pagination dots even for a single card so the layout matches across sliders
        const dotsHost = slider.parentElement && slider.parentElement.querySelector('.promo-slider-dots');
        if (dotsHost) {
            dotsHost.innerHTML = '';
            cards.forEach((_, i) => {
                const d = document.createElement('button');
                d.type = 'button';
                d.className = 'promo-slider-dot' + (i === 0 ? ' is-active' : '');
                d.setAttribute('aria-label', 'Slide ' + (i + 1));
                dotsHost.appendChild(d);
            });
        }

        // Only single card — no auto-rotation, no scroll wiring needed
        if (cards.length < 2) return;

        const interval    = parseInt(slider.dataset.autoRotate, 10) || 4500;
        const slideMs     = 700;
        const resumeAfter = 8000;
        let   idx       = 0;
        let   timer     = null;
        let   pauseT    = null;
        let   animating = false;

        const ease = t => t < .5 ? 4*t*t*t : 1 - Math.pow(-2*t + 2, 3) / 2;
        const targetLeftFor = (card) => card.offsetLeft - (slider.clientWidth - card.offsetWidth) / 2;

        // Hook click handlers on the dots we already created above
        const dots = dotsHost ? Array.from(dotsHost.querySelectorAll('.promo-slider-dot')) : [];
        dots.forEach((d, i) => d.addEventListener('click', () => { pauseAndResume(); slideTo(i); }));

        const setActive = (i) => {
            idx = (i + cards.length) % cards.length;
            dots.forEach((d, j) => d.classList.toggle('is-active', j === idx));
        };

        const slideTo = (i) => {
            const next = (i + cards.length) % cards.length;
            const target = cards[next];
            const from   = slider.scrollLeft;
            const to     = targetLeftFor(target);
            setActive(next);
            if (Math.abs(to - from) < 1) return;

            animating = true;
            const prevSnap = slider.style.scrollSnapType;
            slider.style.scrollSnapType = 'none';

            const startTime = performance.now();
            const step = (now) => {
                const t = Math.min((now - startTime) / slideMs, 1);
                slider.scrollLeft = from + (to - from) * ease(t);
                if (t < 1) {
                    requestAnimationFrame(step);
                } else {
                    slider.style.scrollSnapType = prevSnap;
                    animating = false;
                }
            };
            requestAnimationFrame(step);
        };

        const start = () => {
            stop();
            timer = setInterval(() => { if (!animating) slideTo(idx + 1); }, interval);
        };
        const stop = () => { if (timer) { clearInterval(timer); timer = null; } };

        const pauseAndResume = () => {
            stop();
            clearTimeout(pauseT);
            pauseT = setTimeout(start, resumeAfter);
        };

        slider.addEventListener('pointerdown', pauseAndResume);
        slider.addEventListener('touchstart',  pauseAndResume, { passive: true });
        slider.addEventListener('wheel',       pauseAndResume, { passive: true });

        let scrollT;
        slider.addEventListener('scroll', () => {
            if (animating) return;
            clearTimeout(scrollT);
            scrollT = setTimeout(() => {
                const center = slider.scrollLeft + slider.clientWidth / 2;
                let best = 0, bestDist = Infinity;
                cards.forEach((c, i) => {
                    const cCenter = c.offsetLeft + c.offsetWidth / 2;
                    const d = Math.abs(center - cCenter);
                    if (d < bestDist) { bestDist = d; best = i; }
                });
                setActive(best);
            }, 120);
        }, { passive: true });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) stop(); else start();
        });

        start();
    }

    document.querySelectorAll('.promo-slider[data-auto-rotate]').forEach(initPromoSlider);
</script>
@endpush
@endsection
