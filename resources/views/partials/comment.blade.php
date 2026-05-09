@php
    /** @var \App\Models\Comment $c */
    $cIsAnon  = $c->is_anonymous;
    $cDisplay = $cIsAnon ? ($c->anon_seed ?? 'مجهول') : $c->user->username;
    $cTier    = $cIsAnon ? 'none' : ($c->user->verification_tier ?? 'none');
    $likedIds = $likedCommentIds ?? [];
    $isLiked  = in_array($c->id, $likedIds, true);
    $depth    = $depth ?? 0;
    $isOwn    = auth()->check() && auth()->id() === $c->user_id;
@endphp

<div id="comment-{{ $c->id }}" class="card-light p-3.5 {{ $depth > 0 ? 'me-6 mt-2' : '' }}" data-comment-id="{{ $c->id }}">
    <div class="flex items-center gap-2 mb-1.5">
        @if($cIsAnon)
            <x-avatar :user="null" :name="$cDisplay" :anon="true" size="sm"/>
            <span class="text-sm font-bold text-ink-950 inline-flex items-center gap-1">
                <x-icon name="mask" class="w-3 h-3 text-coral-600"/>
                {{ $cDisplay }}
            </span>
        @else
            <a href="{{ route('profile.show', $c->user->username) }}" class="shrink-0">
                <x-avatar :user="$c->user" :name="$cDisplay" :anon="false" size="sm"/>
            </a>
            <a href="{{ route('profile.show', $c->user->username) }}" class="text-sm font-bold text-ink-950 inline-flex items-center gap-1 hover:text-coral-600 transition">
                {{ $cDisplay }}
                <x-verified-badge :tier="$cTier"/>
            </a>
        @endif
        <span class="text-[11px] text-ink-400">{{ $c->created_at->diffForHumans() }}</span>

        {{-- Overflow menu --}}
        @auth
            <div class="ms-auto relative" data-menu>
                <button type="button" class="w-7 h-7 rounded-full grid place-items-center text-ink-400 hover:bg-cream-100 hover:text-ink-950 transition" data-menu-toggle aria-label="خيارات">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
                </button>
                <div class="hidden absolute end-0 top-full mt-1 bg-white rounded-2xl shadow-lg border border-ink-950/8 py-1 min-w-[140px] z-20" data-menu-panel>
                    @if($isOwn)
                        <form method="POST" action="{{ route('comments.destroy', $c) }}"
                              data-confirm="حذف الكومنت؟"
                              data-confirm-action="احذف"
                              data-confirm-tone="danger">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full text-start px-4 py-2 text-sm text-blush-500 hover:bg-blush-100/50 inline-flex items-center gap-2">
                                <x-icon name="trash" class="w-3.5 h-3.5"/> احذف
                            </button>
                        </form>
                    @else
                        <button type="button" class="w-full text-start px-4 py-2 text-sm text-ink-950 hover:bg-cream-100 inline-flex items-center gap-2"
                                data-comment-report
                                data-action="{{ route('comments.report', $c) }}"
                                data-csrf="{{ csrf_token() }}">
                            <x-icon name="flag" class="w-3.5 h-3.5"/> ابلاغ
                        </button>
                    @endif
                </div>
            </div>
        @endauth
    </div>

    <p class="text-ink-950 text-sm leading-relaxed whitespace-pre-line ps-9">{{ $c->body }}</p>

    {{-- Actions row --}}
    <div class="flex items-center gap-1 mt-2 ps-9">
        @auth
            <form method="POST" action="{{ route('comments.like', $c) }}" class="inline" data-comment-like>
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold transition {{ $isLiked ? 'bg-coral-500 text-white' : 'text-ink-500 hover:bg-cream-100' }}"
                        data-liked="{{ $isLiked ? '1' : '0' }}">
                    <x-icon name="thumbs-up" :filled="$isLiked" class="w-3.5 h-3.5"/>
                    <span data-like-count>{{ $c->upvotes }}</span>
                </button>
            </form>

            @if($depth === 0)
                <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold text-ink-500 hover:bg-cream-100 transition"
                        data-reply-toggle="{{ $c->id }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-3.5 h-3.5">
                        <polyline points="9 17 4 12 9 7"/>
                        <path d="M20 18v-2a4 4 0 0 0-4-4H4"/>
                    </svg>
                    رد
                </button>
            @endif
        @else
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold text-ink-500 hover:bg-cream-100 transition">
                <x-icon name="thumbs-up" class="w-3.5 h-3.5"/>
                <span>{{ $c->upvotes }}</span>
            </a>
        @endauth
    </div>

    {{-- Reply form (hidden, top-level only) --}}
    @auth
        @if($depth === 0)
            <form method="POST" action="{{ route('comments.reply', $c) }}"
                  class="hidden mt-3 ps-9 space-y-2"
                  data-reply-form="{{ $c->id }}">
                @csrf
                <textarea name="body" required rows="2" maxlength="1000" placeholder="اكتب ردك على {{ $cDisplay }}…"
                          class="w-full bg-cream-100 rounded-xl px-3 py-2 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none text-sm"></textarea>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-1.5 text-[11px] font-bold text-ink-500 cursor-pointer">
                        <input type="checkbox" name="is_anonymous" value="1" class="accent-coral-500"> مجهول
                    </label>
                    <button type="submit" class="btn-primary !py-1.5 !px-4 text-xs ms-auto">رد</button>
                </div>
            </form>
        @endif
    @endauth

    {{-- Replies --}}
    @if($c->relationLoaded('replies') && $c->replies->isNotEmpty())
        <div class="mt-2 space-y-2">
            @foreach($c->replies as $reply)
                @include('partials.comment', ['c' => $reply, 'likedCommentIds' => $likedCommentIds ?? [], 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
