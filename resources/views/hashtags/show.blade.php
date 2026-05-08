@extends('layouts.app', ['title' => '#' . $hashtag->tag . ' · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card-light p-5 mb-4 text-center">
        <div class="text-3xl font-black text-coral-500 mb-1">#{{ $hashtag->tag }}</div>
        <div class="text-ink-500 text-sm">{{ $hashtag->uses_count }} بوست</div>
    </div>

    @if($posts->isEmpty())
        <div class="card-light p-10 text-center">
            <p class="text-ink-500 text-sm">مفيش بوستات بالـهاشتاج ده لسه.</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($posts as $p)
                @include('partials.post-card', ['post' => $p, 'userVotes' => $userVotes])
            @endforeach
        </div>
        <div class="mt-4">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
