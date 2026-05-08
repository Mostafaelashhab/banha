@extends('layouts.app', ['title' => 'بحث · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <form method="GET" action="{{ route('search') }}" class="card-light p-3 mb-4 flex items-center gap-2">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-5 h-5 text-ink-400 ms-2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" name="q" value="{{ $q }}" autofocus placeholder="ابحث في كل بنهاوي…"
               class="flex-1 bg-transparent outline-0 text-ink-950 placeholder-ink-400 text-sm">
        <button class="btn-primary !py-2 !px-4 text-xs">ابحث</button>
    </form>

    @if($q === '')
        <div class="card-light p-10 text-center">
            <p class="text-ink-500 text-sm">اكتب أي حاجة عاوز تلاقيها — بوست، نشاط، يوزر، أو إعلان.</p>
        </div>
    @else
        @if($posts->isEmpty() && $businesses->isEmpty() && $listings->isEmpty() && $users->isEmpty())
            <div class="card-light p-10 text-center">
                <p class="text-ink-500 text-sm">مفيش نتايج لـ <b>"{{ $q }}"</b>.</p>
            </div>
        @endif

        @if($users->isNotEmpty())
            <h3 class="text-sm font-extrabold text-ink-950 mb-2 mt-4">يوزرز ({{ $users->count() }})</h3>
            <div class="card-light p-3 mb-4 space-y-2">
                @foreach($users as $u)
                    <a href="{{ route('profile.show', $u->username) }}" class="flex items-center gap-2 p-2 rounded-xl hover:bg-cream-100 transition">
                        <x-avatar :user="$u" size="sm"/>
                        <span class="text-sm font-bold text-ink-950">@@{{ $u->username }}</span>
                        <x-verified-badge :tier="$u->verification_tier ?? 'none'"/>
                    </a>
                @endforeach
            </div>
        @endif

        @if($businesses->isNotEmpty())
            <h3 class="text-sm font-extrabold text-ink-950 mb-2 mt-4">نشاطات ({{ $businesses->count() }})</h3>
            <div class="space-y-2 mb-4">
                @foreach($businesses as $b)
                    @include('partials.business-feed-card', ['business' => $b, 'isAd' => false])
                @endforeach
            </div>
        @endif

        @if($listings->isNotEmpty())
            <h3 class="text-sm font-extrabold text-ink-950 mb-2 mt-4">إعلانات ({{ $listings->count() }})</h3>
            <div class="grid grid-cols-2 gap-3 mb-4">
                @foreach($listings as $l)
                    <a href="{{ route('marketplace.show', $l) }}" class="card-light p-3 hover:bg-cream-100 transition">
                        <h4 class="text-sm font-bold text-ink-950 line-clamp-1">{{ $l->title }}</h4>
                        @if(in_array($l->kind, ['sale','buy'], true))
                            <div class="text-coral-600 font-extrabold text-sm mt-1">{{ $l->priceLabel() }}</div>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif

        @if($posts->isNotEmpty())
            <h3 class="text-sm font-extrabold text-ink-950 mb-2 mt-4">بوستات ({{ $posts->count() }})</h3>
            <div class="space-y-2">
                @foreach($posts as $p)
                    @include('partials.post-card', ['post' => $p, 'userVotes' => []])
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection
