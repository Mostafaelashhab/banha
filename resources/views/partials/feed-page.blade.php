{{-- Cards-only fragment used for both initial render and AJAX appends --}}
@foreach($posts as $post)
    @include('partials.post-card', ['post' => $post, 'userVotes' => $userVotes])
@endforeach

{{-- Sentinel + meta about the next page --}}
<div data-feed-end
     data-next-url="{{ $posts->hasMorePages() ? $posts->nextPageUrl() : '' }}"
     data-has-more="{{ $posts->hasMorePages() ? '1' : '0' }}"></div>
