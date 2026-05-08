@props(['tier' => 'none', 'size' => 'sm'])

@if($tier === 'gold')
    <span {{ $attributes->merge(['class' => 'v-badge v-badge-gold']) }} title="موثّق ذهبي">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
    </span>
@elseif($tier === 'silver')
    <span {{ $attributes->merge(['class' => 'v-badge v-badge-silver']) }} title="موثّق فضي">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
    </span>
@endif
