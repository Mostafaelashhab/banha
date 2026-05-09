@extends('layouts.app', ['title' => $event->title])

@php $km = $event->kindMeta(); @endphp

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('events.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-base font-bold text-ink-500">{{ $km['label'] }}</h1>
        <button type="button" class="ms-auto w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950 hover:bg-cream-100 transition"
                data-share data-share-url="{{ route('events.show', $event) }}"
                data-share-title="{{ $event->title }}"
                aria-label="شارك">
            <x-icon name="share" class="w-4 h-4"/>
        </button>
    </div>

    @if($event->cover_url)
        <img src="{{ $event->cover_url }}" alt="" class="w-full rounded-3xl mb-3 max-h-[400px] object-cover">
    @endif

    <div class="card-light p-5 mb-3">
        <span class="px-3 py-1 rounded-full pill-{{ $km['tone'] }} text-xs font-bold inline-flex items-center gap-1.5 mb-3">
            <x-icon :name="$km['icon']" class="w-3 h-3"/> {{ $km['label'] }}
        </span>

        <h2 class="text-2xl font-black text-ink-950 mb-2">{{ $event->title }}</h2>

        <div class="space-y-2 mb-4">
            <div class="flex items-center gap-2 text-sm text-ink-950">
                <x-icon name="bell" class="w-4 h-4 text-coral-500"/>
                <span class="font-bold">{{ $event->starts_at->translatedFormat('l d F Y') }}</span>
                <span class="text-ink-500">· {{ $event->starts_at->translatedFormat('H:i') }}</span>
                @if($event->ends_at)
                    <span class="text-ink-400">→ {{ $event->ends_at->translatedFormat('H:i') }}</span>
                @endif
            </div>
            @if($event->location)
                <div class="flex items-center gap-2 text-sm text-ink-950">
                    <x-icon name="map-pin" class="w-4 h-4 text-coral-500"/>
                    {{ $event->location }}
                </div>
            @endif
            @if($event->zone)
                <div class="text-xs text-ink-500"><x-icon name="map" class="w-3 h-3 inline"/> {{ $event->zone->name }}</div>
            @endif
        </div>

        @if($event->description)
            <p class="text-ink-950 text-sm leading-relaxed whitespace-pre-line mb-4">{{ $event->description }}</p>
        @endif

        @auth
            @if(! $event->isPast())
                <form method="POST" action="{{ route($isAttending ? 'events.unattend' : 'events.attend', $event) }}">
                    @csrf
                    <button class="w-full btn-primary justify-center !py-3 {{ $isAttending ? '!bg-mint-500' : '' }}">
                        @if($isAttending)
                            ✓ هتحضر · اضغط للإلغاء
                        @else
                            🎟 سجّل حضورك
                        @endif
                    </button>
                </form>
            @endif
        @endauth

        <div class="text-center text-xs text-ink-500 mt-3">👥 {{ $event->attendees_count }} مسجّلين</div>

        @if($event->contact_phone)
            <a href="tel:{{ $event->contact_phone }}" class="block text-center mt-3 text-sm font-bold text-coral-600">📞 {{ $event->contact_phone }}</a>
        @endif

        <a href="{{ route('profile.show', $event->user->username) }}" class="flex items-center gap-2 mt-4 pt-3 border-t border-ink-950/8">
            <x-avatar :user="$event->user" size="sm"/>
            <span class="text-xs text-ink-500">منظّم: <span class="font-bold text-ink-950">{{ '@'.$event->user->username }}</span></span>
        </a>
    </div>

    @auth
        @if(auth()->id() === $event->user_id || auth()->user()->is_admin)
            <form method="POST" action="{{ route('events.destroy', $event) }}"
                  data-confirm="إلغاء الحدث؟" data-confirm-tone="danger">
                @csrf @method('DELETE')
                <button class="card-light p-3 w-full text-blush-500 font-bold text-sm hover:bg-blush-100/50 transition flex items-center justify-center gap-2">
                    <x-icon name="trash" class="w-4 h-4"/> ألغي الحدث
                </button>
            </form>
        @endif
    @endauth
</div>
@endsection
