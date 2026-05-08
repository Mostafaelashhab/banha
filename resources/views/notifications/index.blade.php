@extends('layouts.app', ['title' => 'الإشعارات · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-xl font-extrabold text-ink-950 mb-4">الإشعارات</h1>

    @if($notifications->isEmpty())
        <div class="card-light p-10 text-center">
            <div class="icon-tile mx-auto mb-4 text-coral-600 w-16 h-16">
                <x-icon name="bell" class="w-7 h-7"/>
            </div>
            <h3 class="text-xl font-extrabold text-ink-950 mb-1">مفيش إشعارات لسه</h3>
            <p class="text-ink-500 text-sm">لما حد يتفاعل مع بوستك أو يبعتلك حاجة هتلاقيها هنا.</p>
        </div>
    @else
        <div class="card-light p-2 space-y-1">
            @foreach($notifications as $n)
                <a href="{{ $n->url ?? '#' }}" class="flex items-start gap-3 p-3 rounded-xl hover:bg-cream-100 transition {{ ! $n->read_at && $loop->first ? 'bg-coral-50' : '' }}">
                    <span class="w-9 h-9 rounded-full pill-coral grid place-items-center shrink-0 mt-0.5">
                        <x-icon name="bell" class="w-4 h-4"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-ink-950">{{ $n->title }}</div>
                        @if($n->body)
                            <div class="text-xs text-ink-500 mt-0.5 line-clamp-2">{{ $n->body }}</div>
                        @endif
                        <div class="text-[10px] text-ink-400 mt-1">{{ $n->created_at?->diffForHumans() }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
