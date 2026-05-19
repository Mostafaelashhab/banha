@extends('layouts.app', [
    'title'       => 'بنهاوي | كل حاجة في بنها: مطاعم، دكاترة، صيدليات، عروض وبيع وشراء',
    'description' => 'دور على مطاعم، دكاترة، صيدليات، صنايعية، عروض، أرقام طوارئ، وبيع وشراء في بنها والقليوبية. اتصل، ابعت واتساب، وشوف الاتجاهات بسهولة.',
    'keywords'    => 'بنها, القليوبية, مطاعم بنها, دكاترة بنها, صيدليات بنها, صنايعية بنها, عروض بنها, طوارئ بنها, بيع وشراء بنها, جامعة بنها, دليل بنها',
])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ───── Top row: Greeting + notification bell ─────── --}}
    <div class="flex items-center justify-between gap-3 mb-4 rise rise-1">
        <div class="min-w-0">
            @auth
                <div class="text-[11px] font-bold text-ink-500">أهلاً {{ auth()->user()->username }}</div>
            @endauth
            <h1 class="text-xl font-black text-ink-950 leading-tight">
                بتدور على إيه في بنها؟
            </h1>
        </div>

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
    <div class="relative mb-4 rise rise-1" id="home-search" data-suggest-url="{{ route('search.suggest') }}">
        <form action="{{ route('search') }}" method="GET" class="flex items-center gap-2 bg-cream-200 rounded-full ps-5 pe-1.5 py-2 ring-1 ring-ink-950/5 focus-within:ring-coral-500/40 transition">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4 text-ink-400 shrink-0">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="search" name="q" autocomplete="off"
                   placeholder="مطعم، دكتور، صيدلية، صنايعي، شقة، عرض، وظيفة…"
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

    {{-- ───── Utility shortcuts — horizontal slider (uniform blue cards) ─────── --}}
    @php
        $utilityShortcuts = [
            ['route' => route('craftsmen.index'),        'label' => 'صنايعية',      'icon' => 'wrench'],
            ['route' => route('offers.index'),           'label' => 'عروض',         'icon' => 'tag'],
            ['route' => route('bookings.index'),         'label' => 'احجز موعد',    'icon' => 'check'],
            ['route' => route('open-now.index'),         'label' => 'مفتوح دلوقتي', 'icon' => 'bell'],
            ['route' => route('jobs.index'),             'label' => 'وظايف',         'icon' => 'briefcase'],
            ['route' => route('trains.index'),           'label' => 'القطارات',     'icon' => 'train'],
            ['route' => route('lost-found.index'),       'label' => 'مفقودات',      'icon' => 'search'],
            ['route' => route('emergency.index'),        'label' => 'طوارئ',         'icon' => 'bolt'],
            ['route' => route('university.index'),       'label' => 'الجامعة',      'icon' => 'graduation'],
            ['route' => route('marketplace.index'),      'label' => 'سوق',          'icon' => 'bag'],
        ];
    @endphp
    <div class="mb-6 -mx-4 px-4 rise rise-1">
        <div class="flex gap-2 overflow-x-auto scrollbar-hide snap-x snap-mandatory pb-1"
             style="scroll-padding-inline-start: 1rem;">
            @foreach($utilityShortcuts as $s)
                <a href="{{ $s['route'] }}"
                   class="shrink-0 w-20 snap-start flex flex-col items-center gap-1.5 py-3 rounded-2xl bg-coral-50 hover:bg-coral-100 transition">
                    <span class="w-11 h-11 rounded-full bg-white text-coral-600 grid place-items-center shadow-sm">
                        <x-icon :name="$s['icon']" class="w-5 h-5"/>
                    </span>
                    <span class="text-[10px] font-extrabold text-ink-950 text-center leading-tight px-1">{{ $s['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ───── Popular searches — single-tap intents ─────── --}}
    @if(! empty($popularSearches))
        <div class="mb-6 rise rise-2">
            <div class="text-[11px] font-bold text-ink-500 mb-2 px-1">الأكثر بحثًا</div>
            <div class="overflow-x-auto scrollbar-hide -mx-4 px-4">
                <div class="flex items-center gap-2 min-w-max">
                    @foreach($popularSearches as $term)
                        <a href="{{ route('search', ['q' => $term]) }}"
                           class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white ring-1 ring-ink-950/8 text-[12px] font-extrabold text-ink-950 hover:ring-coral-500/40 hover:text-coral-600 transition">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-3 h-3 text-ink-400">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            {{ $term }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ───── Promo banners — 4-card slider (map, QR menu, add biz, post ad) ──── --}}
      @if($promoBanners->isNotEmpty())
        <div class="mb-10 rise rise-2">
            <div class="promo-slider-wrap">
                <div class="promo-slider" data-auto-rotate="4500">
                    @foreach($promoBanners as $banner)
                        @include('partials.promo-banner', [
                            'href'      => $banner->destinationUrl(),
                            'variant'   => 'custom',
                            'imageOnly' => $banner->isImageOnly(),
                            'alt'       => $banner->business?->name ?: $banner->title,
                            'tag'       => $banner->tag,
                            'title'     => $banner->title,
                            'desc'      => $banner->description,
                            'cta'       => $banner->cta_text,
                            'image'     => $banner->image_url,
                            'bgFrom'    => $banner->bg_from,
                            'bgTo'      => $banner->bg_to,
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
                <x-icon-tile icon="chevron-left" shape="circle" size="md"/>
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
                <x-icon-tile icon="chevron-left" shape="circle" size="md"/>
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
                    <x-icon name="chevron-left" class="w-5 h-5 text-honey-700 shrink-0 group-hover:-translate-x-1 transition"/>
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
                    <x-icon-tile icon="chevron-left" shape="circle" size="md"/>
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
                    'title'   => 'بيع، اشترِ، أعلن',
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
                    <x-icon-tile icon="chevron-left" shape="circle" size="md"/>
                </a>
            </div>

            <div class="biz-card-scroll">
                @foreach($openNow as $b)
                    @include('directory.partials.biz-card', ['business' => $b])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ───── جديد في السوق — newest marketplace listings ─────── --}}
    @if($newListings->isNotEmpty())
        <section class="rise rise-4 mt-10">
            <div class="flex items-center justify-between mb-4 px-1">
                <h2 class="text-xl font-black text-ink-950">جديد في السوق</h2>
                <a href="{{ route('marketplace.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-extrabold text-ink-950 hover:text-coral-600 transition">
                    شوف الكل
                    <x-icon-tile icon="chevron-left" shape="circle" size="md"/>
                </a>
            </div>

            <div class="overflow-x-auto scrollbar-hide -mx-4 px-4">
                <div class="flex items-stretch gap-3 min-w-max">
                    @foreach($newListings as $l)
                        @php
                            $cm = $l->categoryMeta();
                            $km = $l->kindMeta();
                            $priceText = $l->priceLabel();
                            // Only force LTR when the price is numeric — Arabic phrases
                            // like "بسعر مفاوض" would otherwise render misaligned.
                            $priceIsNumeric = $priceText !== '' && preg_match('/\d/', $priceText);
                        @endphp
                        <a href="{{ route('marketplace.show', $l) }}"
                           class="shrink-0 w-44 bg-white rounded-2xl ring-1 ring-ink-950/8 overflow-hidden hover:ring-coral-500/40 transition flex flex-col">
                            <div class="aspect-square bg-coral-50 relative">
                                {{-- Fallback first so the <img> overlays it when loaded. --}}
                                <div class="absolute inset-0 grid place-items-center text-coral-600/40">
                                    <x-icon :name="$cm['icon'] ?? 'bag'" class="w-12 h-12"/>
                                </div>
                                @if($l->photo_url)
                                    <img src="{{ $l->photo_url }}" alt="{{ $l->title }}" loading="lazy"
                                         class="absolute inset-0 w-full h-full object-cover"
                                         onerror="this.style.display='none'">
                                @endif
                                @if($l->kind && $l->kind !== 'sale')
                                    <span class="absolute top-1.5 start-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/95 text-[9px] font-extrabold text-{{ $km['tone'] ?? 'coral' }}-600 backdrop-blur">
                                        {{ $km['label'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="p-2.5 flex-1 flex flex-col">
                                <div class="text-[12px] font-extrabold text-ink-950 line-clamp-2 leading-snug">{{ $l->title }}</div>
                                @if($priceText !== '')
                                    <div class="text-[13px] font-black text-coral-600 mt-1" @if($priceIsNumeric) dir="ltr" @endif>
                                        {{ $priceText }}
                                    </div>
                                @endif
                                @if($l->zone)
                                    <div class="text-[10px] text-ink-500 mt-auto pt-1 truncate inline-flex items-center gap-1">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5 shrink-0">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        {{ $l->zone->name }}
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ───── دليل طلاب جامعة بنها — student hub promo ─────── --}}
    <a href="{{ route('university.index') }}" class="block mt-6 rounded-3xl p-4 relative overflow-hidden ring-1 ring-honey-500/20 hover:ring-honey-500/40 transition rise rise-4"
       style="background: #FFF6E6;">
        <div class="absolute -top-12 -end-12 w-48 h-48 rounded-full bg-honey-400/30 blur-3xl pointer-events-none"></div>
        <div class="relative flex items-center gap-3">
            <span class="w-12 h-12 rounded-2xl bg-white grid place-items-center text-2xl shrink-0">🎓</span>
            <div class="flex-1 min-w-0">
                <div class="text-[10px] font-extrabold text-honey-700 mb-0.5">للطلاب</div>
                <div class="text-sm font-black text-ink-950 leading-tight">دليل طلاب جامعة بنها</div>
                <div class="text-[11px] text-ink-500 mt-0.5 leading-snug truncate">أكل، سكن، مكتبات، كورسات، Part-time.</div>
            </div>
            <x-icon name="chevron-left" class="w-4 h-4 text-honey-700 shrink-0"/>
        </div>
    </a>

    {{-- ───── Business owner CTA — claim your page ─────── --}}
    <a href="{{ route('marketing.claim') }}" class="block mt-3 rounded-3xl p-4 relative overflow-hidden rise rise-4"
       style="background: #1F46DB;">
        <div class="absolute -bottom-16 -start-16 w-56 h-56 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -top-10 -end-10 w-40 h-40 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="relative flex items-center gap-3">
            <span class="w-12 h-12 rounded-2xl bg-white/15 grid place-items-center text-white shrink-0">
                {{-- Storefront icon (not a house) — speaks to shop owners --}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                    <path d="M3 9h18l-1.5-5h-15z"/>
                    <path d="M4 9v11a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V9"/>
                    <path d="M8 14h8"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0 text-white">
                <div class="text-[10px] font-extrabold text-white/80 mb-0.5">لأصحاب النشاطات</div>
                <div class="text-sm font-black leading-tight">عندك محل أو مطعم؟ امتلك صفحتك</div>
                <div class="text-[11px] text-white/85 mt-0.5 leading-snug font-bold truncate">واتساب، صور، منيو، عروض — كله من مكان واحد.</div>
            </div>
            <x-icon name="chevron-left" class="w-4 h-4 text-white shrink-0"/>
        </div>
    </a>

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
