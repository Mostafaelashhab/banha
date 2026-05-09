{{-- Unified feed renderer: posts + alerts + businesses (ads) + prices --}}
@foreach($items as $item)
    @switch($item['kind'])
        @case('post')
            @include('partials.post-card', ['post' => $item['data'], 'userVotes' => $userVotes])
            @break
        @case('alert')
            @include('partials.alert-feed-card', ['alert' => $item['data']])
            @break
        @case('business')
            @include('partials.business-feed-card', ['business' => $item['data'], 'isAd' => $item['is_ad'] ?? false])
            @break
        @case('price')
            @include('partials.price-feed-card', ['price' => $item['data']])
            @break
        @case('listing')
            @include('partials.listing-feed-card', ['listing' => $item['data']])
            @break
    @endswitch
@endforeach

{{-- Sentinel + meta about the next page --}}
<div data-feed-end
     data-next-url="{{ $paginator->hasMorePages() ? $paginator->nextPageUrl() : '' }}"
     data-has-more="{{ $paginator->hasMorePages() ? '1' : '0' }}"></div>
