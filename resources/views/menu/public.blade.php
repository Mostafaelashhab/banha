@php $L = \App\Models\Business::menuLabels($business->category); @endphp
@extends('layouts.app', [
    'title'       => $L['title'] . ' · ' . $business->name . ' · ' . ($business->zone->name ?? 'بنها') . ' · بنهاوي',
    'description' => $L['title'] . ' وأسعار ' . $business->name . ' في ' . ($business->zone->name ?? 'بنها') . '. شوف كل ' . $L['item_label'] . ' والأسعار وكلّم النشاط مباشرة عبر بنهاوي.',
    'ogImage'     => $business->photo_url,
    'canonical'   => route('menu.public', $business),
])

@push('head')
<style>
    .menu-leader {
        flex: 1;
        border-bottom: 1px dotted rgba(11, 11, 12, .25);
        margin: 0 .5rem;
        align-self: end;
        height: 1em;
        margin-bottom: .35em;
    }
    .menu-cat-nav { scroll-snap-type: x mandatory; }
    .menu-cat-nav > a { scroll-snap-align: start; }
    .menu-cat-nav > a.is-active {
        background: var(--color-coral-500, #2D5BFF);
        color: #fff;
        border-color: var(--color-coral-500, #2D5BFF);
    }
    html { scroll-padding-top: 80px; }
</style>

{{-- JSON-LD: Restaurant + Menu (rich snippets for Google) --}}
<script type="application/ld+json">
@php
    $menuSections = [];
    foreach ($business->menuCategories as $cat) {
        $menuItems = [];
        foreach ($cat->items as $it) {
            $entry = ['@type' => 'MenuItem', 'name' => $it->name];
            if ($it->description) $entry['description'] = $it->description;
            if ($it->price) {
                $entry['offers'] = [
                    '@type'         => 'Offer',
                    'price'         => (string) $it->price,
                    'priceCurrency' => $business->menu_currency ?? 'EGP',
                ];
            }
            if ($it->photo_url) $entry['image'] = url($it->photo_url);
            $menuItems[] = $entry;
        }
        if (! empty($menuItems)) {
            $menuSections[] = [
                '@type'        => 'MenuSection',
                'name'         => $cat->name,
                'hasMenuItem'  => $menuItems,
            ];
        }
    }

    $ld = [
        '@context' => 'https://schema.org',
        '@type'    => $business->category === 'food' ? 'Restaurant' : 'LocalBusiness',
        'name'     => $business->name,
        'url'      => route('menu.public', $business),
        'image'    => $business->photo_url ? url($business->photo_url) : null,
        'address'  => [
            '@type'           => 'PostalAddress',
            'addressLocality' => $business->zone->name ?? 'بنها',
            'addressRegion'   => 'القليوبية',
            'addressCountry'  => 'EG',
            'streetAddress'   => $business->address,
        ],
        'telephone' => $business->phone,
    ];
    if ($business->lat && $business->lng) {
        $ld['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) $business->lat, 'longitude' => (float) $business->lng];
    }
    if ($business->ratings_count > 0) {
        $ld['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (float) $business->rating_avg,
            'reviewCount' => (int) $business->ratings_count,
        ];
    }
    if (! empty($menuSections)) {
        $ld['hasMenu'] = [
            '@type' => 'Menu',
            'name'  => $L['title'] . ' · ' . $business->name,
            'hasMenuSection' => $menuSections,
        ];
    }
    $ld = array_filter($ld);
@endphp
{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@php
    $heroPhoto = ($business->photo_url && ! str_contains($business->photo_url, 'd-innova.com')) ? $business->photo_url : null;
    $heroInitial = mb_substr(trim($business->name ?: '?'), 0, 1);
    $heroColor   = ($business->categoryMeta()['color'] ?? '#2D5BFF');
    $itemsCount = $business->menuItems()->where('is_available', true)->count();

    // Build a pre-filled WhatsApp order message (used as a fallback CTA, non-cart case)
    $waOrderMsg = $business->whatsapp
        ? 'حابب أتواصل بخصوص ' . $L['title'] . ' بتاعت ' . $business->name . ' — شفت ' . $L['item_label'] . ' على ' . route('menu.public', $business)
        : null;

    // Ordering is allowed for food + shops (anything in ORDER_CATEGORIES) with a WhatsApp number.
    // The actual stepper still requires the item to have a price (handled in public-item.blade).
    $cartEnabled = $business->supportsOrdering();
    $authUser    = auth()->user();
@endphp

@section('content')
<div class="max-w-2xl mx-auto pb-20">

    {{-- Hero: branded Banhawy cover when no user photo --}}
    <div class="relative -mx-4 mb-3 overflow-hidden aspect-[16/9]"
         style="background: linear-gradient(135deg, {{ $heroColor }}, {{ $heroColor }}cc 60%, {{ $heroColor }}88);">
        <svg class="absolute inset-0 w-full h-full opacity-15" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
            <defs>
                <pattern id="menu-hero-dots-{{ $business->id }}" x="0" y="0" width="28" height="28" patternUnits="userSpaceOnUse">
                    <circle cx="3" cy="3" r="1.8" fill="white"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#menu-hero-dots-{{ $business->id }})"/>
        </svg>
        <div class="absolute inset-0 grid place-items-center">
            <span class="text-white font-black text-[110px] leading-none opacity-95 select-none drop-shadow-lg">{{ $heroInitial }}</span>
        </div>
        @unless($heroPhoto)
            <span class="absolute top-3 end-3 inline-flex items-center gap-1 bg-white/15 backdrop-blur-sm rounded-full px-2.5 py-1 text-white text-[10px] font-extrabold z-30">
                <span class="w-4 h-4 rounded-md bg-white text-[10px] grid place-items-center font-black" style="color: {{ $heroColor }};">ب</span>
                بنهاوي
            </span>
        @endunless
        @if($heroPhoto)
            <img src="{{ $heroPhoto }}" alt="{{ $business->name }}" loading="eager"
                 class="absolute inset-0 w-full h-full object-cover z-10"
                 onerror="this.style.display='none'">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent z-20"></div>

        <div class="absolute top-3 start-3 flex flex-col gap-1.5 z-30">
            @if($business->is_verified)
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-mint-500 text-white w-fit">
                    <x-icon name="check" class="w-3 h-3"/> موثّق
                </span>
            @endif
            @if($business->is_24h)
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-honey-500 text-ink-950 w-fit">
                    ٢٤ ساعة
                </span>
            @endif
        </div>

        <div class="absolute bottom-0 inset-x-0 p-4 z-30">
            <h1 class="text-2xl md:text-3xl font-black text-white leading-tight drop-shadow-lg">{{ $business->name }}</h1>
            <div class="flex items-center gap-2 mt-1.5 text-white/90 text-sm flex-wrap">
                @if($business->zone)
                    <span class="inline-flex items-center gap-1">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $business->zone->name }}
                    </span>
                @endif
                @if($business->ratings_count > 0)
                    <span class="text-white/60">·</span>
                    <span class="inline-flex items-center gap-0.5">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3 text-honey-400"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                        <span class="font-bold">{{ $business->rating_avg }}</span>
                        <span class="text-white/70 text-xs">({{ $business->ratings_count }})</span>
                    </span>
                @endif
                @if($itemsCount > 0)
                    <span class="text-white/60">·</span>
                    <span>{{ $itemsCount }} {{ $L['item_label'] }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Top action bar (sticky-feel CTAs) --}}
    <div class="grid grid-cols-{{ ($business->phone && $business->whatsapp) ? '2' : '1' }} gap-2 mb-3">
        @if($business->phone)
            <a href="tel:{{ $business->phone }}" data-track-click="phone" data-business="{{ $business->id }}"
               class="btn-primary justify-center !py-3 text-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                اتصل
            </a>
        @endif
        @if($business->whatsapp)
            <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}?text={{ urlencode($waOrderMsg) }}"
               target="_blank"
               data-track-click="whatsapp" data-business="{{ $business->id }}"
               class="inline-flex items-center justify-center gap-2 py-3 rounded-full font-bold text-white text-sm transition hover:scale-[1.02]"
               style="background: linear-gradient(135deg, #25D366, #128C7E)">
                <x-icon name="whatsapp" class="w-4 h-4"/>
                {{ $business->category === 'food' ? 'اطلب على واتساب' : 'تواصل على واتساب' }}
            </a>
        @endif
    </div>

    {{-- Sticky category nav (jumps to sections) --}}
    @if($business->menuCategories->where('items', '!=', null)->count() > 0)
        @php
            $visibleCats = $business->menuCategories->filter(fn ($c) => $c->items->isNotEmpty());
        @endphp
        @if($visibleCats->count() > 1)
            <div class="sticky top-14 z-20 -mx-4 px-4 py-2 bg-cream-100/90 backdrop-blur border-b border-ink-950/8">
                <div class="menu-cat-nav flex gap-2 overflow-x-auto scrollbar-hide">
                    @foreach($visibleCats as $cat)
                        <a href="#cat-{{ $cat->id }}"
                           class="chip shrink-0 inline-flex items-center gap-1.5"
                           data-cat="{{ $cat->id }}">
                            {{ $cat->name }}
                            <span class="text-[10px] opacity-60">({{ $cat->items->count() }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- Menu sections --}}
    @if($business->menuCategories->isEmpty() && $looseItems->isEmpty())
        <div class="card-light p-10 text-center mt-3">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-16 h-16 mx-auto text-ink-300 mb-3">
                <path d="M3 11h18l-1 9H4z"/><path d="M7 11V8a5 5 0 0 1 10 0v3"/>
            </svg>
            <h3 class="font-extrabold text-ink-950 mb-1">{{ $L['title'] }} لسه فاضية</h3>
            <p class="text-ink-500 text-sm">هتتحدّث قريب!</p>
        </div>
    @else
        @foreach($business->menuCategories as $cat)
            @if($cat->items->isNotEmpty())
                <section id="cat-{{ $cat->id }}" class="mt-5 mb-3">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <h2 class="text-xl font-black text-ink-950 inline-flex items-center gap-2">
                            <span class="w-1 h-6 bg-coral-500 rounded-full"></span>
                            {{ $cat->name }}
                        </h2>
                        <span class="text-xs font-bold text-ink-400">{{ $cat->items->count() }} {{ $L['item_label'] }}</span>
                    </div>
                    <div class="card-light p-3 divide-y divide-ink-950/5">
                        @foreach($cat->items as $it)
                            @include('menu.partials.public-item', ['item' => $it, 'currency' => $business->menu_currency ?? 'EGP', 'cartEnabled' => $cartEnabled])
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        @if($looseItems->isNotEmpty())
            <section class="mt-5 mb-3">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h2 class="text-xl font-black text-ink-950 inline-flex items-center gap-2">
                        <span class="w-1 h-6 bg-coral-500 rounded-full"></span>
                        {{ $L['item_label'] }} تانية
                    </h2>
                    <span class="text-xs font-bold text-ink-400">{{ $looseItems->count() }} {{ $L['item_label'] }}</span>
                </div>
                <div class="card-light p-3 divide-y divide-ink-950/5">
                    @foreach($looseItems as $it)
                        @include('menu.partials.public-item', ['item' => $it, 'currency' => $business->menu_currency ?? 'EGP', 'cartEnabled' => $cartEnabled])
                    @endforeach
                </div>
            </section>
        @endif
    @endif

    {{-- Type-specific extras (stars / delivery / wifi / specialty / etc.) --}}
    @php
        $extras    = (array) ($business->extra ?? []);
        $extraDefs = \App\Models\Business::extraFieldsFor($business->sub_type);
        $visible   = collect($extraDefs)
            ->filter(fn ($def, $key) => array_key_exists($key, $extras) && $extras[$key] !== null && $extras[$key] !== '')
            ->all();
        $cm        = $business->categoryMeta();
    @endphp
    @if(! empty($visible))
        <div class="card-light p-4 mt-5">
            <h3 class="text-sm font-extrabold text-ink-950 mb-3 inline-flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-coral-100 text-coral-600 grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>
                    </svg>
                </span>
                تفاصيل {{ $cm['label'] }}
            </h3>
            <dl class="grid grid-cols-2 gap-3">
                @foreach($visible as $key => $def)
                    @php $v = $extras[$key]; @endphp
                    <div class="bg-cream-100/70 rounded-xl p-3">
                        <dt class="text-[10px] font-bold text-ink-500">{{ $def['label'] }}</dt>
                        <dd class="text-sm font-extrabold text-ink-950 mt-0.5">
                            @if($def['type'] === 'checkbox')
                                @if($v)
                                    <span class="inline-flex items-center gap-1 text-mint-700">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        متوفر
                                    </span>
                                @else
                                    <span class="text-ink-400">مش متوفر</span>
                                @endif
                            @elseif($def['type'] === 'select' && isset($def['options'][$v]))
                                {{ $def['options'][$v] }}
                            @elseif($key === 'website')
                                <a href="{{ str_starts_with($v, 'http') ? $v : 'https://'.$v }}" target="_blank" rel="noopener"
                                   class="text-coral-600 hover:underline break-all" dir="ltr">
                                    {{ \Illuminate\Support\Str::limit(preg_replace('#^https?://#', '', $v), 32) }}
                                </a>
                            @else
                                {{ $v }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endif

    {{-- Address + hours card --}}
    @if($business->address || $business->hours)
        <div class="card-light p-4 mt-5 space-y-3">
            @if($business->address)
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-xl pill-coral grid place-items-center shrink-0">
                        <x-icon name="map-pin" class="w-4 h-4"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] text-ink-500">العنوان</div>
                        <div class="text-sm font-bold text-ink-950">{{ $business->address }}</div>
                    </div>
                </div>
            @endif
            @if($business->hours && ! $business->is_24h)
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-xl pill-honey grid place-items-center shrink-0">
                        <x-icon name="bell" class="w-4 h-4"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] text-ink-500">المواعيد</div>
                        <div class="text-sm font-bold text-ink-950">{{ $business->hours }}</div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Footer attribution --}}
    <div class="text-center mt-8 mb-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-ink-400 hover:text-coral-600 transition">
            <span class="w-7 h-7 rounded-lg brand-bg grid place-items-center text-white font-black text-xs">ب</span>
            <span class="text-xs font-bold">صفحة رقمية مدعومة من بنهاوي</span>
        </a>
    </div>
