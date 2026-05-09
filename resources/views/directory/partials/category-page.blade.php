@foreach($businesses as $b)
    @include('directory.partials.business-row', ['business' => $b])
@endforeach

<div data-feed-end
     data-next-url="{{ $businesses->hasMorePages() ? $businesses->nextPageUrl() : '' }}"
     data-has-more="{{ $businesses->hasMorePages() ? '1' : '0' }}"></div>
