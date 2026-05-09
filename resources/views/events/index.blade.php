@extends('layouts.app', ['title' => 'الأحداث · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-3">
        <h1 class="text-xl font-extrabold text-ink-950">📅 أحداث بنها</h1>
        @auth
            <a href="{{ route('events.create') }}" class="btn-primary !py-2 !px-4 text-sm">
                <x-icon name="plus" class="w-4 h-4"/> حدث جديد
            </a>
        @endauth
    </div>

    <div class="flex gap-2 mb-3 overflow-x-auto scrollbar-hide -mx-4 px-4">
        <a href="{{ route('events.index') }}" class="chip {{ ! $activeKind ? 'chip-active' : '' }} shrink-0">الكل</a>
        @foreach($kinds as $k => $meta)
            <a href="{{ route('events.index', ['kind' => $k]) }}"
               class="chip pill-{{ $meta['tone'] }} {{ $activeKind === $k ? 'chip-active' : '' }} shrink-0">
                <x-icon :name="$meta['icon']" class="w-3.5 h-3.5"/> {{ $meta['label'] }}
            </a>
        @endforeach
    </div>

    @if($events->isEmpty())
        <div class="card-light p-10 text-center">
            <p class="text-ink-500 text-sm">مفيش أحداث جاية لسه. كن أول واحد يضيف!</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($events as $e)
                @php $km = $e->kindMeta(); @endphp
                <a href="{{ route('events.show', $e) }}" class="card-light p-0 overflow-hidden block hover:bg-cream-100 transition">
                    @if($e->cover_url)
                        <img src="{{ $e->cover_url }}" alt="" loading="lazy" class="w-full h-32 object-cover">
                    @endif
                    <div class="p-3">
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="text-[10px] font-bold pill-{{ $km['tone'] }} px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                                <x-icon :name="$km['icon']" class="w-3 h-3"/> {{ $km['label'] }}
                            </span>
                            <span class="text-[10px] text-ink-400">{{ $e->starts_at->translatedFormat('d M · H:i') }}</span>
                        </div>
                        <h3 class="text-sm font-extrabold text-ink-950 line-clamp-1">{{ $e->title }}</h3>
                        @if($e->location)
                            <div class="text-[11px] text-ink-500 mt-0.5"><x-icon name="map-pin" class="w-3 h-3 inline"/> {{ $e->location }}</div>
                        @endif
                        @if($e->attendees_count > 0)
                            <div class="text-[11px] text-coral-600 font-bold mt-1">👥 {{ $e->attendees_count }} هيحضروا</div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-4">{{ $events->links() }}</div>
    @endif
</div>
@endsection
