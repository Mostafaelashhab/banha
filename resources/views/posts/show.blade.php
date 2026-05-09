@extends('layouts.app', ['title' => 'بوست · بنهاوي'])

@php
    use App\Support\AnonSeed;
    $isAnon  = $post->is_anonymous;
    $display = $isAnon ? ($post->anon_seed ?? 'مجهول') : $post->user->username;
    $score   = (int) $post->upvotes - (int) $post->downvotes;
    $cat     = \App\Models\Post::CATEGORIES[$post->category] ?? $post->category;
    $tier    = $isAnon ? 'none' : ($post->user->verification_tier ?? 'none');
    $tierCard = match ($tier) {
        'gold'   => 'tier-gold',
        'silver' => 'tier-silver',
        default  => '',
    };
@endphp

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('feed') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-base font-bold text-ink-500">بوست</h1>
    </div>

    {{-- post --}}
    @php
        $cardClass = match (true) {
            $post->is_announcement => 'post-announcement',
            $post->is_sponsored    => 'post-sponsored',
            default                => $tierCard,
        };
    @endphp
    <article class="card-light {{ $cardClass }} p-5 mb-4 relative">
        @if($post->is_announcement)
            <div class="-mx-5 -mt-5 mb-4 px-5 py-2 bg-mint-500 text-white text-xs font-extrabold rounded-t-2xl inline-flex items-center gap-2">
                <x-icon name="bell" class="w-3.5 h-3.5"/> 📢 من فريق بنهاوي · إعلان
            </div>
        @elseif($post->is_sponsored)
            <div class="-mx-5 -mt-5 mb-4 px-5 py-2 bg-honey-500 text-ink-950 text-xs font-extrabold rounded-t-2xl inline-flex items-center gap-2">
                <x-icon name="check" class="w-3.5 h-3.5"/> ⭐ مُروَّج
            </div>
        @endif
        <header class="flex items-center gap-2.5 mb-4">
            <x-avatar :user="$isAnon ? null : $post->user" :name="$display" :anon="$isAnon" size="md"/>
            <div class="flex-1">
                <div class="flex items-center gap-1.5 font-bold text-ink-950">
                    @if($isAnon)
                        <x-icon name="mask" class="w-4 h-4 text-coral-600"/>
                    @endif
                    {{ $display }}
                    @if(! $isAnon)
                        <x-verified-badge :tier="$tier"/>
                    @endif
                    @if($post->zone)<span class="text-ink-400 font-normal text-xs">· {{ $post->zone->name }}</span>@endif
                </div>
                <div class="text-xs text-ink-400">{{ $post->created_at->diffForHumans() }} · {{ $cat }}</div>
            </div>
        </header>

        @if($post->title)
            <h2 class="text-2xl font-black text-ink-950 mb-2 leading-tight">{{ $post->title }}</h2>
        @endif
        <p class="text-ink-950 text-[16px] leading-loose whitespace-pre-line">{!! \App\Support\TextRenderer::renderHashtags($post->body, (bool) ($post->user->is_admin ?? false)) !!}</p>

        @if($post->image_url)
            <img src="{{ $post->image_url }}" alt="" class="mt-3 w-full rounded-2xl object-contain max-h-[600px] bg-cream-100">
        @endif

        @if($post->poll)
            <div class="mt-4">
                @include('partials.poll', ['poll' => $post->poll])
            </div>
        @endif

        <footer class="mt-5 flex items-center gap-1 text-ink-500 pt-4 border-t border-ink-950/5" data-vote-block
                data-post-id="{{ $post->id }}"
                data-vote-url="{{ route('posts.vote', $post) }}"
                data-my-vote="{{ $userVote ?? 0 }}">
            <button type="button" data-vote="1"
                    class="vote-btn {{ ($userVote ?? 0) === 1 ? 'is-liked' : '' }}"
                    aria-label="إعجاب">
                <x-icon name="thumbs-up" class="w-4 h-4" :filled="($userVote ?? 0) === 1"/>
                <span data-count="up" class="font-bold">{{ $post->upvotes }}</span>
            </button>

            <button type="button" data-vote="-1"
                    class="vote-btn {{ ($userVote ?? 0) === -1 ? 'is-disliked' : '' }}"
                    aria-label="مش عاجبني">
                <x-icon name="thumbs-down" class="w-4 h-4" :filled="($userVote ?? 0) === -1"/>
                <span data-count="down" class="font-bold">{{ $post->downvotes }}</span>
            </button>

            <span class="vote-btn pointer-events-none">
                <x-icon name="comment" class="w-4 h-4"/>
                <span class="font-bold">{{ $post->comments_count }}</span>
            </span>

            @auth
                @php $isSaved = \App\Models\Bookmark::exists_(auth()->id(), 'post', $post->id); @endphp
                <button type="button" class="ms-auto p-2 rounded-full hover:bg-cream-200 transition {{ $isSaved ? 'text-coral-500' : 'text-ink-400' }}"
                        data-bookmark data-type="post" data-id="{{ $post->id }}" data-saved="{{ $isSaved ? '1' : '0' }}"
                        aria-label="حفظ">
                    <x-icon name="heart" :filled="$isSaved" class="w-4 h-4"/>
                </button>
                @if($post->user_id === auth()->id())
                    <form method="POST" action="{{ route('posts.destroy', $post) }}"
                          data-confirm="حذف البوست؟"
                          data-confirm-body="هيتمسح خالص ومش هيرجع تاني."
                          data-confirm-action="احذف"
                          data-confirm-tone="danger">
                        @csrf @method('DELETE')
                        <button class="p-2 rounded-full hover:bg-blush-100 hover:text-blush-500 transition text-ink-400" aria-label="حذف">
                            <x-icon name="trash" class="w-4 h-4"/>
                        </button>
                    </form>
                @else
                    <button type="button"
                            data-report="{{ route('posts.report', $post) }}"
                            class="p-2 rounded-full hover:bg-cream-200 hover:text-blush-500 transition text-ink-400"
                            aria-label="بلّغ">
                        <x-icon name="flag" class="w-4 h-4"/>
                    </button>
                @endif
            @endauth
        </footer>
    </article>

    {{-- comment form --}}
    <div id="comments" class="mb-4">
        <h3 class="text-lg font-extrabold text-ink-950 mb-3">{{ $post->comments_count }} كومنت</h3>
        <form method="POST" action="{{ route('posts.comment', $post) }}" class="card-light p-4 space-y-3">
            @csrf
            <textarea name="body" required rows="3" maxlength="1000" placeholder="اكتب رأيك…"
                      class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none"></textarea>
            @error('body') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-xs font-bold text-ink-500 cursor-pointer">
                    <input type="checkbox" name="is_anonymous" value="1" class="accent-coral-500"> مجهول
                </label>
                <button class="btn-primary !py-2 !px-5 text-sm ms-auto">
                    أرسل
                    <x-icon name="arrow-left" class="w-4 h-4"/>
                </button>
            </div>
        </form>
    </div>

    {{-- comments list --}}
    <div class="space-y-2">
        @forelse($comments as $c)
            @include('partials.comment', ['c' => $c, 'likedCommentIds' => $likedCommentIds ?? [], 'depth' => 0])
        @empty
            <div class="card-light p-6 text-center text-ink-500 text-sm">
                لسه مفيش كومنتات. كن أول واحد يرد.
            </div>
        @endforelse
    </div>
</div>
@endsection
