@foreach($users as $u)
    @php $isFollowing = in_array($u->id, $followingIds, true); @endphp
    <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-cream-100 transition">
        <a href="{{ route('profile.show', $u->username) }}" class="shrink-0">
            <x-avatar :user="$u" size="md"/>
        </a>
        <a href="{{ route('profile.show', $u->username) }}" class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5">
                <span class="text-sm font-bold text-ink-950 truncate">{{ $u->username }}</span>
                <x-verified-badge :tier="$u->verification_tier ?? 'none'"/>
                @if($u->is_admin)
                    <span class="inline-flex items-center text-[9px] font-extrabold px-1.5 py-0.5 rounded-full bg-coral-500 text-white">ADMIN</span>
                @endif
            </div>
            <div class="text-[11px] text-ink-500">
                {{ $u->posts_count ?? 0 }} بوست
                · {{ $u->followers_count ?? 0 }} متابع
                · {{ $u->reputation ?? 0 }} نقطة
            </div>
        </a>
        @auth
            @if(auth()->id() !== $u->id)
                <form method="POST" action="{{ route('users.follow', $u) }}" class="shrink-0">
                    @csrf
                    <button class="text-[11px] font-bold px-3 py-1.5 rounded-full {{ $isFollowing ? 'bg-cream-100 text-ink-500 border border-ink-950/8' : 'bg-coral-500 text-white' }} hover:scale-105 transition">
                        {{ $isFollowing ? '✓ متابع' : '+ تابع' }}
                    </button>
                </form>
            @endif
        @endauth
    </div>
@endforeach

<div data-feed-end
     data-next-url="{{ $users->hasMorePages() ? $users->nextPageUrl() : '' }}"
     data-has-more="{{ $users->hasMorePages() ? '1' : '0' }}"></div>
