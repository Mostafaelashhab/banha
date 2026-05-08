@extends('layouts.app', ['title' => 'محفوظاتي · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-xl font-extrabold text-ink-950 mb-4">محفوظاتي</h1>

    @if($posts->isEmpty() && $businesses->isEmpty() && $listings->isEmpty())
        <div class="card-light p-10 text-center">
            <div class="icon-tile mx-auto mb-4 text-coral-600 w-16 h-16">
                <x-icon name="heart" class="w-7 h-7"/>
            </div>
            <h3 class="text-xl font-extrabold text-ink-950 mb-1">مفيش حاجة محفوظة</h3>
            <p class="text-ink-500 text-sm">اضغط ❤ على أي بوست/نشاط/إعلان عشان يتحفظ هنا.</p>
        </div>
    @else
        @if($listings->isNotEmpty())
            <h3 class="text-sm font-extrabold text-ink-950 mb-2">إعلانات</h3>
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

        @if($businesses->isNotEmpty())
            <h3 class="text-sm font-extrabold text-ink-950 mb-2">نشاطات</h3>
            <div class="space-y-2 mb-4">
                @foreach($businesses as $b)
                    @include('partials.business-feed-card', ['business' => $b, 'isAd' => false])
                @endforeach
            </div>
        @endif

        @if($posts->isNotEmpty())
            <h3 class="text-sm font-extrabold text-ink-950 mb-2">بوستات</h3>
            <div class="space-y-2">
                @foreach($posts as $p)
                    @include('partials.post-card', ['post' => $p, 'userVotes' => []])
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection
