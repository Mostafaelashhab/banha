@php
    /** @var \App\Models\Post $post */
    $isAnon   = $post->is_anonymous;
    $display  = $isAnon ? ($post->anon_seed ?? 'مجهول') : $post->user->username;
    $score    = (int) $post->upvotes - (int) $post->downvotes;
    $myVote   = $userVotes[$post->id] ?? 0;
    $cat      = \App\Models\Post::CATEGORIES[$post->category] ?? $post->category;
    $tier     = $isAnon ? 'none' : ($post->user->verification_tier ?? 'none');
    $tierCard = match ($tier) {
        'gold'   => 'tier-gold',
        'silver' => 'tier-silver',
        default  => '',
    };
@endphp

<article class="card-light {{ $tierCard }} p-4 mb-3 relative" data-post-id="{{ $post->id }}">
    {{-- header --}}
    <header class="flex items-center gap-2.5 mb-3">
        <x-avatar :user="$isAnon ? null : $post->user" :name="$display" :anon="$isAnon" size="md"/>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 text-sm font-bold text-ink-950">
                @if($isAnon)
                    <x-icon name="mask" class="w-3.5 h-3.5 text-coral-600"/>
                @endif
                <span class="truncate">{{ $display }}</span>
                @if(! $isAnon)
                    <x-verified-badge :tier="$tier"/>
                @endif
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
        <p class="text-ink-950 text-[15px] leading-relaxed whitespace-pre-line">{!! \App\Support\TextRenderer::renderHashtags(\Illuminate\Support\Str::limit($post->body, 280)) !!}</p>
        @if($post->image_url)
            <img src="{{ $post->image_url }}" alt="" loading="lazy" class="mt-3 w-full rounded-2xl object-cover max-h-[420px]">
        @endif
    </a>

    {{-- actions --}}
    <footer class="mt-4 flex items-center gap-1 text-ink-500 text-sm" data-vote-block
            data-post-id="{{ $post->id }}"
            data-vote-url="{{ route('posts.vote', $post) }}"
            data-my-vote="{{ $myVote }}">
        {{-- Like --}}
        <button type="button" data-vote="1"
                class="vote-btn {{ $myVote === 1 ? 'is-liked' : '' }}"
                aria-label="إعجاب">
            <x-icon name="thumbs-up" class="w-4 h-4" :filled="$myVote === 1"/>
            <span data-count="up" class="font-bold">{{ $post->upvotes }}</span>
        </button>

        {{-- Dislike --}}
        <button type="button" data-vote="-1"
                class="vote-btn {{ $myVote === -1 ? 'is-disliked' : '' }}"
                aria-label="مش عاجبني">
            <x-icon name="thumbs-down" class="w-4 h-4" :filled="$myVote === -1"/>
            <span data-count="down" class="font-bold">{{ $post->downvotes }}</span>
        </button>

        {{-- Comments --}}
        <a href="{{ route('posts.show', $post) }}#comments"
           class="vote-btn">
            <x-icon name="comment" class="w-4 h-4"/>
            <span class="font-bold">{{ $post->comments_count }}</span>
        </a>

        {{-- Share --}}
        <button type="button"
                data-share data-share-url="{{ route('posts.show', $post) }}"
                data-share-title="بوست على بنهاوي"
                data-share-text="{{ \Illuminate\Support\Str::limit($post->title ?? $post->body, 120) }}"
                class="vote-btn ms-auto" aria-label="شير">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
        </button>

        {{-- Report --}}
        <button type="button"
                data-report="{{ route('posts.report', $post) }}"
                class="vote-btn text-ink-400" aria-label="بلّغ عن البوست">
            <x-icon name="flag" class="w-4 h-4"/>
        </button>
    </footer>
</article>
