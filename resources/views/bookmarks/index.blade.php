@extends('layouts.app', ['title' => 'محفوظاتي · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-xl font-extrabold text-ink-950 mb-4">محفوظاتي</h1>

    @if($businesses->isEmpty() && $listings->isEmpty())
        <x-empty-state size="lg" icon="heart"
                       title="مفيش حاجة محفوظة"
                       hint="اضغط على أي نشاط أو إعلان عشان يتحفظ هنا."/>
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

    
    @endif
</div>
@endsection