</div>

{{-- Sticky bottom WhatsApp CTA on long menus (non-cart fallback) --}}
@if($business->whatsapp && $itemsCount > 5 && ! $cartEnabled)
    <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}?text={{ urlencode($waOrderMsg) }}"
       target="_blank"
       data-track-click="whatsapp" data-business="{{ $business->id }}"
       class="fixed bottom-4 inset-x-4 max-w-md mx-auto z-30 inline-flex items-center justify-center gap-2 py-3.5 rounded-full font-extrabold text-white text-sm shadow-2xl"
       style="background: linear-gradient(135deg, #25D366, #128C7E)">
        <x-icon name="whatsapp" class="w-5 h-5"/>
        اطلب الآن على واتساب
    </a>
@endif

{{-- ──── Cart: sticky button + bottom sheet (food category only) ──── --}}
@if($cartEnabled)
    @php
        $currency = $business->menu_currency ?? 'EGP';
    @endphp
    <div data-cart-root
         data-biz-id="{{ $business->id }}"
         data-biz-name="{{ $business->name }}"
         data-currency="{{ $currency }}"
         data-order-url="{{ route('order.store', $business) }}"
         data-areas-nearest-url="{{ route('areas.nearest') }}"
         data-min-order="{{ (int) ($business->delivery_min_order ?? 0) }}"
         data-default-area-id="{{ $userDefaultAreaId ?? '' }}"
         data-areas='@json($deliveryAreas)'
         data-csrf="{{ csrf_token() }}"
         @if($authUser)
            data-user-name="{{ $authUser->name }}"
            data-user-phone="{{ $authUser->phone ?? '' }}"
            data-user-set-area-url="{{ route('profile.area.set') }}"
         @endif
    >

        {{-- Sticky bottom bar: hidden when cart empty, shown otherwise --}}
        <button type="button" data-cart-open
                class="fixed bottom-4 inset-x-4 max-w-md mx-auto z-30 hidden items-center gap-3 py-3 ps-4 pe-3 rounded-full font-extrabold text-white text-sm shadow-2xl transition hover:scale-[1.01]"
                style="background: var(--color-coral-500, #2D5BFF)">
            <span class="w-9 h-9 rounded-full bg-white/20 grid place-items-center text-base font-black" data-cart-badge>0</span>
            <span class="flex-1 text-start">
                <span class="block text-[10px] text-white/80 font-bold">طلبك جاهز · اضغط للمراجعة</span>
                <span class="block text-sm" data-cart-summary>0 صنف</span>
            </span>
            <span class="font-black text-sm shrink-0" dir="ltr" data-cart-total>0 {{ $currency }}</span>
        </button>

        {{-- Backdrop --}}
        <div data-cart-backdrop class="fixed inset-0 bg-black/55 z-40 hidden" aria-hidden="true"></div>

        {{-- Bottom sheet --}}
        <div data-cart-sheet
             class="fixed bottom-0 inset-x-0 z-50 hidden bg-cream-50 rounded-t-3xl shadow-2xl translate-y-full transition-transform duration-300 max-h-[90vh] flex flex-col"
             role="dialog" aria-modal="true" aria-labelledby="cart-sheet-title">

            <div class="px-4 pt-3 pb-2 border-b border-ink-950/8 shrink-0">
                <div class="w-12 h-1.5 bg-ink-950/15 rounded-full mx-auto mb-3" data-cart-drag></div>
                <div class="flex items-center justify-between">
                    <h3 id="cart-sheet-title" class="text-base font-black text-ink-950 inline-flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-coral-100 text-coral-600 grid place-items-center">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>
                            </svg>
                        </span>
                        <span data-cart-title>طلبي</span>
                    </h3>
                    <button type="button" data-cart-close
                            class="w-8 h-8 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-500 hover:text-ink-950 transition"
                            aria-label="إغلاق">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- STEP 1: review items --}}
            <div data-cart-step="review" class="flex-1 flex flex-col overflow-hidden">
                <div data-cart-list class="overflow-y-auto px-4 py-2 flex-1"></div>

                <div data-cart-empty class="p-10 text-center hidden">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-14 h-14 mx-auto text-ink-300 mb-2">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>
                    </svg>
                    <p class="text-sm text-ink-500">السلة فاضية — ضيف أصناف من القايمة</p>
                </div>

                <div class="border-t border-ink-950/8 p-4 space-y-3 shrink-0 bg-white" data-cart-footer>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ink-500">الإجمالي</span>
                        <span class="text-xl font-black text-ink-950" dir="ltr">
                            <span data-cart-total-big>0</span>
                            <span class="text-xs text-ink-400 font-bold">{{ $currency }}</span>
                        </span>
                    </div>
                    <button type="button" data-cart-to-checkout
                            class="w-full inline-flex items-center justify-center gap-2 py-3.5 rounded-full font-extrabold text-white text-sm shadow-lg transition hover:scale-[1.01]"
                            style="background: var(--color-coral-500, #2D5BFF)">
                        كمّل الطلب
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4 rtl:rotate-180">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>
                    <button type="button" data-cart-clear
                            class="w-full text-xs font-bold text-blush-500 hover:underline">
                        امسح السلة
                    </button>
                </div>
            </div>

            {{-- STEP 2: checkout form --}}
            <form data-cart-step="checkout" data-cart-form
                  class="flex-1 hidden flex-col overflow-hidden">
                <div class="overflow-y-auto px-4 py-3 flex-1 space-y-3">
                    <button type="button" data-cart-back
                            class="inline-flex items-center gap-1 text-xs font-bold text-ink-500 hover:text-ink-950">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-3 h-3 rtl:-rotate-180">
                            <polyline points="15 18 9 12 15 6"/>
                        </svg>
                        رجوع للسلة
                    </button>
                    <div>
                        <label class="block text-[11px] font-bold text-ink-700 mb-1">اسمك *</label>
                        <input type="text" name="customer_name" required maxlength="80"
                               value="{{ $authUser->name ?? '' }}"
                               class="w-full bg-cream-100 rounded-2xl px-4 py-2.5 text-sm text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition"
                               placeholder="مثلاً: أحمد محمد">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-ink-700 mb-1">موبايلك *</label>
                        <input type="tel" name="customer_phone" required dir="ltr" inputmode="numeric"
                               pattern="01[0125][0-9]{8}" maxlength="11"
                               value="{{ $authUser->phone ?? '' }}"
                               class="w-full bg-cream-100 rounded-2xl px-4 py-2.5 text-sm text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition"
                               placeholder="01XXXXXXXXX">
                        <p class="text-[10px] text-ink-400 mt-1">المطعم هيكلّمك على الرقم ده لتأكيد الطلب.</p>
                    </div>
                    @if($business->offersDelivery())
                        <div data-area-block>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-[11px] font-bold text-ink-700">منطقتك (للتوصيل) *</label>
                                <button type="button" data-area-detect
                                        class="inline-flex items-center gap-1 text-[10px] font-extrabold text-coral-600 hover:underline">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                        <circle cx="12" cy="12" r="3"/>
                                        <path d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
                                    </svg>
                                    حدّد مكاني تلقائي
                                </button>
                            </div>
                            <select name="area_id" required data-area-select
                                    class="w-full bg-cream-100 rounded-2xl px-4 py-2.5 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition appearance-none">
                                <option value="">— اختار منطقتك —</option>
                                @php
                                    $grouped = collect($deliveryAreas)->groupBy('parent');
                                @endphp
                                @foreach($grouped as $parent => $rows)
                                    <optgroup label="{{ $parent }}">
                                        @foreach($rows as $a)
                                            <option value="{{ $a['id'] }}" data-fee="{{ $a['fee'] }}">
                                                {{ $a['name'] }} ({{ $a['fee'] == 0 ? 'مجاناً' : $a['fee'].' ج' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <p data-area-detect-msg class="hidden text-[10px] text-ink-500 mt-1"></p>
                        </div>
                    @endif
                    <div>
                        <label class="block text-[11px] font-bold text-ink-700 mb-1">العنوان بالظبط (شارع، عمارة، دور)</label>
                        <input type="text" name="customer_address" maxlength="255"
                               class="w-full bg-cream-100 rounded-2xl px-4 py-2.5 text-sm text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition"
                               placeholder="مثلاً: شارع طه حسين، عمارة 12، الدور 3">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-ink-700 mb-1">ملاحظات (اختياري)</label>
                        <textarea name="notes" rows="2" maxlength="300"
                                  class="w-full bg-cream-100 rounded-2xl px-4 py-2.5 text-sm text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none"
                                  placeholder="مثلاً: من غير بصل، فلفل حار زيادة..."></textarea>
                    </div>

                    <div data-cart-error class="hidden rounded-2xl bg-blush-100 border border-blush-500/30 p-3 text-xs font-bold text-blush-700"></div>
                </div>

                <div class="border-t border-ink-950/8 p-4 space-y-2 shrink-0 bg-white">
                    @if($business->offersDelivery())
                        <div class="flex items-center justify-between text-[11px] text-ink-500">
                            <span>الأصناف</span>
                            <span dir="ltr"><span data-cart-subtotal>0</span> {{ $currency }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-ink-500" data-cart-fee-row>
                            <span>الشحن <span data-cart-fee-zone class="text-ink-400"></span></span>
                            <span dir="ltr"><span data-cart-fee>—</span></span>
                        </div>
                        @if((int) ($business->delivery_min_order ?? 0) > 0)
                            <p class="hidden text-[10px] font-bold text-blush-600" data-cart-min-warn>
                                ⚠ الحد الأدنى للأوردر {{ (int) $business->delivery_min_order }} ج.
                            </p>
                        @endif
                    @endif
                    <div class="flex items-center justify-between pt-1 border-t border-ink-950/5">
                        <span class="text-xs font-bold text-ink-500">الإجمالي</span>
                        <span class="text-lg font-black text-ink-950" dir="ltr">
                            <span data-cart-total-big-2>0</span>
                            <span class="text-xs text-ink-400 font-bold">{{ $currency }}</span>
                        </span>
                    </div>
                    <button type="submit" data-cart-submit
                            class="w-full inline-flex items-center justify-center gap-2 py-3.5 rounded-full font-extrabold text-white text-sm shadow-lg transition hover:scale-[1.01] disabled:opacity-60 disabled:hover:scale-100"
                            style="background: var(--color-coral-500, #2D5BFF)">
                        <span data-cart-submit-label>أكد الطلب</span>
                        <svg data-cart-spinner class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                        </svg>
                    </button>
                    <p class="text-[10px] text-ink-400 text-center">باضغطك على الزرار، طلبك بيتبعت للمطعم على واتساب من بنهاوي.</p>
                </div>
            </form>

            {{-- STEP 3: success --}}
            <div data-cart-step="success" class="flex-1 hidden flex-col items-center justify-center text-center p-6">
                <div class="w-16 h-16 rounded-full bg-mint-100 grid place-items-center mb-3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 text-mint-700">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <h3 class="text-lg font-black text-ink-950 mb-1">تمام، طلبك وصلهم!</h3>
                <p class="text-sm text-ink-500 mb-1" data-cart-success-msg>المطعم هيتواصل معاك قريب.</p>
                <p class="text-[11px] text-ink-400 mb-5">رقم طلبك: <span dir="ltr" data-cart-order-id class="font-mono font-bold"></span></p>
                <button type="button" data-cart-close-final
                        class="px-6 py-2.5 rounded-full bg-ink-950 text-white text-sm font-extrabold">
                    تمام
                </button>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
// Highlight category in sticky nav as user scrolls through sections
(function () {
    const nav = document.querySelector('.menu-cat-nav');
    if (!nav) return;
    const links = nav.querySelectorAll('a[data-cat]');
    if (!links.length) return;

    const setActive = (id) => {
        links.forEach(a => a.classList.toggle('is-active', a.dataset.cat === String(id)));
    };

    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                const id = e.target.id.replace('cat-', '');
                setActive(id);
            }
        });
    }, { rootMargin: '-30% 0px -50% 0px', threshold: 0 });

    document.querySelectorAll('section[id^="cat-"]').forEach(s => io.observe(s));
})();

