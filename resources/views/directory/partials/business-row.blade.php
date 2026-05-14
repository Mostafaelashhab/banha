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

        @if($isPromoted)
            <button type="button" class="biz-card__promoted-tag"
                    data-promoted-info
                    aria-label="ممول">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.19 1.91-2.19 3.34s.88 2.67 2.19 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zM12 6.8l1.45 3.49 3.75.3-2.85 2.45.87 3.66L12 14.78l-3.22 1.92.87-3.66L6.8 10.59l3.75-.3z"/>
                </svg>
                ممول
            </button>
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
                @if($business->is_24h)
                    <span class="biz-card__stat biz-card__stat--live" aria-label="٢٤ ساعة">
                        <span class="biz-card__stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/>
                                <polyline points="12 7 12 12 15 14"/>
                            </svg>
                        </span>
                        ٢٤ ساعة
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
