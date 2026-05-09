@extends('layouts.app', ['title' => 'ستوري · ' . $story->user->username])

@section('content')
<div class="max-w-md mx-auto">
    <div class="card-light p-0 overflow-hidden relative bg-black">
        <div class="absolute top-0 inset-x-0 z-10 px-4 py-3 bg-gradient-to-b from-black/80 to-transparent flex items-center gap-2">
            <x-avatar :user="$story->user" size="sm"/>
            <div class="flex-1 min-w-0">
                <div class="text-white text-sm font-bold truncate">{{ $story->user->username }}</div>
                <div class="text-white/70 text-[10px]">{{ $story->created_at->diffForHumans() }}</div>
            </div>
            <a href="{{ route('stories.index') }}" class="text-white/80 hover:text-white" aria-label="رجوع">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-5 h-5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </a>
        </div>

        <img src="{{ $story->image_url }}" alt="" class="w-full max-h-[80vh] object-contain">

        @if($story->caption)
            <div class="absolute bottom-0 inset-x-0 p-4 bg-gradient-to-t from-black/80 to-transparent">
                <p class="text-white text-sm whitespace-pre-line">{{ $story->caption }}</p>
            </div>
        @endif
    </div>

    <div class="text-center text-xs text-ink-400 mt-3">
        ينتهي {{ $story->expires_at->diffForHumans() }}
    </div>

    @auth
        @if(auth()->id() === $story->user_id || auth()->user()->is_admin)
            {{-- Owner: tappable view count → opens viewers list --}}
            <a href="{{ route('stories.viewers', $story) }}" class="card-light mt-3 p-3 flex items-center justify-center gap-2 hover:bg-cream-100 transition">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-coral-600">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                <span class="font-bold text-ink-950 text-sm">{{ $story->views_count }} مشاهدة</span>
                <span class="text-xs text-ink-400">— اضغط شوف اللي شافوا</span>
            </a>

            <form method="POST" action="{{ route('stories.destroy', $story) }}" class="mt-3"
                  data-confirm="حذف الستوري؟" data-confirm-tone="danger">
                @csrf @method('DELETE')
                <button class="card-light p-3 w-full text-blush-500 font-bold text-sm hover:bg-blush-100/50 transition flex items-center justify-center gap-2">
                    <x-icon name="trash" class="w-4 h-4"/> احذف
                </button>
            </form>
        @else
            {{-- Public: just shows count --}}
            <div class="text-center text-xs text-ink-400 mt-1 inline-flex items-center justify-center gap-1 w-full">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                {{ $story->views_count }} مشاهدة
            </div>
        @endif
    @endauth
</div>
@endsection
