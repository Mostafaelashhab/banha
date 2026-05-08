@php
    use App\Support\AnonSeed;
    /** @var \App\Models\Post $post */
    $isAnon   = $post->is_anonymous;
    $display  = $isAnon ? ($post->anon_seed ?? 'مجهول') : $post->user->username;
    $color    = AnonSeed::avatarColor($display);
    $initial  = AnonSeed::initial($display);
    $score    = (int) $post->upvotes - (int) $post->downvotes;
    $myVote   = $userVotes[$post->id] ?? 0;
    $cat      = \App\Models\Post::CATEGORIES[$post->category] ?? $post->category;
@endphp

<article class="card-light p-4 mb-3" data-post-id="{{ $post->id }}">
    {{-- header --}}
    <header class="flex items-center gap-2.5 mb-3">
        <span class="w-9 h-9 rounded-full grid place-items-center text-white font-bold text-sm shrink-0"
              style="background: {{ $color }}">{{ $initial }}</span>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 text-sm font-bold text-ink-950">
                @if($isAnon)
                    <x-icon name="mask" class="w-3.5 h-3.5 text-coral-600"/>
                @endif
                <span class="truncate">{{ $display }}</span>
                @if($post->zone)
                    <span class="text-ink-400 font-normal text-xs">· {{ $post->zone->name }}</span>
                @endif
            </div>
            <div class="text-[11px] text-ink-400">{{ $post->created_at->diffForHumans() }} · {{ $cat }}</div>
        </div>
        <span class="pill-coral text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0">{{ $cat }}</span>
    </header>

    {{-- body --}}
    <a href="{{ route('posts.show', $post) }}" class="block">
        @if($post->title)
            <h3 class="font-extrabold text-ink-950 mb-1.5 leading-tight">{{ $post->title }}</h3>
        @endif
        <p class="text-ink-950 text-[15px] leading-relaxed whitespace-pre-line">{{ Str::limit($post->body, 280) }}</p>
    </a>

    {{-- actions --}}
    <footer class="mt-4 flex items-center gap-1 text-ink-500 text-sm">
        <form method="POST" action="{{ route('posts.vote', $post) }}" class="inline-flex items-center bg-cream-100 rounded-full">
            @csrf
            <button name="value" value="{{ $myVote === 1 ? 0 : 1 }}"
                    class="p-2 rounded-full hover:bg-coral-100 transition {{ $myVote === 1 ? 'text-coral-600' : '' }}"
                    aria-label="رفع">
                <x-icon name="arrow-right" class="w-4 h-4 -rotate-90"/>
            </button>
            <span class="px-1 font-bold text-ink-950 min-w-[1.5rem] text-center">{{ $score }}</span>
            <button name="value" value="{{ $myVote === -1 ? 0 : -1 }}"
                    class="p-2 rounded-full hover:bg-blush-100 transition {{ $myVote === -1 ? 'text-blush-500' : '' }}"
                    aria-label="نزل">
                <x-icon name="arrow-right" class="w-4 h-4 rotate-90"/>
            </button>
        </form>

        <a href="{{ route('posts.show', $post) }}#comments"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full hover:bg-cream-200 transition">
            <x-icon name="bell" class="w-4 h-4"/>
            <span class="font-bold">{{ $post->comments_count }}</span>
        </a>

        <button type="button"
                data-report="{{ route('posts.report', $post) }}"
                class="ms-auto p-2 rounded-full hover:bg-cream-200 transition text-ink-400 hover:text-blush-500"
                aria-label="بلّغ عن البوست">
            <x-icon name="flag" class="w-4 h-4"/>
        </button>
    </footer>
</article>
