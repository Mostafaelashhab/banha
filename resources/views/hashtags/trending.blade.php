@extends('layouts.app', ['title' => 'الهاشتاجات الترند · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-xl font-extrabold text-ink-950 mb-4">🔥 ترند الهاشتاجات</h1>
    @if($tags->isEmpty())
        <div class="card-light p-10 text-center text-ink-500 text-sm">مفيش هاشتاجات شائعة لسه.</div>
    @else
        <div class="card-light p-3 space-y-1">
            @foreach($tags as $i => $t)
                <a href="{{ route('hashtag.show', $t->tag) }}" class="flex items-center justify-between gap-3 p-3 rounded-xl hover:bg-cream-100 transition">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full grid place-items-center text-xs font-extrabold {{ $i < 3 ? 'bg-coral-500 text-white' : 'bg-cream-100 text-ink-500' }}">
                            {{ $i + 1 }}
                        </span>
                        <span class="font-bold text-ink-950">#{{ $t->tag }}</span>
                    </div>
                    <span class="text-xs text-ink-400">{{ $t->uses_count }} بوست</span>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
