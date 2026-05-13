@php
    /** @var \App\Models\Business $business */
    $cm       = $business->categoryMeta();
    $color    = $cm['color'] ?? '#2D5BFF';
    $icon     = $cm['icon'] ?? 'bag';
    $rating   = (float) ($business->rating_avg ?? 0);
    $ratings  = (int) ($business->ratings_count ?? 0);
    $subtitle = $business->displayType() ?: ($cm['label'] ?? '');

    // Cover — gallery photo first, then the main photo_url as a fallback.
    // If neither, the category gradient default shows.
    // The small avatar below uses photo_url separately (the business logo).
    $cover = optional($business->relationLoaded('photos') ? $business->photos->first() : null)->url
           ?? $business->photo_url
           ?? null;
@endphp
<a href="{{ route('directory.show', $business) }}" class="biz-card group">
    <div class="biz-card__photo">
        {{-- Category default — a gradient + big icon, used when no photo exists or one fails to load --}}
        <div class="biz-card__photo-default"
             style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}aa);">
            <x-icon :name="$icon" class="biz-card__photo-default-icon"/>
        </div>
        @if($cover)
            <img src="{{ $cover }}" alt="{{ $business->name }}" loading="lazy"
                 onerror="this.style.display='none'">
        @endif

    </div>

    <div class="biz-card__info">
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
                    @if($business->is_verified)
                        <x-verified-badge tier="gold" class="biz-card__title-badge"/>
                    @endif
                </div>
                <div class="biz-card__subtitle">{{ $subtitle }}</div>
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
    </div>
</a>
