@extends('layouts.app', ['title' => 'بحث · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <form method="GET" action="{{ route('search') }}" class="card-light p-3 mb-4 flex items-center gap-2">
        <x-icon name="search" class="w-5 h-5 text-ink-400 ms-2"/>
        <input type="text" name="q" value="{{ $q }}" autofocus placeholder="ابحث في كل بنهاوي…"
               class="flex-1 bg-transparent outline-0 text-ink-950 placeholder-ink-400 text-sm">
        <x-button type="submit" size="sm">ابحث</x-button>
    </form>

    @if($q === '')
        <x-empty-state icon="search"
                       title="ابحث في كل بنهاوي"
                       hint="اكتب أي حاجة عاوز تلاقيها — بوست، نشاط، يوزر، أو إعلان"/>
    @else
        @if($posts->isEmpty() && $businesses->isEmpty() && $listings->isEmpty() && $users->isEmpty())
            <x-empty-state icon="search"
                           :title="'مفيش نتايج لـ &quot;' . e($q) . '&quot;'"
                           hint="جرّب كلمة تانية أو شيل الفلتر"/>
        @endif

        @if($users->isNotEmpty())
            <h3 class="text-sm font-extrabold text-ink-950 mb-2 mt-4">يوزرز ({{ $users->count() }})</h3>
            <x-card padding="sm" class="mb-4 space-y-2">
                @foreach($users as $u)
                    <a href="{{ route('profile.show', $u->username) }}" class="flex items-center gap-2 p-2 rounded-xl hover:bg-cream-100 transition">
                        <x-avatar :user="$u" size="sm"/>
                        <span class="text-sm font-bold text-ink-950">{{ '@'.$u->username }}</span>
                        <x-verified-badge :tier="$u->verification_tier ?? 'none'"/>
                    </a>
                @endforeach
            </x-card>
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
                    <x-card as="a" :href="route('marketplace.show', $l)" padding="sm" class="hover:bg-cream-100 transition">
                        <h4 class="text-sm font-bold text-ink-950 line-clamp-1">{{ $l->title }}</h4>
                        @if(in_array($l->kind, ['sale','buy'], true))
                            <div class="text-coral-600 font-extrabold text-sm mt-1">{{ $l->priceLabel() }}</div>
                        @endif
                    </x-card>
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
