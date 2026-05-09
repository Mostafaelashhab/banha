@extends('layouts.app', ['title' => 'الستوريز · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-extrabold text-ink-950">📸 ستوريز</h1>
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
        <div class="grid grid-cols-3 gap-3">
            @foreach($stories as $userId => $userStories)
                @php $latest = $userStories->first(); @endphp
                <a href="{{ route('stories.show', $latest) }}" class="block">
                    <div class="aspect-square rounded-2xl overflow-hidden relative ring-4 ring-coral-500 ring-offset-2 ring-offset-cream-100">
                        <img src="{{ $latest->image_url }}" alt="" loading="lazy" class="w-full h-full object-cover">
                        <div class="absolute inset-x-0 bottom-0 p-2 bg-gradient-to-t from-black/70 to-transparent">
                            <div class="text-white text-[11px] font-bold truncate">{{ $latest->user->username }}</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
