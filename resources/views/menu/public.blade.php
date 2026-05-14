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

    // Build a pre-filled WhatsApp order message
    $waOrderMsg = $business->whatsapp
        ? 'حابب أتواصل بخصوص ' . $L['title'] . ' بتاعت ' . $business->name . ' — شفت ' . $L['item_label'] . ' على ' . route('menu.public', $business)
        : null;

    // Cart only makes sense for food-category businesses with a WhatsApp number
    $cartEnabled = $business->category === 'food' && $business->whatsapp;
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
        $waPhone = \App\Services\WaapiService::toIntl($business->whatsapp);
        $currency = $business->menu_currency ?? 'EGP';
    @endphp
    <div data-cart-root
         data-biz-id="{{ $business->id }}"
         data-biz-name="{{ $business->name }}"
         data-wa-phone="{{ $waPhone }}"
         data-currency="{{ $currency }}"
         data-menu-url="{{ route('menu.public', $business) }}">

        {{-- Sticky bottom bar: hidden when cart empty, shown otherwise --}}
        <button type="button" data-cart-open
                class="fixed bottom-4 inset-x-4 max-w-md mx-auto z-30 hidden items-center gap-3 py-3 ps-4 pe-3 rounded-full font-extrabold text-white text-sm shadow-2xl transition hover:scale-[1.01]"
                style="background: linear-gradient(135deg, #25D366, #128C7E)">
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
             class="fixed bottom-0 inset-x-0 z-50 hidden bg-cream-50 rounded-t-3xl shadow-2xl translate-y-full transition-transform duration-300 max-h-[85vh] flex flex-col"
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
                        طلبي
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

            <div data-cart-list class="overflow-y-auto px-4 py-2 flex-1"></div>

            <div data-cart-empty class="p-10 text-center hidden">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-14 h-14 mx-auto text-ink-300 mb-2">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>
                </svg>
                <p class="text-sm text-ink-500">السلة فاضية — ضيف أصناف من القايمة</p>
            </div>

            <div class="border-t border-ink-950/8 p-4 space-y-3 shrink-0 bg-white" data-cart-footer>
                <textarea data-cart-notes rows="2" maxlength="300"
                          placeholder="ملاحظات للطلب (اختياري) — مثلاً: من غير بصل، عنواني هو..."
                          class="w-full bg-cream-100 rounded-2xl px-4 py-2.5 text-sm text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none"></textarea>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-ink-500">الإجمالي</span>
                    <span class="text-xl font-black text-ink-950" dir="ltr">
                        <span data-cart-total-big>0</span>
                        <span class="text-xs text-ink-400 font-bold">{{ $currency }}</span>
                    </span>
                </div>
                <button type="button" data-cart-send
                        data-track-click="whatsapp" data-business="{{ $business->id }}"
                        class="w-full inline-flex items-center justify-center gap-2 py-3.5 rounded-full font-extrabold text-white text-sm shadow-lg transition hover:scale-[1.01] disabled:opacity-60"
                        style="background: linear-gradient(135deg, #25D366, #128C7E)">
                    <x-icon name="whatsapp" class="w-5 h-5"/>
                    أرسل الطلب على واتساب
                </button>
                <button type="button" data-cart-clear
                        class="w-full text-xs font-bold text-blush-500 hover:underline">
                    امسح السلة
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

