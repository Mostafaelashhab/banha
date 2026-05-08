@extends('layouts.app', ['title' => $business->name . ' · دليل بنها'])

@php
    $cm = $business->categoryMeta();
    $sm = $business->subTypeMeta();
@endphp

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.category', $business->category) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-base font-bold text-ink-500">{{ $cm['label'] }} · {{ $sm['label'] }}</h1>
        @auth
            @if(auth()->id() === $business->owner_user_id)
                <a href="{{ route('directory.edit', $business) }}" class="ms-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-coral-100 text-coral-700 text-xs font-bold hover:bg-coral-500 hover:text-white transition">
                    <x-icon name="more" class="w-3.5 h-3.5"/>
                    عدّل
                </a>
            @endif
        @endauth
    </div>

    {{-- Hero --}}
    <div class="card-light p-5 mb-3">
        <div class="flex items-start gap-4">
            <span class="w-16 h-16 rounded-2xl grid place-items-center text-3xl shrink-0"
                  style="background: {{ $cm['color'] }}20; border: 1px solid {{ $cm['color'] }}50">
                {{ $business->emoji ?: $sm['emoji'] }}
            </span>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <h2 class="text-xl md:text-2xl font-black text-ink-950">{{ $business->name }}</h2>
                    @if($business->is_verified)
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-mint-100 text-mint-700">
                            <x-icon name="check" class="w-3 h-3"/>
                            موثّق
                        </span>
                    @endif
                </div>
                <div class="text-sm text-ink-500 mt-0.5">
                    {{ $sm['label'] }}
                    @if($business->zone) · {{ $business->zone->name }} @endif
                </div>
                @if($business->ratings_count > 0)
                    <div class="mt-2 inline-flex items-center gap-1 text-sm">
                        <span class="text-coral-500">★</span>
                        <span class="font-bold text-ink-950">{{ $business->rating_avg }}</span>
                        <span class="text-ink-400 text-xs">({{ $business->ratings_count }} تقييم)</span>
                    </div>
                @endif
            </div>
        </div>

        @if($business->description)
            <p class="text-ink-950 text-sm leading-relaxed mt-4">{{ $business->description }}</p>
        @endif

        {{-- Action buttons --}}
        <div class="grid grid-cols-2 gap-2 mt-4">
            @if($business->phone)
                <a href="tel:{{ $business->phone }}" class="btn-primary justify-center !py-3 text-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    اتصل
                </a>
            @endif
            @if($business->whatsapp)
                <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 py-3 px-5 rounded-full font-bold text-white text-sm transition hover:scale-[1.02]"
                   style="background: linear-gradient(135deg, #25D366, #128C7E); box-shadow: 0 12px 24px -10px rgba(37, 211, 102, .55)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                        <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9z"/>
                    </svg>
                    واتساب
                </a>
            @endif
        </div>
    </div>

    {{-- Info --}}
    <div class="card-light p-5 mb-3 space-y-3">
        @if($business->address)
            <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-xl pill-coral grid place-items-center shrink-0">
                    <x-icon name="map-pin" class="w-4 h-4"/>
                </span>
                <div class="flex-1">
                    <div class="text-[11px] text-ink-500">العنوان</div>
                    <div class="text-sm font-bold text-ink-950">{{ $business->address }}</div>
                </div>
            </div>
        @endif

        @if($business->hours || $business->is_24h)
            <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-xl {{ $business->is_24h ? 'pill-mint' : 'pill-coral' }} grid place-items-center shrink-0">
                    <x-icon name="bell" class="w-4 h-4"/>
                </span>
                <div class="flex-1">
                    <div class="text-[11px] text-ink-500">المواعيد</div>
                    <div class="text-sm font-bold text-ink-950">
                        @if($business->is_24h)
                            <span class="text-mint-700">٢٤ ساعة · مفتوح دلوقتي</span>
                        @else
                            {{ $business->hours }}
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if($business->phone)
            <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-xl pill-blush grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                </span>
                <div class="flex-1">
                    <div class="text-[11px] text-ink-500">رقم التليفون</div>
                    <div class="text-sm font-bold text-ink-950" dir="ltr">{{ $business->phone }}</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Similar --}}
    @if($similar->isNotEmpty())
        <h3 class="text-sm font-extrabold text-ink-950 mb-3 mt-5">{{ $sm['label'] }} تاني في نفس المنطقة</h3>
        <div class="space-y-2">
            @foreach($similar as $b)
                @include('directory.partials.business-row', ['business' => $b])
            @endforeach
        </div>
    @endif

    {{-- Disclaimer --}}
    <div class="card-light !shadow-none border-coral-500/20 bg-coral-50 p-3 mt-4">
        <p class="text-[11px] text-ink-500 leading-relaxed">
            <b class="text-ink-950">ملاحظة:</b>
            بنهاوي بيعرض النشاطات للمعلوماتية فقط. تأكّد قبل الشغل/الشراء، ولو حصلت مشكلة بلّغ من زر "تبليغ".
        </p>
    </div>
</div>
@endsection
