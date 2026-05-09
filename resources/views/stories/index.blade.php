@extends('layouts.app', ['title' => 'الستوريز · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-extrabold text-ink-950 inline-flex items-center gap-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-coral-600">
                <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
            </svg>
            ستوريز
        </h1>
        @auth
            <a href="{{ route('stories.create') }}" class="btn-primary !py-2 !px-4 text-sm">
                <x-icon name="plus" class="w-4 h-4"/> ستوري جديدة
            </a>
        @endauth
    </div>

    @if($stories->isEmpty())
        <div class="card-light p-10 text-center">
            <p class="text-ink-500 text-sm">مفيش ستوريز دلوقتي. كن أول واحد يحط واحدة!</p>
            <p class="text-[10px] text-ink-400 mt-2">الستوريز بتنمسح تلقائي بعد ٢٤ ساعة.</p>
        </div>
    @else
        @php $myCount = $stories->where('user_id', auth()->id())->count(); @endphp
        @auth
            @if($myCount > 0)
                <div class="text-xs text-ink-500 mb-2">
                    عندك <span class="font-bold text-ink-950">{{ $myCount }}</span> ستوري نشطة
                </div>
            @endif
        @endauth

        <div class="grid grid-cols-3 gap-3">
            @foreach($stories as $story)
                @php $isMine = auth()->check() && auth()->id() === $story->user_id; @endphp
                <a href="{{ route('stories.show', $story) }}" class="block group">
                    <div class="aspect-square rounded-2xl overflow-hidden relative ring-4 {{ $isMine ? 'ring-mint-500' : 'ring-coral-500' }} ring-offset-2 ring-offset-cream-100">
                        <img src="{{ $story->image_url }}" alt="" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition">
                        <div class="absolute inset-x-0 bottom-0 p-2 bg-gradient-to-t from-black/80 to-transparent">
                            <div class="text-white text-[11px] font-bold truncate">{{ $story->user->username }}</div>
                            <div class="text-white/70 text-[9px]">{{ $story->created_at->diffForHumans(['short' => true]) }}</div>
                        </div>
                        @if($isMine)
                            <span class="absolute top-1 end-1 inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-mint-500 text-white text-[9px] font-extrabold">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                {{ $story->views_count }}
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
