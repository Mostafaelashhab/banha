@extends('layouts.app', ['title' => 'بنهاوي · دليلك الكامل لمدينة بنها'])

@php
    use App\Models\Business;

    // Sponsored (paid) — appears first
    $promoted = Business::query()
        ->where('is_active', true)
        ->where('promoted_until', '>', now())
        ->with('zone:id,name')
        ->orderByDesc('promoted_until')
        ->limit(6)
        ->get();

    // Top rated — verified or 4+ stars, with a real photo
    $featured = Business::query()
        ->where('is_active', true)
        ->whereNotNull('photo_url')
        ->where('photo_url', '!=', '')
        ->where(function ($q) {
            $q->where('is_verified', true)->orWhere('rating_avg', '>=', 4);
        })
        ->with('zone:id,name')
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

    $totalOnMap = Business::where('is_active', true)
        ->whereNotNull('lat')->whereNotNull('lng')->count();
@endphp

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ───── Greeting line — personal, friendly ─────────────────────── --}}
    <div class="flex items-start justify-between mb-4 rise rise-1">
        <div>
            <h1 class="text-2xl font-black text-ink-950 leading-tight">
                @auth
                    أهلاً يا {{ auth()->user()->username }}!
                @else
                    أهلاً بيك في بنهاوي!
                @endauth
            </h1>
            <p class="text-ink-500 text-sm mt-1">إيه أخبارك النهارده؟</p>
        </div>
        @auth
            @php $unread = \App\Models\Notification::where('user_id', auth()->id())->whereNull('read_at')->count(); @endphp
            <a href="{{ route('notifications.index') }}" class="relative w-11 h-11 rounded-full bg-white ring-1 ring-ink-950/8 grid place-items-center text-ink-700 hover:text-coral-600 hover:ring-coral-500/30 transition shrink-0 mt-1">
                <x-icon name="bell" class="w-5 h-5"/>
                @if($unread > 0)
                    <span class="absolute -top-0.5 -end-0.5 min-w-[18px] h-[18px] rounded-full bg-coral-500 text-white text-[10px] font-extrabold grid place-items-center px-1 ring-2 ring-cream-100">
                        {{ $unread > 9 ? '9+' : $unread }}
                    </span>
                @endif
            </a>
        @endauth
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

    {{-- ───── Promo card — hero with CTA + decorative art ────────────── --}}
    <a href="{{ route('directory.map') }}" class="promo-card mb-7 rise rise-2 group">
        <span class="promo-card-glow"></span>

        {{-- Decorative city-map illustration (SVG, scales with the card) --}}
        <svg class="promo-card-art" viewBox="0 0 160 160" fill="none" aria-hidden="true">
            <defs>
                <linearGradient id="promo-pin-grad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#fff" stop-opacity=".95"/>
                    <stop offset="1" stop-color="#fff" stop-opacity=".6"/>
                </linearGradient>
            </defs>
            {{-- soft sun/halo --}}
            <circle cx="120" cy="42" r="32" fill="rgba(255,255,255,.18)"/>
            <circle cx="120" cy="42" r="20" fill="rgba(255,255,255,.28)"/>
            {{-- skyline silhouettes --}}
            <g fill="rgba(255,255,255,.85)" stroke="rgba(11,11,12,.05)">
                <rect x="22"  y="78"  width="22" height="64" rx="3"/>
                <rect x="48"  y="62"  width="26" height="80" rx="3"/>
                <rect x="78"  y="86"  width="18" height="56" rx="3"/>
                <rect x="100" y="70"  width="22" height="72" rx="3"/>
                <rect x="126" y="92"  width="22" height="50" rx="3"/>
            </g>
            {{-- windows --}}
            <g fill="#FF7A4D" opacity=".75">
                <rect x="28"  y="86"  width="3" height="3"/>
                <rect x="35"  y="86"  width="3" height="3"/>
                <rect x="28"  y="96"  width="3" height="3"/>
                <rect x="35"  y="96"  width="3" height="3"/>
                <rect x="55"  y="72"  width="3" height="3"/>
                <rect x="62"  y="72"  width="3" height="3"/>
                <rect x="55"  y="82"  width="3" height="3"/>
                <rect x="62"  y="82"  width="3" height="3"/>
                <rect x="55"  y="92"  width="3" height="3"/>
                <rect x="62"  y="92"  width="3" height="3"/>
                <rect x="106" y="80"  width="3" height="3"/>
                <rect x="113" y="80"  width="3" height="3"/>
                <rect x="106" y="92"  width="3" height="3"/>
                <rect x="113" y="92"  width="3" height="3"/>
            </g>
            {{-- floating map pin --}}
            <g transform="translate(70 38)">
                <path d="M0 22c-12-9-18-17-18-26a18 18 0 0 1 36 0c0 9-6 17-18 26Z" fill="url(#promo-pin-grad)" stroke="rgba(11,11,12,.08)"/>
                <circle r="6" cx="0" cy="-5" fill="#FF7A4D"/>
            </g>
        </svg>

        <div class="relative max-w-[60%]">
            <div class="text-[10px] font-extrabold tracking-wider uppercase opacity-80 mb-1.5">جديد · خريطة بنها</div>
            <div class="font-black text-lg leading-tight">
                لاقي أحسن أماكن بنها قربك
            </div>
            <p class="text-white/90 text-[12px] mt-1 leading-snug font-bold">
                مطاعم، صيدليات، خدمات — كلهم على خريطة واحدة.
            </p>
        </div>

        <span class="promo-card-cta">
            افتح الخريطة
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3.5 h-3.5">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </span>
    </a>

    {{-- ───── Categories — circle icons row ──────────────────────────── --}}
    <section class="mb-7 rise rise-3">
        <div class="flex items-center justify-between mb-3 px-1">
            <h2 class="text-base font-extrabold text-ink-950">الفئات</h2>
            <a href="{{ route('directory.index') }}" class="text-xs font-bold text-coral-600">شوف الكل ←</a>
        </div>
        <div class="overflow-x-auto scrollbar-hide -mx-4 px-4">
            <div class="flex items-start gap-3 min-w-max">
                @foreach($homeCats as $cat)
                    @php $count = $catCounts[$cat['key']] ?? 0; $color = $cat['color'] ?? '#FF7A4D'; @endphp
                    <a href="{{ route('directory.category', $cat['key']) }}" class="cat-circle"
                       style="--cat-shadow: {{ $color }}40;">
                        <span class="cat-circle-disc"
                              style="background: linear-gradient(135deg, {{ $color }}18, {{ $color }}30); color: {{ $color }};">
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
            <div class="feat-scroll -mx-4 px-4">
                @foreach($promoted as $b)
                    @php $cm = $b->categoryMeta(); $rating = (float) ($b->rating_avg ?? 0); @endphp
                    <a href="{{ route('directory.show', $b) }}" class="feat-card shrink-0 group">
                        @if($b->photo_url)
                            <img src="{{ $b->photo_url }}" alt="{{ $b->name }}" loading="lazy"
                                 onerror="this.style.display='none'"
                                 class="group-hover:scale-105 transition duration-500">
                        @else
                            <div class="absolute inset-0 grid place-items-center text-white text-4xl font-black"
                                 style="background: linear-gradient(135deg, {{ $cm['color'] ?? '#FF7A4D' }}, {{ $cm['color'] ?? '#FF7A4D' }}cc);">
                                {{ mb_substr($b->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="feat-card-overlay"></div>

                        <div class="absolute top-3 start-3 flex flex-col gap-1.5">
                            <span class="inline-flex items-center gap-1 bg-honey-500 text-ink-950 text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-sm">
                                ★ مميّز
                            </span>
                            @if($b->is_verified)
                                <span class="inline-flex items-center gap-1 bg-mint-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-sm">
                                    <x-icon name="check" class="w-2.5 h-2.5"/> موثّق
                                </span>
                            @endif
                        </div>

                        @if($rating >= 4)
                            <div class="absolute top-3 end-3 inline-flex items-center gap-0.5 bg-white/95 backdrop-blur-sm text-ink-950 text-[11px] font-extrabold px-2 py-1 rounded-full shadow-sm">
                                <svg viewBox="0 0 24 24" fill="#FF7A4D" class="w-3 h-3"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                                {{ number_format($rating, 1) }}
                            </div>
                        @endif

                        <div class="feat-card-body">
                            <div class="inline-flex items-center gap-1 bg-white/15 backdrop-blur-sm border border-white/25 rounded-full px-2 py-0.5 text-[10px] font-extrabold mb-1.5">
                                <x-icon :name="$cm['icon'] ?? 'bag'" class="w-2.5 h-2.5"/>
                                {{ $cm['label'] ?? '' }}
                            </div>
                            <div class="font-black text-base leading-tight line-clamp-2 drop-shadow">{{ $b->name }}</div>
                            @if($b->zone)
                                <div class="text-white/85 text-[11px] mt-1 inline-flex items-center gap-1">
                                    <x-icon name="map-pin" class="w-3 h-3"/>
                                    {{ $b->zone->name }}
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
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

            {{-- First 3 as a horizontal carousel (visual hook), the rest as compact rows --}}
            @if($featured->count() > 0)
                <div class="feat-scroll -mx-4 px-4 mb-3">
                    @foreach($featured->take(6) as $b)
                        @php $cm = $b->categoryMeta(); $rating = (float) ($b->rating_avg ?? 0); @endphp
                        <a href="{{ route('directory.show', $b) }}" class="feat-card shrink-0 group">
                            <img src="{{ $b->photo_url }}" alt="{{ $b->name }}" loading="lazy"
                                 onerror="this.style.display='none'"
                                 class="group-hover:scale-105 transition duration-500">
                            <div class="feat-card-overlay"></div>

                            @if($b->is_verified)
                                <div class="absolute top-3 start-3">
                                    <span class="inline-flex items-center gap-1 bg-mint-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-sm">
                                        <x-icon name="check" class="w-2.5 h-2.5"/> موثّق
                                    </span>
                                </div>
                            @endif

                            @if($rating >= 4)
                                <div class="absolute top-3 end-3 inline-flex items-center gap-0.5 bg-white/95 backdrop-blur-sm text-ink-950 text-[11px] font-extrabold px-2 py-1 rounded-full shadow-sm">
                                    <svg viewBox="0 0 24 24" fill="#FF7A4D" class="w-3 h-3"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                                    {{ number_format($rating, 1) }}
                                </div>
                            @endif

                            <div class="feat-card-body">
                                <div class="inline-flex items-center gap-1 bg-white/15 backdrop-blur-sm border border-white/25 rounded-full px-2 py-0.5 text-[10px] font-extrabold mb-1.5">
                                    <x-icon :name="$cm['icon'] ?? 'bag'" class="w-2.5 h-2.5"/>
                                    {{ $cm['label'] ?? '' }}
                                </div>
                                <div class="font-black text-base leading-tight line-clamp-2 drop-shadow">{{ $b->name }}</div>
                                @if($b->zone)
                                    <div class="text-white/85 text-[11px] mt-1 inline-flex items-center gap-1">
                                        <x-icon name="map-pin" class="w-3 h-3"/>
                                        {{ $b->zone->name }}
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($featured->count() > 6)
                    <div class="space-y-2.5">
                        @foreach($featured->slice(6, 6) as $b)
                            @include('directory.partials.business-row', ['business' => $b])
                        @endforeach
                    </div>
                @endif
            @endif
        </section>
    @endif

    {{-- ───── Map preview card ─────────────────────────────────────────── --}}
    <section class="mb-7 rise rise-5">
        <a href="{{ route('directory.map') }}"
           class="relative block overflow-hidden rounded-3xl ring-1 ring-ink-950/8 hover:ring-mint-500/40 hover:shadow-xl transition group"
           style="background: linear-gradient(135deg, #1FA857 0%, #10B981 100%);">

            {{-- Map tile background, blurred & dimmed so the card stays calm --}}
            <div class="absolute inset-0 opacity-30 group-hover:opacity-40 transition"
                 style="background-image: url('https://tile.openstreetmap.org/13/4731/3294.png');
                        background-size: cover;
                        background-position: center;
                        filter: saturate(.5) hue-rotate(110deg);"></div>
            <div class="absolute inset-0 bg-gradient-to-tr from-mint-700/80 via-mint-600/55 to-transparent"></div>

            {{-- Decorative pins drifting --}}
            <div class="hero-blob absolute top-6 end-12 w-3 h-3 rounded-full bg-white/90 shadow-lg"></div>
            <div class="hero-blob delay absolute bottom-8 start-16 w-2.5 h-2.5 rounded-full bg-white/90 shadow-lg"></div>
            <div class="hero-blob absolute top-1/2 end-1/3 w-2 h-2 rounded-full bg-white/80 shadow"></div>

            <div class="relative p-5 min-h-[140px] flex items-center gap-4">
                <span class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm border border-white/30 grid place-items-center text-white shrink-0 shadow-lg">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-7 h-7">
                        <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                        <line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/>
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="inline-flex items-center gap-1 bg-white/20 backdrop-blur-sm text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-white pulse-soft"></span>
                        خريطة حية
                    </div>
                    <div class="text-white font-black text-lg leading-tight">شوف بنها على الخريطة</div>
                    <div class="text-white/90 text-xs mt-1 font-bold">
                        {{ number_format($totalOnMap) }} نشاط بمكانه على الخريطة
                    </div>
                </div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-6 h-6 text-white shrink-0 group-hover:-translate-x-1 transition">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </div>
        </a>
    </section>

</div>
@endsection
