@php
    $lk = $listing->kindMeta();
    $lc = $listing->categoryMeta();
    $listingZone = $listing->zone->name ?? 'بنها';
    $listingTitle = $listing->title.' · '.$lk['label'].' في '.$listingZone.' | بنهاوي';
    $listingDesc  = trim(implode(' · ', array_filter([
        $listing->title,
        $lk['label'].' '.$lc['label'],
        $listingZone,
        in_array($listing->kind, ['sale','buy'], true) ? $listing->priceLabel() : null,
        $listing->description ? mb_substr(strip_tags($listing->description), 0, 120) : null,
    ])));
@endphp

@extends('layouts.app', [
    'title'       => $listingTitle,
    'description' => $listingDesc,
    'ogImage'     => $listing->photo_url,
    'ogType'      => 'product',
    'canonical'   => route('marketplace.show', $listing),
    'keywords'    => $listing->title.', '.$lk['label'].', '.$lc['label'].', '.$listingZone.', بنها, بيع وشراء',
])

@push('json-ld')
@php
    $product = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $listing->title,
        'description' => $listing->description ? mb_substr(strip_tags($listing->description), 0, 500) : $listing->title,
        'image'       => $listing->photo_url ?: asset('icons/icon-512.png'),
        'category'    => $lc['label'] ?? null,
        'brand'       => ['@type' => 'Brand', 'name' => 'بنهاوي'],
    ];
    if (in_array($listing->kind, ['sale','buy'], true) && $listing->price) {
        $product['offers'] = [
            '@type'         => 'Offer',
            'priceCurrency' => $listing->currency ?: 'EGP',
            'price'         => $listing->price,
            'availability'  => $listing->status === 'active'
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'url'           => route('marketplace.show', $listing),
            'seller'        => ['@type' => 'Person', 'name' => $listing->user->username ?? 'بنهاوي'],
        ];
    }
    $listingCrumbs = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'بنهاوي', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'بيع وشراء', 'item' => route('marketplace.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $lk['label'], 'item' => route('marketplace.index', ['kind' => $listing->kind])],
            ['@type' => 'ListItem', 'position' => 4, 'name' => $listing->title, 'item' => route('marketplace.show', $listing)],
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($product, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode($listingCrumbs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@php
    $km = $listing->kindMeta();
    $cm = $listing->categoryMeta();
    $isSaved = auth()->check() && \App\Models\Bookmark::exists_(auth()->id(), 'listing', $listing->id);
@endphp

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('marketplace.index', ['kind' => $listing->kind]) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-base font-bold text-ink-500">{{ $km['label'] }} · {{ $cm['label'] }}</h1>

        <button type="button" class="ms-auto w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950 hover:bg-cream-100 transition"
                data-share data-share-url="{{ route('marketplace.show', $listing) }}"
                data-share-title="{{ $listing->title }}"
                data-share-text="{{ $listing->priceLabel() }}"
                aria-label="شارك">
            <x-icon name="share" class="w-4 h-4"/>
        </button>

        @auth
            <button type="button" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950 hover:bg-cream-100 transition {{ $isSaved ? 'text-coral-500' : '' }}"
                    data-bookmark data-type="listing" data-id="{{ $listing->id }}" data-saved="{{ $isSaved ? '1' : '0' }}"
                    aria-label="حفظ">
                <x-icon name="heart" :filled="$isSaved" class="w-4 h-4"/>
            </button>
        @endauth
    </div>

    @if($listing->photo_url)
        <img src="{{ $listing->photo_url }}" alt="" class="w-full rounded-3xl mb-3 max-h-[500px] object-contain bg-cream-100">
    @endif

    <div class="card-light p-5 mb-3">
        <div class="flex items-start gap-3 mb-3">
            <span class="px-3 py-1 rounded-full pill-{{ $km['tone'] }} text-xs font-bold inline-flex items-center gap-1.5">
                <x-icon :name="$km['icon']" class="w-3 h-3"/> {{ $km['label'] }}
            </span>
            <span class="text-[11px] text-ink-400 ms-auto">{{ $listing->views }} مشاهدة · {{ $listing->created_at->diffForHumans() }}</span>
        </div>

        <h2 class="text-2xl font-black text-ink-950 mb-1">{{ $listing->title }}</h2>

        @if(in_array($listing->kind, ['sale','buy'], true))
            <div class="text-coral-600 font-black text-3xl mb-2">{{ $listing->priceLabel() }}
                @if($listing->negotiable && $listing->price)
                    <span class="text-ink-400 font-normal text-xs">قابل للمفاوضة</span>
                @endif
            </div>
        @endif

        <div class="text-sm text-ink-500 mb-3">
            @if($listing->zone) <x-icon name="map-pin" class="w-3.5 h-3.5 inline"/> {{ $listing->zone->name }} @endif
        </div>

        @if($listing->description)
            <p class="text-ink-950 text-sm leading-relaxed whitespace-pre-line mt-3">{{ $listing->description }}</p>
        @endif

        {{-- Owner --}}
        <a href="{{ route('profile.show', $listing->user->username) }}" class="flex items-center gap-2 mt-4 pt-3 border-t border-ink-950/8">
            <x-avatar :user="$listing->user" size="sm"/>
            <span class="text-xs text-ink-500">صاحب الإعلان: <span class="font-bold text-ink-950">{{ '@'.$listing->user->username }}</span></span>
        </a>

        {{-- CTAs --}}
        <div class="grid grid-cols-2 gap-2 mt-4">
            @auth
                @if(auth()->id() !== $listing->user_id)
                    <a href="{{ route('chat.open', $listing->user) }}" class="btn-primary justify-center !py-3 text-sm col-span-2">
                        <x-icon name="comment" class="w-4 h-4"/>
                        ابعت رسالة للبائع
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-primary justify-center !py-3 text-sm col-span-2">
                    <x-icon name="comment" class="w-4 h-4"/>
                    ادخل عشان تكلّم البائع
                </a>
            @endauth

            @if($listing->contact_phone)
                <a href="tel:{{ $listing->contact_phone }}" class="card-light p-3 text-coral-700 font-bold text-sm flex items-center justify-center gap-2 hover:bg-coral-100 transition">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    اتصل
                </a>
            @endif
            @if($listing->contact_whatsapp)
                <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($listing->contact_whatsapp) }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 py-3 rounded-full font-bold text-white text-sm transition hover:scale-[1.02]"
                   style="background: linear-gradient(135deg, #25D366, #128C7E)">
                    <x-icon name="whatsapp" class="w-4 h-4"/> واتساب
                </a>
            @endif
        </div>
    </div>

    @auth
        @if(auth()->id() === $listing->user_id || auth()->user()->is_admin)
            <div class="card-light p-4 space-y-2">
                <a href="{{ route('marketplace.edit', $listing) }}"
                   class="card-light p-3 w-full text-coral-600 font-bold text-sm hover:bg-coral-100/50 transition flex items-center justify-center gap-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z"/>
                    </svg>
                    عدّل الإعلان
                </a>
                @if($listing->status === 'active' && in_array($listing->kind, ['sale'], true))
                    <form method="POST" action="{{ route('marketplace.sold', $listing) }}">
                        @csrf
                        <button class="card-light p-3 w-full text-mint-700 font-bold text-sm hover:bg-mint-100/50 transition flex items-center justify-center gap-2">
                            <x-icon name="check" class="w-4 h-4"/> تأشير "اتباع"
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('marketplace.destroy', $listing) }}"
                      data-confirm="حذف الإعلان؟" data-confirm-action="احذف" data-confirm-tone="danger">
                    @csrf @method('DELETE')
                    <button class="card-light p-3 w-full text-blush-500 font-bold text-sm hover:bg-blush-100/50 transition flex items-center justify-center gap-2">
                        <x-icon name="trash" class="w-4 h-4"/> احذف الإعلان
                    </button>
                </form>
            </div>
        @endif
    @endauth
</div>
@endsection