// ── Cart: localStorage per-business + WhatsApp send ─────────────
(function () {
    const root = document.querySelector('[data-cart-root]');
    if (!root) return;

    const bizId    = root.dataset.bizId;
    const bizName  = root.dataset.bizName;
    const waPhone  = root.dataset.waPhone;
    const currency = root.dataset.currency || 'EGP';
    const menuUrl  = root.dataset.menuUrl;
    const STORAGE_KEY = 'banhawy:cart:' + bizId;
    const NOTES_KEY   = 'banhawy:cart-notes:' + bizId;

    const bar       = root.querySelector('[data-cart-open]');
    const sheet     = root.querySelector('[data-cart-sheet]');
    const backdrop  = root.querySelector('[data-cart-backdrop]');
    const list      = root.querySelector('[data-cart-list]');
    const emptyEl   = root.querySelector('[data-cart-empty]');
    const footerEl  = root.querySelector('[data-cart-footer]');
    const sendBtn   = root.querySelector('[data-cart-send]');
    const clearBtn  = root.querySelector('[data-cart-clear]');
    const closeBtn  = root.querySelector('[data-cart-close]');
    const notesEl   = root.querySelector('[data-cart-notes]');
    const totalBig  = root.querySelector('[data-cart-total-big]');
    const badge     = root.querySelector('[data-cart-badge]');
    const summary   = root.querySelector('[data-cart-summary]');
    const totalSm   = root.querySelector('[data-cart-total]');

    /** @type {Object<string,{id:string,name:string,price:number,qty:number}>} */
    let cart = {};
    try { cart = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}') || {}; } catch (e) { cart = {}; }
    try { notesEl.value = localStorage.getItem(NOTES_KEY) || ''; } catch (e) {}

    function save() {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(cart)); } catch (e) {}
    }
    function saveNotes() {
        try { localStorage.setItem(NOTES_KEY, notesEl.value || ''); } catch (e) {}
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

    // ── Sticky bar + sheet rendering ──
    function refresh() {
        const n = totalItems();
        const t = totalPrice();
        if (n > 0) {
            bar.classList.remove('hidden');
            bar.classList.add('inline-flex');
            badge.textContent = n;
            summary.textContent = n + ' صنف' + (n > 10 ? '' : '');
            totalSm.textContent = fmt(t) + ' ' + currency;
        } else {
            bar.classList.add('hidden');
            bar.classList.remove('inline-flex');
        }

        // Sheet list
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
    }

    // ── Mutators ──
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
        try { localStorage.removeItem(NOTES_KEY); } catch (e) {}
        notesEl.value = '';
        updateAllItemUI(); refresh(); closeSheet();
    }

    // ── Wire in-list buttons ──
    document.querySelectorAll('[data-cart-item]').forEach(wrap => {
        const id = wrap.dataset.itemId;
        const name = wrap.dataset.itemName;
        const price = wrap.dataset.itemPrice;
        wrap.querySelector('[data-cart-add]')?.addEventListener('click', () => add(id, name, price));
        wrap.querySelector('[data-cart-inc]')?.addEventListener('click', () => inc(id));
        wrap.querySelector('[data-cart-dec]')?.addEventListener('click', () => dec(id));
    });

    // ── Wire in-sheet buttons (delegated) ──
    list.addEventListener('click', (e) => {
        const row = e.target.closest('[data-sheet-row]');
        if (!row) return;
        const id = row.dataset.id;
        if (e.target.closest('[data-sheet-inc]')) inc(id);
        else if (e.target.closest('[data-sheet-dec]')) dec(id);
    });

    // ── Sheet open/close ──
    function openSheet() {
        sheet.classList.remove('hidden');
        sheet.classList.add('flex');
        backdrop.classList.remove('hidden');
        requestAnimationFrame(() => sheet.style.transform = 'translateY(0)');
        document.body.style.overflow = 'hidden';
    }
    function closeSheet() {
        sheet.style.transform = '';
        backdrop.classList.add('hidden');
        document.body.style.overflow = '';
        setTimeout(() => { sheet.classList.add('hidden'); sheet.classList.remove('flex'); }, 280);
    }
    bar.addEventListener('click', openSheet);
    backdrop.addEventListener('click', closeSheet);
    closeBtn.addEventListener('click', closeSheet);
    clearBtn.addEventListener('click', () => {
        if (confirm('تمسح السلة كلها؟')) clear();
    });
    notesEl.addEventListener('input', saveNotes);

    // ── WhatsApp send ──
    sendBtn.addEventListener('click', () => {
        const ids = Object.keys(cart);
        if (ids.length === 0) return;
        const lines = ids.map(id => {
            const it = cart[id];
            return '• ' + it.name + ' × ' + it.qty + ' = ' + fmt(it.qty * it.price) + ' ' + currency;
        }).join('\n');
        const notes = (notesEl.value || '').trim();
        const msg = 'السلام عليكم 👋\n'
                  + 'حابب أطلب من ' + bizName + ':\n\n'
                  + lines + '\n\n'
                  + 'الإجمالي: ' + fmt(totalPrice()) + ' ' + currency
                  + (notes ? '\n\nملاحظات: ' + notes : '')
                  + '\n\n(الطلب من بنهاوي · ' + menuUrl + ')';
        const url = 'https://wa.me/' + waPhone + '?text=' + encodeURIComponent(msg);
        window.open(url, '_blank', 'noopener');
    });

    // ── Initial render ──
    updateAllItemUI();
    refresh();
})();
</script>
@endpush
@endsection
