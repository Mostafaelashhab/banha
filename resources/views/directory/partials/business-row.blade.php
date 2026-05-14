@php
    /** @var \App\Models\Business $business */
    $cm       = $business->categoryMeta();
    $sm       = $business->subTypeMeta();
    $color    = $cm['color'] ?? '#2D5BFF';
    $icon     = $cm['icon'] ?? 'bag';
    $rating   = (float) ($business->rating_avg ?? 0);
    $ratings  = (int) ($business->ratings_count ?? 0);
    $subtitle = $business->displayType() ?: ($cm['label'] ?? '');
    $isPromoted = $business->isPromoted();
    $isVerified = $business->is_verified;
    $showUrl  = route('directory.show', $business);

    $cover = optional($business->relationLoaded('photos') ? $business->photos->first() : null)->url
           ?? $business->photo_url
           ?? null;
@endphp

<div class="biz-card {{ $isPromoted ? 'biz-card--promoted' : '' }}">
    {{-- Photo --}}
    <a href="{{ $showUrl }}" class="block biz-card__photo" aria-label="{{ $business->name }}">
        <div class="biz-card__photo-default"
             style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}aa);">
            <x-icon :name="$icon" class="biz-card__photo-default-icon"/>
        </div>
        @if($cover)
            <img src="{{ $cover }}" alt="{{ $business->name }}" loading="lazy"
                 onerror="this.style.display='none'">
        @endif

        @if($business->is_24h)
            <div class="biz-card__photo-chips">
                <span class="biz-card__chip biz-card__chip--live">
                    <span class="biz-card__chip-dot"></span>
                    ٢٤ ساعة
                </span>
            </div>
        @endif
    </a>

    {{-- Info --}}
    <div class="biz-card__info">
        <a href="{{ $showUrl }}" class="block" style="color: inherit; text-decoration: none;">
            <div class="biz-card__head">
                <span class="biz-card__avatar">
                    <span class="biz-card__avatar-fallback"
                          style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}cc);">
                        {{ mb_substr($business->name, 0, 1) }}
                    </span>
                    @if($business->photo_url)
                        <img src="{{ $business->photo_url }}" alt="" loading="lazy"
                             onerror="this.style.display='none'">
                    @endif
                </span>
                <div class="biz-card__head-text">
                    <div class="biz-card__title">
                        <span class="biz-card__name">{{ $business->name }}</span>
                        @if($isVerified)
                            <x-verified-badge tier="gold" class="biz-card__title-badge"/>
                        @endif
                        @if($isPromoted)
                            <x-promoted-badge class="biz-card__title-badge"/>
                        @endif
                    </div>
                    <div class="biz-card__subtitle">
                        <x-icon :name="$sm['icon'] ?? $icon" class="biz-card__subtitle-icon"/>
                        <span>{{ $subtitle }}</span>
                    </div>
                </div>
            </div>

            <div class="biz-card__stats">
                <span class="biz-card__stat biz-card__stat--rating">
                    <span class="biz-card__stat-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                    </span>
                    {{ $rating > 0 ? number_format($rating, 1) : '—' }}
                </span>
                <span class="biz-card__stat">
                    <span class="biz-card__stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </span>
                    {{ $ratings }} تقييم
                </span>
                @if($business->zone)
                    <span class="biz-card__stat biz-card__stat--zone">
                        <span class="biz-card__stat-icon">
                            <x-icon name="map-pin"/>
                        </span>
                        {{ $business->zone->name }}
                    </span>
                @endif
            </div>
        </a>

        {{-- Action row --}}
        @if($business->phone || $business->whatsapp)
            <div class="biz-card__actions">
                @if($business->phone)
                    <a href="tel:{{ $business->phone }}" data-track-click="phone" data-business="{{ $business->id }}"
                       class="biz-card__action biz-card__action--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-4 h-4">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        اتصل
                    </a>
                @endif
                @if($business->whatsapp)
                    <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}" target="_blank"
                       data-track-click="whatsapp" data-business="{{ $business->id }}"
                       class="biz-card__action biz-card__action--wa" aria-label="واتساب">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 2.9z"/>
                        </svg>
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
