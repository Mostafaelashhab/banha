@extends('layouts.app', [
    'title'       => 'احجز موعد · دكاترة وصالونات وخدمات · بنهاوي',
    'description' => 'احجز موعد إلكتروني عند الدكاترة والصالونات والخدمات في القليوبية — اختار الساعة، أكّد على واتساب.',
    'canonical'   => route('bookings.index'),
])

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-start gap-3 mb-4">
        <span class="w-12 h-12 rounded-2xl bg-mint-100 text-mint-700 grid place-items-center shrink-0">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
                <polyline points="9 16 11 18 15 14"/>
            </svg>
        </span>
        <div class="flex-1 min-w-0">
            <h1 class="text-xl font-black text-ink-950">احجز موعد</h1>
            <p class="text-xs text-ink-500 leading-relaxed mt-0.5">
                دكاترة · صالونات · صنايعية · ومراكز خدمات بتقبل حجوزات إلكترونية. اختار النشاط واحجز ساعتك.
            </p>
        </div>
    </div>

    {{-- Category facets --}}
    @if(! empty($facets))
        <div class="flex gap-2 overflow-x-auto scrollbar-hide mb-4 -mx-4 px-4">
            <a href="{{ route('bookings.index') }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-xs font-extrabold transition
                      {{ ! $categoryKey ? 'bg-coral-500 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                الكل
                <span class="opacity-70 text-[10px]">({{ $total }})</span>
            </a>
            @foreach($facets as $catKey => $count)
                @php $meta = \App\Models\Business::CATEGORIES[$catKey] ?? null; @endphp
                @if($meta)
                    <a href="{{ route('bookings.index', ['category' => $catKey]) }}"
                       class="shrink-0 px-4 py-1.5 rounded-full text-xs font-extrabold transition inline-flex items-center gap-1.5
                              {{ $categoryKey === $catKey ? 'bg-coral-500 text-white' : 'bg-white text-ink-950 ring-1 ring-ink-950/8 hover:ring-coral-500/40' }}">
                        <span>{{ $meta['emoji'] }}</span>
                        {{ $meta['label'] }}
                        <span class="opacity-70 text-[10px]">({{ $count }})</span>
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    {{-- List --}}
    @if($businesses->isEmpty())
        <div class="card-light p-10 text-center">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-16 h-16 mx-auto text-ink-300 mb-3">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <h3 class="font-extrabold text-ink-950 mb-1">مفيش أنشطة بتقبل حجز لسه</h3>
            <p class="text-xs text-ink-500 leading-relaxed">
                لو انت صاحب عيادة / صالون / خدمة، فعّل الحجز الإلكتروني من
                <a href="{{ auth()->check() ? route('directory.mine') : route('login') }}" class="text-coral-600 font-bold hover:underline">لوحة نشاطك</a>
                وخلي العملاء يحجزوا أوقاتهم من على الموبايل.
            </p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($businesses as $b)
                @php
                    $cm = $b->categoryMeta();
                    $sm = $b->subTypeMeta();
                @endphp
                <a href="{{ route('booking.show', $b) }}"
                   class="card-light p-3 flex items-center gap-3 hover:bg-cream-100 transition group">
                    @if($b->photo_url)
                        <img src="{{ $b->photo_url }}" alt="" loading="lazy" class="w-14 h-14 rounded-2xl object-cover shrink-0">
                    @else
                        <span class="w-14 h-14 rounded-2xl grid place-items-center shrink-0 text-lg"
                              style="background: {{ $cm['color'] }}1A; color: {{ $cm['color'] }};">
                            <x-icon :name="$cm['icon']" class="w-6 h-6"/>
                        </span>
                    @endif

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-extrabold text-ink-950 truncate">{{ $b->name }}</span>
                            @if($b->is_verified)
                                <span class="inline-flex items-center text-mint-700" title="موثّق">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5">
                                        <path d="M12 2 4 6v6c0 5 3.4 9.6 8 11 4.6-1.4 8-6 8-11V6Zm-1 13-3.5-3.5L9 10l2 2 5-5 1.5 1.5Z"/>
                                    </svg>
                                </span>
                            @endif
                        </div>
                        <div class="text-[11px] text-ink-500 truncate">
                            {{ $sm['label'] ?? '' }}
                            @if($b->zone)
                                <span class="text-ink-400">·</span>
                                {{ $b->zone->name }}
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-1 text-[10px]">
                            @if($b->ratings_count > 0)
                                <span class="inline-flex items-center gap-0.5 text-honey-700 font-bold">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                                    {{ $b->rating_avg }}
                                    <span class="text-ink-400 font-normal">({{ $b->ratings_count }})</span>
                                </span>
                            @endif
                            <span class="text-ink-500 font-bold">
                                {{ $b->booking_slot_minutes }} دقيقة/موعد
                            </span>
                            @if($b->isOpenNow() === true)
                                <span class="inline-flex items-center gap-1 text-mint-700 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-mint-500"></span>
                                    مفتوح
                                </span>
                            @endif
                        </div>
                    </div>

                    <span class="shrink-0 px-3 py-1.5 rounded-full bg-mint-500 text-white text-[10px] font-extrabold group-hover:bg-mint-600 transition inline-flex items-center gap-1">
                        احجز
                        <x-icon name="arrow-left" class="w-3 h-3"/>
                    </span>
                </a>
            @endforeach
        </div>

        <p class="text-[10px] text-ink-400 text-center mt-4">
            عرض {{ $businesses->count() }} نشاط
            @if($total > $businesses->count())
                من أصل {{ $total }} (أحدث أول)
            @endif
        </p>
    @endif

    {{-- Owner CTA --}}
    <div class="card-light p-4 mt-6 bg-coral-50 ring-1 ring-coral-500/15">
        <div class="flex items-start gap-3">
            <span class="w-9 h-9 rounded-2xl bg-coral-500 text-white grid place-items-center shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-extrabold text-ink-950">عندك نشاط بيقبل حجوزات؟</div>
                <p class="text-[11px] text-ink-500 leading-relaxed mt-0.5">
                    فعّل الحجز الإلكتروني وعمّل لوحة لإدارة المواعيد — مجاناً.
                </p>
                <a href="{{ auth()->check() ? route('directory.mine') : route('login') }}"
                   class="inline-flex items-center gap-1 text-xs font-extrabold text-coral-600 hover:underline mt-1">
                    فعّل الحجز لنشاطك ←
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
