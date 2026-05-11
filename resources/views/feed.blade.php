@extends('layouts.app', ['title' => 'بنهاوي · دليلك الكامل لمدينة بنها'])

@php
    use App\Models\Business;

    // Sponsored (paid) — appears first
    $promoted = Business::query()
        ->where('is_active', true)
        ->where('promoted_until', '>', now())
        ->with(['zone:id,name', 'photos:id,business_id,url'])
        ->orderByDesc('promoted_until')
        ->limit(6)
        ->get();

    // Top rated — verified or 4+ stars (photo not required; we fall back per category)
    $featured = Business::query()
        ->where('is_active', true)
        ->where(function ($q) {
            $q->where('is_verified', true)->orWhere('rating_avg', '>=', 4);
        })
        ->with(['zone:id,name', 'photos:id,business_id,url'])
        ->orderByDesc('is_verified')
        ->orderByDesc('rating_avg')
        ->orderByDesc('views_count')
        ->limit(12)
        ->get();

    // Main 6 categories for the home grid
    $homeCatKeys = ['food', 'medical', 'shops', 'services', 'transport', 'education'];
    $homeCats    = collect($homeCatKeys)->map(fn ($k) => ['key' => $k] + (Business::CATEGORIES[$k] ?? []));
    $catCounts   = Business::where('is_active', true)
        ->whereIn('category', $homeCatKeys)
        ->selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category')->all();

@endphp

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ───── Greeting line — personal, friendly ─────────────────────── --}}
    <div class="mb-4 rise rise-1">
        <h1 class="text-2xl font-black text-ink-950 leading-tight">
            @auth
                أهلاً يا {{ auth()->user()->username }}!
            @else
                أهلاً بيك في بنهاوي!
            @endauth
        </h1>
        <p class="text-ink-500 text-sm mt-1">إيه أخبارك النهارده؟</p>
    </div>

    {{-- ───── Search ─────────────────────────────────────────────────── --}}
    <div class="mb-5 rise rise-1">
        <a href="{{ route('search') }}"
           class="relative flex items-center gap-2.5 bg-white rounded-2xl px-4 py-3.5 ring-1 ring-ink-950/8 hover:ring-coral-500/40 hover:shadow-lg transition group overflow-hidden">
            <span class="search-shine"></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-5 h-5 text-ink-400 shrink-0 relative">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <span class="text-sm text-ink-500 flex-1 truncate relative">دوّر على نشاط، صنايعي، أو دكتور…</span>
            <span class="relative text-[11px] font-extrabold bg-coral-500 text-white px-3 py-1.5 rounded-xl group-hover:bg-coral-600 transition shadow-sm shadow-coral-500/30">ابحث</span>
        </a>
    </div>

    {{-- ───── Promo banners — 4-card slider (map, QR menu, add biz, post ad) ──── --}}
    <div class="mb-7 rise rise-2">
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
    </div>

    {{-- ───── Categories — circle icons row ──────────────────────────── --}}
    <section class="mb-7 rise rise-3">
        <div class="flex items-center justify-between mb-3 px-1">
            <h2 class="text-base font-extrabold text-ink-950">الفئات</h2>
            <a href="{{ route('directory.index') }}" class="text-xs font-bold text-coral-600">شوف الكل ←</a>
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
    <section class="mb-7 rise rise-2">
        <div class="flex items-center justify-between mb-3 px-1">
            <h2 class="text-base font-extrabold text-ink-950 inline-flex items-center gap-1.5">
                <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-honey-500">
                    <polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/>
                </svg>
                مميّزة الأسبوع
            </h2>
            @if($promoted->isNotEmpty())
                <span class="text-[10px] font-bold text-honey-700 bg-honey-100 px-2 py-1 rounded-full">إعلانات</span>
            @endif
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
                                          style="background: linear-gradient(135deg, {{ $cm['color'] ?? '#FF7A4D' }}, {{ $cm['color'] ?? '#FF7A4D' }}cc);">
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
               style="background: linear-gradient(135deg, #FFF7E9, #FFE4C2);">
                <div class="absolute -top-10 -end-10 w-40 h-40 rounded-full bg-honey-400/30 blur-2xl pulse-soft pointer-events-none"></div>
                <div class="relative flex items-center gap-3">
                    <span class="w-12 h-12 rounded-2xl grid place-items-center text-white shadow-lg shadow-honey-500/40"
                          style="background: linear-gradient(135deg, #FFB85C, #FF9F2D);">
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
        <section class="mb-4 rise rise-3">
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-extrabold text-ink-950 inline-flex items-center gap-1.5">
                    <span class="text-coral-500">★</span> الأكتر تقييم في بنها
                </h2>
                <a href="{{ route('directory.index') }}" class="text-xs font-bold text-coral-600">شوف الكل ←</a>
            </div>

            <div class="biz-card-scroll">
                @foreach($featured as $b)
                    @include('directory.partials.biz-card', ['business' => $b])
                @endforeach
            </div>
        </section>
    @endif

</div>

@push('scripts')
<script>
    (() => {
        const slider = document.querySelector('.promo-slider[data-auto-rotate]');
        if (!slider) return;

        const cards = Array.from(slider.children).filter(el => el.classList.contains('promo-card'));
        if (cards.length < 2) return;

        const interval    = parseInt(slider.dataset.autoRotate, 10) || 4500;
        const slideMs     = 700;
        const resumeAfter = 8000;
        let   idx       = 0;
        let   timer     = null;
        let   pauseT    = null;
        let   animating = false;

        // ease-in-out cubic
        const ease = t => t < .5 ? 4*t*t*t : 1 - Math.pow(-2*t + 2, 3) / 2;

        const targetLeftFor = (card) =>
            card.offsetLeft - (slider.clientWidth - card.offsetWidth) / 2;

        // Smooth scroll with snap temporarily disabled so animation isn't interrupted
        const slideTo = (i) => {
            idx = (i + cards.length) % cards.length;
            const target = cards[idx];
            const from   = slider.scrollLeft;
            const to     = targetLeftFor(target);
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

        // Sync idx when user scrolls manually
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
                idx = best;
            }, 120);
        }, { passive: true });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) stop(); else start();
        });

        start();
    })();
</script>
@endpush
@endsection
