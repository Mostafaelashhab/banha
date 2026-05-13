@php
    /** @var \App\Models\Business $business */
    $cm = $business->categoryMeta();
    $sm = $business->subTypeMeta();
    $isPromoted = $business->isPromoted();
    $isVerified = $business->is_verified;
    $showUrl = route('directory.show', $business);
@endphp

<div class="relative bg-white rounded-3xl overflow-hidden hover:shadow-xl transition group {{ $isPromoted ? 'biz-card-promoted' : '' }}">

    {{-- Sponsored strip --}}
    @if($isPromoted)
        <div class="flex items-center gap-2 px-4 py-2 bg-honey-100">
            <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-honey-700"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
            <span class="text-[11px] font-extrabold text-honey-700 tracking-wide">مُموَّل · إعلان</span>
            <span class="ms-auto text-[10px] text-ink-400 font-bold">{{ $business->views_count }} مشاهدة</span>
        </div>
    @endif

    {{-- Hero photo --}}
    <a href="{{ $showUrl }}" class="block relative" aria-label="{{ $business->name }}">
        <x-business-cover :business="$business" class="w-full aspect-[16/9]" size="md"/>

        @if($isVerified)
            <div class="absolute top-2.5 start-2.5 z-20">
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-1 rounded-full bg-mint-500 text-white shadow-md">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3"><path d="M12 0a12 12 0 1 0 0 24 12 12 0 0 0 0-24zm5.7 9.3-7 7a1 1 0 0 1-1.4 0l-3-3a1 1 0 0 1 1.4-1.4L10 14.6l6.3-6.3a1 1 0 0 1 1.4 1.4z"/></svg>
                    موثّق
                </span>
            </div>
        @endif

        @if($business->ratings_count > 0)
            <div class="absolute top-2.5 end-2.5 z-20 inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white/95 backdrop-blur-sm shadow-md">
                <svg viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3 text-honey-500"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                <span class="text-[12px] font-extrabold text-ink-950">{{ $business->rating_avg }}</span>
                <span class="text-[10px] text-ink-500">({{ $business->ratings_count }})</span>
            </div>
        @endif

        @if($business->is_24h || $business->has_menu)
            <div class="absolute bottom-2.5 start-2.5 z-20 flex gap-1.5">
                @if($business->is_24h)
                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-1 rounded-full bg-ink-950/85 text-white backdrop-blur-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-mint-400 animate-pulse"></span>
                        ٢٤ ساعة
                    </span>
                @endif
                @if($business->has_menu)
                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-1 rounded-full bg-coral-500 text-white shadow-md">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                            <rect x="6" y="3" width="12" height="18" rx="2"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/>
                        </svg>
                        منيو
                    </span>
                @endif
            </div>
        @endif
    </a>

    {{-- Body --}}
    <div class="p-4">
        {{-- Title section is its own link (separate from action buttons below) --}}
        <a href="{{ $showUrl }}" class="block">
            <div class="mb-2">
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-md max-w-full"
                      style="background: {{ $cm['color'] }}14; color: {{ $cm['color'] }};">
                    <x-icon :name="$sm['icon'] ?? 'bag'" class="w-2.5 h-2.5 shrink-0"/>
                    <span class="truncate">{{ $business->displayType() }}</span>
                </span>
            </div>

            <h3 class="block text-base font-extrabold text-ink-950 leading-snug overflow-hidden text-ellipsis whitespace-nowrap mb-1.5">{{ $business->name }}</h3>

            @if($business->zone || $business->address)
                <div class="flex items-center gap-1.5 text-[11px] text-ink-500 mb-2 overflow-hidden">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3 text-ink-400 shrink-0">
                        <path d="M20 10c0 7-8 13-8 13s-8-6-8-13a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span class="overflow-hidden text-ellipsis whitespace-nowrap min-w-0 flex-1">{{ $business->address ?: ($business->zone->name ?? '') }}</span>
                </div>
            @endif

            @if($business->description)
                <p class="text-[12px] text-ink-500 overflow-hidden text-ellipsis whitespace-nowrap mb-3">{{ $business->description }}</p>
            @else
                <div class="h-2"></div>
            @endif
        </a>

        {{-- Bottom action row — separate <a> tags, NOT nested inside the card link --}}
        <div class="flex items-center gap-2">
            @if($business->phone)
                <a href="tel:{{ $business->phone }}" data-track-click="phone" data-business="{{ $business->id }}"
                   class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 rounded-xl bg-coral-500 hover:bg-coral-600 text-white text-sm font-extrabold transition active:scale-[.98]">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-4 h-4">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    اتصل
                </a>
            @endif
            @if($business->whatsapp)
                <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}" target="_blank"
                   data-track-click="whatsapp" data-business="{{ $business->id }}"
                   class="w-11 h-11 rounded-xl grid place-items-center text-white shrink-0 transition hover:scale-105"
                   style="background: linear-gradient(135deg, #25D366, #128C7E);" aria-label="واتساب">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9z"/>
                    </svg>
                </a>
            @endif
            <a href="{{ $showUrl }}" aria-label="التفاصيل"
               class="w-11 h-11 rounded-xl grid place-items-center bg-cream-100 hover:bg-cream-200 text-ink-700 shrink-0 transition">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-4 h-4">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>
        </div>
    </div>
</div>