// ── Cart: localStorage per-business + server-side order via WAAPI ──
(function () {
    const root = document.querySelector('[data-cart-root]');
    if (!root) return;

    const bizId    = root.dataset.bizId;
    const currency = root.dataset.currency || 'EGP';
    const orderUrl = root.dataset.orderUrl;
    const csrf     = root.dataset.csrf;
    const STORAGE_KEY = 'banhawy:cart:' + bizId;

    // ── Delivery wiring ──
    const areaNearestUrl   = root.dataset.areasNearestUrl || '';
    const areaSetUserUrl   = root.dataset.userSetAreaUrl || '';
    const minOrder         = Number(root.dataset.minOrder || 0);
    const userDefaultAreaId = root.dataset.defaultAreaId || '';
    let areas = [];
    try { areas = JSON.parse(root.dataset.areas || '[]') || []; } catch (e) { areas = []; }
    const areaSelect      = root.querySelector('[data-area-select]');
    const areaDetectBtn   = root.querySelector('[data-area-detect]');
    const areaDetectMsg   = root.querySelector('[data-area-detect-msg]');
    const feeRow          = root.querySelector('[data-cart-fee-row]');
    const feeEl           = root.querySelector('[data-cart-fee]');
    const feeZoneEl       = root.querySelector('[data-cart-fee-zone]');
    const subtotalEl      = root.querySelector('[data-cart-subtotal]');
    const minWarnEl       = root.querySelector('[data-cart-min-warn]');
    const COOKIE_AREA     = 'banhawy_area_id';

    function getCookie(name) {
        return document.cookie.split('; ').reduce((acc, c) => {
            const [k, v] = c.split('=');
            return k === name ? decodeURIComponent(v) : acc;
        }, '');
    }
    function setCookie(name, value) {
        const oneYear = 60 * 60 * 24 * 365;
        document.cookie = name + '=' + encodeURIComponent(value) + '; max-age=' + oneYear + '; path=/; samesite=lax';
    }
    function selectedAreaId() {
        return areaSelect ? areaSelect.value : '';
    }
    function selectedFee() {
        if (!areaSelect || !areaSelect.value) return null;
        const opt = areaSelect.options[areaSelect.selectedIndex];
        const v = opt ? opt.dataset.fee : null;
        return v === undefined || v === null || v === '' ? null : Number(v);
    }

    const bar         = root.querySelector('[data-cart-open]');
    const sheet       = root.querySelector('[data-cart-sheet]');
    const backdrop    = root.querySelector('[data-cart-backdrop]');
    const list        = root.querySelector('[data-cart-list]');
    const emptyEl     = root.querySelector('[data-cart-empty]');
    const footerEl    = root.querySelector('[data-cart-footer]');
    const toCheckout  = root.querySelector('[data-cart-to-checkout]');
    const backBtn     = root.querySelector('[data-cart-back]');
    const clearBtn    = root.querySelector('[data-cart-clear]');
    const closeBtn    = root.querySelector('[data-cart-close]');
    const closeFinal  = root.querySelector('[data-cart-close-final]');
    const totalBig    = root.querySelector('[data-cart-total-big]');
    const totalBig2   = root.querySelector('[data-cart-total-big-2]');
    const badge       = root.querySelector('[data-cart-badge]');
    const summary     = root.querySelector('[data-cart-summary]');
    const totalSm     = root.querySelector('[data-cart-total]');
    const form        = root.querySelector('[data-cart-form]');
    const submitBtn   = root.querySelector('[data-cart-submit]');
    const submitLabel = root.querySelector('[data-cart-submit-label]');
    const spinner     = root.querySelector('[data-cart-spinner]');
    const errBox      = root.querySelector('[data-cart-error]');
    const successMsg  = root.querySelector('[data-cart-success-msg]');
    const orderIdEl   = root.querySelector('[data-cart-order-id]');

    const steps = {
        review:   root.querySelector('[data-cart-step="review"]'),
        checkout: root.querySelector('[data-cart-step="checkout"]'),
        success:  root.querySelector('[data-cart-step="success"]'),
    };

    /** @type {Object<string,{id:string,name:string,price:number,qty:number}>} */
    let cart = {};
    try { cart = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}') || {}; } catch (e) { cart = {}; }

    function save() {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(cart)); } catch (e) {}
    }
    function totalItems() {
        return Object.values(cart).reduce((s, x) => s + x.qty, 0);
    }
    function totalPrice() {
        return Object.values(cart).reduce((s, x) => s + (x.qty * x.price), 0);
    }
    function fmt(n) {
        const v = Math.round(n * 100) / 100;
        return (v % 1 === 0 ? String(v) : v.toFixed(2));
    }
    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function setStep(name) {
        Object.entries(steps).forEach(([k, el]) => {
            if (!el) return;
            if (k === name) {
                el.classList.remove('hidden');
                el.classList.add('flex');
            } else {
                el.classList.add('hidden');
                el.classList.remove('flex');
            }
        });
    }

    // ── In-list steppers ──
    function updateItemUI(itemId) {
        const wrap = document.querySelector('[data-cart-item][data-item-id="' + itemId + '"]');
        if (!wrap) return;
        const addBtn  = wrap.querySelector('[data-cart-add]');
        const stepper = wrap.querySelector('[data-cart-stepper]');
        const qtyEl   = wrap.querySelector('[data-cart-qty]');
        const qty = cart[itemId]?.qty || 0;
        if (qty > 0) {
            addBtn.classList.add('hidden');
            stepper.classList.remove('hidden');
            stepper.classList.add('inline-flex');
            qtyEl.textContent = qty;
        } else {
            stepper.classList.add('hidden');
            stepper.classList.remove('inline-flex');
            addBtn.classList.remove('hidden');
        }
    }
    function updateAllItemUI() {
        document.querySelectorAll('[data-cart-item]').forEach(w => updateItemUI(w.dataset.itemId));
    }

    function refresh() {
        const n = totalItems();
        const t = totalPrice();
        if (n > 0) {
            bar.classList.remove('hidden');
            bar.classList.add('inline-flex');
            badge.textContent = n;
            summary.textContent = n + ' صنف';
            totalSm.textContent = fmt(t) + ' ' + currency;
        } else {
            bar.classList.add('hidden');
            bar.classList.remove('inline-flex');
        }

        const ids = Object.keys(cart);
        if (ids.length === 0) {
            list.innerHTML = '';
            emptyEl.classList.remove('hidden');
            footerEl.classList.add('hidden');
        } else {
            emptyEl.classList.add('hidden');
            footerEl.classList.remove('hidden');
            list.innerHTML = ids.map(id => {
                const it = cart[id];
                const line = it.qty * it.price;
                return ''
                  + '<div class="flex items-center gap-3 py-3 border-b border-ink-950/5 last:border-0" data-sheet-row data-id="' + escapeHtml(id) + '">'
                  +   '<div class="flex-1 min-w-0">'
                  +     '<div class="text-sm font-extrabold text-ink-950 truncate">' + escapeHtml(it.name) + '</div>'
                  +     '<div class="text-[11px] text-ink-500" dir="ltr">' + fmt(it.price) + ' ' + currency + ' × ' + it.qty + '</div>'
                  +   '</div>'
                  +   '<div class="inline-flex items-center gap-1 bg-coral-500 rounded-full p-1 shrink-0">'
                  +     '<button type="button" data-sheet-dec class="w-7 h-7 rounded-full bg-white text-coral-600 grid place-items-center font-black">−</button>'
                  +     '<span class="min-w-[26px] text-center text-white text-sm font-extrabold">' + it.qty + '</span>'
                  +     '<button type="button" data-sheet-inc class="w-7 h-7 rounded-full bg-white text-coral-600 grid place-items-center font-black">+</button>'
                  +   '</div>'
                  +   '<div class="text-sm font-black text-coral-600 shrink-0 min-w-[60px] text-end" dir="ltr">' + fmt(line) + '</div>'
                  + '</div>';
            }).join('');
        }
        totalBig.textContent = fmt(t);

        // Delivery fee + grand total (only when business offers delivery)
        const fee = selectedFee();
        const hasDelivery = !!feeRow;
        const grand = t + (fee !== null ? fee : 0);
        if (subtotalEl) subtotalEl.textContent = fmt(t);
        if (feeEl) {
            if (fee === null) feeEl.textContent = '— اختار منطقة';
            else if (fee === 0) feeEl.textContent = 'مجاناً';
            else feeEl.textContent = fmt(fee) + ' ' + currency;
        }
        if (feeZoneEl) {
            const opt = areaSelect && areaSelect.value ? areaSelect.options[areaSelect.selectedIndex] : null;
            feeZoneEl.textContent = opt ? '· ' + (opt.text.split(' (')[0]) : '';
        }
        if (totalBig2) totalBig2.textContent = fmt(hasDelivery ? grand : t);

        // Min-order warning
        if (minWarnEl) {
            minWarnEl.classList.toggle('hidden', !(minOrder > 0 && t > 0 && t < minOrder));
        }
    }

    function add(id, name, price) {
        if (!cart[id]) cart[id] = { id, name, price: Number(price) || 0, qty: 0 };
        cart[id].qty += 1;
        save(); updateItemUI(id); refresh();
    }
    function inc(id) { if (cart[id]) { cart[id].qty += 1; save(); updateItemUI(id); refresh(); } }
    function dec(id) {
        if (!cart[id]) return;
        cart[id].qty -= 1;
        if (cart[id].qty <= 0) delete cart[id];
        save(); updateItemUI(id); refresh();
    }
    function clear() {
        cart = {}; save();
        updateAllItemUI(); refresh(); closeSheet();
    }

    document.querySelectorAll('[data-cart-item]').forEach(wrap => {
        const id = wrap.dataset.itemId;
        const name = wrap.dataset.itemName;
        const price = wrap.dataset.itemPrice;
        wrap.querySelector('[data-cart-add]')?.addEventListener('click', () => add(id, name, price));
        wrap.querySelector('[data-cart-inc]')?.addEventListener('click', () => inc(id));
        wrap.querySelector('[data-cart-dec]')?.addEventListener('click', () => dec(id));
    });

    list.addEventListener('click', (e) => {
        const row = e.target.closest('[data-sheet-row]');
        if (!row) return;
        const id = row.dataset.id;
        if (e.target.closest('[data-sheet-inc]')) inc(id);
        else if (e.target.closest('[data-sheet-dec]')) dec(id);
    });

    function openSheet() {
        setStep('review');
        sheet.classList.remove('hidden');
        backdrop.classList.remove('hidden');
        requestAnimationFrame(() => sheet.style.transform = 'translateY(0)');
        document.body.style.overflow = 'hidden';
    }
    function closeSheet() {
        sheet.style.transform = '';
        backdrop.classList.add('hidden');
        document.body.style.overflow = '';
        setTimeout(() => { sheet.classList.add('hidden'); }, 280);
    }
    bar.addEventListener('click', openSheet);
    backdrop.addEventListener('click', closeSheet);
    closeBtn.addEventListener('click', closeSheet);
    closeFinal?.addEventListener('click', closeSheet);
    clearBtn.addEventListener('click', () => {
        if (confirm('تمسح السلة كلها؟')) clear();
    });
    toCheckout.addEventListener('click', () => {
        if (totalItems() === 0) return;
        errBox.classList.add('hidden');
        setStep('checkout');
    });
    backBtn?.addEventListener('click', () => setStep('review'));

    // ── Area picker: change → recompute, persist, sync to user ──
    if (areaSelect) {
        areaSelect.addEventListener('change', () => {
            refresh();
            const aid = areaSelect.value;
            if (aid) {
                setCookie(COOKIE_AREA, aid);
                if (areaSetUserUrl) {
                    fetch(areaSetUserUrl, {
                        method: 'POST',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},
                        body: JSON.stringify({ area_id: Number(aid) }),
                    }).catch(() => {});
                }
            }
        });

        // Pre-select preferred area: user.default → cookie → first option
        const cookieArea = getCookie(COOKIE_AREA);
        const preferred = userDefaultAreaId || cookieArea;
        if (preferred) {
            const opt = areaSelect.querySelector('option[value="' + preferred + '"]');
            if (opt) areaSelect.value = preferred;
        }
    }

    // ── Geolocation auto-detect: try once, on first checkout open ──
    let geoTried = false;
    function tryAutoDetect(silent) {
        if (geoTried || !areaSelect || !areaNearestUrl) return;
        geoTried = true;
        if (!navigator.geolocation) {
            if (!silent && areaDetectMsg) {
                areaDetectMsg.classList.remove('hidden');
                areaDetectMsg.textContent = 'متصفحك مش بيدعم تحديد الموقع.';
            }
            return;
        }
        // Skip auto-detect if user already picked an area (don't override their choice)
        if (areaSelect.value && silent) return;

        if (!silent && areaDetectMsg) {
            areaDetectMsg.classList.remove('hidden');
            areaDetectMsg.textContent = '⏳ بنحدد مكانك...';
        }
        navigator.geolocation.getCurrentPosition(async (pos) => {
            try {
                const u = areaNearestUrl + '?lat=' + pos.coords.latitude + '&lng=' + pos.coords.longitude;
                const r = await fetch(u, { headers: {'Accept':'application/json'} });
                const body = await r.json();
                if (body.ok && body.area && areaSelect) {
                    // Only auto-fill if user hasn't picked yet
                    if (!areaSelect.value || !silent) {
                        const opt = areaSelect.querySelector('option[value="' + body.area.id + '"]');
                        if (opt) {
                            areaSelect.value = String(body.area.id);
                            areaSelect.dispatchEvent(new Event('change'));
                            if (areaDetectMsg) {
                                areaDetectMsg.classList.remove('hidden');
                                areaDetectMsg.textContent = '✓ تم اختيار ' + body.area.name + ' أوتوماتيك.';
                                setTimeout(() => areaDetectMsg.classList.add('hidden'), 4000);
                            }
                            return;
                        }
                    }
                }
                if (!silent && areaDetectMsg) {
                    areaDetectMsg.classList.remove('hidden');
                    areaDetectMsg.textContent = 'مكانك مش ضمن مناطق التوصيل — اختار يدوي.';
                }
            } catch (err) {
                if (!silent && areaDetectMsg) {
                    areaDetectMsg.classList.remove('hidden');
                    areaDetectMsg.textContent = 'فشل تحديد المكان — اختار يدوي.';
                }
            }
        }, () => {
            if (!silent && areaDetectMsg) {
                areaDetectMsg.classList.remove('hidden');
                areaDetectMsg.textContent = 'الموقع مرفوض — اختار منطقتك يدوي.';
            }
        }, { timeout: 8000, maximumAge: 5 * 60 * 1000 });
    }
    // Fire silent auto-detect on first checkout open (only when no area set yet)
    toCheckout?.addEventListener('click', () => { if (areaSelect && !areaSelect.value) tryAutoDetect(true); }, { once: false });
    areaDetectBtn?.addEventListener('click', () => { geoTried = false; tryAutoDetect(false); });

    // ── Submit order to server (server sends WAAPI message to restaurant) ──
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (totalItems() === 0) return;

        errBox.classList.add('hidden');
        submitBtn.disabled = true;
        submitLabel.textContent = 'جارٍ الإرسال...';
        spinner.classList.remove('hidden');

        // Client-side guards before the network round-trip
        if (areaSelect && !areaSelect.value) {
            errBox.textContent = 'اختار منطقتك للتوصيل.';
            errBox.classList.remove('hidden');
            submitBtn.disabled = false;
            submitLabel.textContent = 'أكد الطلب';
            spinner.classList.add('hidden');
            return;
        }
        if (minOrder > 0 && totalPrice() < minOrder) {
            errBox.textContent = 'الحد الأدنى للأوردر ' + minOrder + ' ' + currency + '.';
            errBox.classList.remove('hidden');
            submitBtn.disabled = false;
            submitLabel.textContent = 'أكد الطلب';
            spinner.classList.add('hidden');
            return;
        }

        const fd = new FormData(form);
        const payload = {
            customer_name:    fd.get('customer_name') || '',
            customer_phone:   fd.get('customer_phone') || '',
            customer_address: fd.get('customer_address') || '',
            area_id:          fd.get('area_id') ? Number(fd.get('area_id')) : null,
            notes:            fd.get('notes') || '',
            items: Object.values(cart).map(it => ({ id: Number(it.id), qty: it.qty })),
        };

        try {
            const resp = await fetch(orderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type':     'application/json',
                    'Accept':           'application/json',
                    'X-CSRF-TOKEN':     csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });
            const body = await resp.json().catch(() => ({}));

            if (!resp.ok || !body.ok) {
                const msg = body?.error
                    || (body?.errors && Object.values(body.errors).flat().join(' · '))
                    || 'حصل خطأ، حاول تاني.';
                errBox.textContent = msg;
                errBox.classList.remove('hidden');
                return;
            }

            orderIdEl.textContent = '#' + body.order_id;
            if (body.message) successMsg.textContent = body.message;
            cart = {}; save(); updateAllItemUI(); refresh();
            setStep('success');
        } catch (err) {
            errBox.textContent = 'الشبكة فيها مشكلة. حاول تاني.';
            errBox.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitLabel.textContent = 'أكد الطلب';
            spinner.classList.add('hidden');
        }
    });

    updateAllItemUI();
    refresh();
    setStep('review');
})();
</script>
@endpush
@endsection
