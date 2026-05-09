@extends('layouts.app', ['title' => 'مشاهدات الستوري · بنهاوي'])

@section('content')
<div class="max-w-md mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('stories.show', $story) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950 inline-flex items-center gap-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-coral-600">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
            {{ $story->views_count }} مشاهدة
        </h1>
    </div>

    {{-- Story thumbnail --}}
    <a href="{{ route('stories.show', $story) }}" class="block card-light p-2 mb-3 flex items-center gap-3">
        <img src="{{ $story->image_url }}" alt="" class="w-14 h-14 rounded-xl object-cover shrink-0">
        <div class="flex-1 min-w-0">
            @if($story->caption)
                <p class="text-sm text-ink-950 truncate">{{ $story->caption }}</p>
            @endif
            <div class="text-[11px] text-ink-400">
                نُشرت {{ $story->created_at->diffForHumans() }} · ينتهي {{ $story->expires_at->diffForHumans() }}
            </div>
        </div>
    </a>

    @if($viewers->isEmpty())
        <div class="card-light p-10 text-center">
            <p class="text-ink-500 text-sm">مفيش حد شاف الستوري لسه. شارك اللينك مع الناس.</p>
        </div>
    @else
        <div class="card-light p-2 space-y-1">
            @foreach($viewers as $v)
                <a href="{{ route('profile.show', $v->username) }}" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-cream-100 transition">
                    <x-avatar :user="$v" size="md"/>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-bold text-ink-950">{{ $v->username }}</span>
                            <x-verified-badge :tier="$v->verification_tier ?? 'none'"/>
                        </div>
                        <div class="text-[11px] text-ink-400">شاف {{ \Illuminate\Support\Carbon::parse($v->viewed_at)->diffForHumans() }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
