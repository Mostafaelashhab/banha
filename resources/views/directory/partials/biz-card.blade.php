@php
    /** @var \App\Models\Business $business */
    $cm       = $business->categoryMeta();
    $color    = $cm['color'] ?? '#FF7A4D';
    $icon     = $cm['icon'] ?? 'bag';
    $rating   = (float) ($business->rating_avg ?? 0);
    $ratings  = (int) ($business->ratings_count ?? 0);
    $subtitle = $business->displayType() ?: ($cm['label'] ?? '');

    // Cover image — try photo_url, then first uploaded photo, then null (use category default)
    $cover = $business->photo_url
        ?: optional($business->relationLoaded('photos') ? $business->photos->first() : null)->url;
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

        @if($business->is_verified)
            <span class="biz-card__verified">
                <x-icon name="check" class="w-2.5 h-2.5"/> موثّق
            </span>
        @endif
    </div>

    <div class="biz-card__info">
        <div class="biz-card__head">
            <span class="biz-card__avatar">
                <span class="biz-card__avatar-fallback"
                      style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}cc);">
                    {{ mb_substr($business->name, 0, 1) }}
                </span>
                @if($cover)
                    <img src="{{ $cover }}" alt="" loading="lazy"
                         onerror="this.style.display='none'">
                @endif
            </span>
            <div class="biz-card__head-text">
                <div class="biz-card__title">{{ $business->name }}</div>
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
