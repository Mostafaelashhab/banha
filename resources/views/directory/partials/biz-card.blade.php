@php
    /** @var \App\Models\Business $business */
    $cm       = $business->categoryMeta();
    $color    = $cm['color'] ?? '#FF7A4D';
    $rating   = (float) ($business->rating_avg ?? 0);
    $ratings  = (int) ($business->ratings_count ?? 0);
    $subtitle = $business->displayType() ?: ($cm['label'] ?? '');
@endphp
<a href="{{ route('directory.show', $business) }}" class="biz-card group">
    <div class="biz-card__photo">
        <span class="biz-card__photo-fallback"
              style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}cc);">
            {{ mb_substr($business->name, 0, 1) }}
        </span>
        @if($business->photo_url)
            <img src="{{ $business->photo_url }}" alt="{{ $business->name }}" loading="lazy"
                 onerror="this.style.display='none'">
        @endif

        @if($business->is_verified)
            <span class="biz-card__verified" title="موثّق" aria-label="موثّق">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.19 1.91-2.19 3.34s.88 2.67 2.19 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2L6.8 12.46l1.41-1.42 2.26 2.26 4.8-5.23 1.47 1.36-6.2 6.77z"/>
                </svg>
            </span>
        @endif
    </div>

    <div class="biz-card__info">
        <div class="biz-card__title">{{ $business->name }}</div>
        <div class="biz-card__subtitle">{{ $subtitle }}</div>

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
