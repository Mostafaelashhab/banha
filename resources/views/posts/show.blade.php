@extends('layouts.app', ['title' => 'بوست · بنهاوي'])

@php
    use App\Support\AnonSeed;
    $isAnon  = $post->is_anonymous;
    $display = $isAnon ? ($post->anon_seed ?? 'مجهول') : $post->user->username;
    $score   = (int) $post->upvotes - (int) $post->downvotes;
    $cat     = \App\Models\Post::CATEGORIES[$post->category] ?? $post->category;
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
    <article class="card-light p-5 mb-4">
        <header class="flex items-center gap-2.5 mb-4">
            <x-avatar :user="$isAnon ? null : $post->user" :name="$display" :anon="$isAnon" size="md"/>
            <div class="flex-1">
                <div class="flex items-center gap-1.5 font-bold text-ink-950">
                    @if($isAnon)
                        <x-icon name="mask" class="w-4 h-4 text-coral-600"/>
                    @endif
                    {{ $display }}
                    @if($post->zone)<span class="text-ink-400 font-normal text-xs">· {{ $post->zone->name }}</span>@endif
                </div>
                <div class="text-xs text-ink-400">{{ $post->created_at->diffForHumans() }} · {{ $cat }}</div>
            </div>
        </header>

        @if($post->title)
            <h2 class="text-2xl font-black text-ink-950 mb-2 leading-tight">{{ $post->title }}</h2>
        @endif
        <p class="text-ink-950 text-[16px] leading-loose whitespace-pre-line">{{ $post->body }}</p>

        <footer class="mt-5 flex items-center gap-1 text-ink-500 pt-4 border-t border-ink-950/5">
            <form method="POST" action="{{ route('posts.vote', $post) }}" class="inline-flex items-center bg-cream-100 rounded-full">
                @csrf
                <button name="value" value="{{ $userVote === 1 ? 0 : 1 }}"
                        class="p-2 rounded-full hover:bg-coral-100 transition {{ $userVote === 1 ? 'text-coral-600' : '' }}">
                    <x-icon name="arrow-right" class="w-4 h-4 -rotate-90"/>
                </button>
                <span class="px-2 font-bold text-ink-950 min-w-[2rem] text-center">{{ $score }}</span>
                <button name="value" value="{{ $userVote === -1 ? 0 : -1 }}"
                        class="p-2 rounded-full hover:bg-blush-100 transition {{ $userVote === -1 ? 'text-blush-500' : '' }}">
                    <x-icon name="arrow-right" class="w-4 h-4 rotate-90"/>
                </button>
            </form>

            <span class="px-3 py-2 inline-flex items-center gap-1.5 text-sm">
                <x-icon name="bell" class="w-4 h-4"/>
                {{ $post->comments_count }} كومنت
            </span>

            @auth
                @if($post->user_id === auth()->id())
                    <form method="POST" action="{{ route('posts.destroy', $post) }}" class="ms-auto"
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
                            class="ms-auto p-2 rounded-full hover:bg-cream-200 hover:text-blush-500 transition text-ink-400"
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
            @php
                $cIsAnon = $c->is_anonymous;
                $cDisplay = $cIsAnon ? ($c->anon_seed ?? 'مجهول') : $c->user->username;
            @endphp
            <div class="card-light p-3.5">
                <div class="flex items-center gap-2 mb-1.5">
                    <x-avatar :user="$cIsAnon ? null : $c->user" :name="$cDisplay" :anon="$cIsAnon" size="sm"/>
                    <span class="text-sm font-bold text-ink-950 inline-flex items-center gap-1">
                        @if($cIsAnon)<x-icon name="mask" class="w-3 h-3 text-coral-600"/>@endif
                        {{ $cDisplay }}
                    </span>
                    <span class="text-[11px] text-ink-400">{{ $c->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-ink-950 text-sm leading-relaxed whitespace-pre-line ps-9">{{ $c->body }}</p>
            </div>
        @empty
            <div class="card-light p-6 text-center text-ink-500 text-sm">
                لسه مفيش كومنتات. كن أول واحد يرد.
            </div>
        @endforelse
    </div>
</div>
@endsection
