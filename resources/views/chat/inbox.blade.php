@extends('layouts.app', ['title' => 'الرسايل · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-xl font-extrabold text-ink-950 mb-4">💬 الرسايل</h1>

    @if($threads->isEmpty())
        <x-empty-state size="lg" icon="comment"
                       title="مفيش رسايل لسه"
                       hint="لما حد يبعتلك أو تبعت لحد، الرسايل هتظهر هنا."/>
    @else
        <div class="card-light p-2 space-y-1">
            @foreach($threads as $t)
                @php $other = $t->users->firstWhere('id', '!=', auth()->id()); @endphp
                @if(! $other) @continue @endif
                <a href="{{ route('chat.show', $t) }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-cream-100 transition">
                    <x-avatar :user="$other" size="md"/>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 text-sm font-bold text-ink-950">
                            {{ $other->username }}
                            <x-verified-badge :tier="$other->verification_tier ?? 'none'"/>
                            @if($other->isOnline())
                                <span class="text-[10px] text-mint-700 font-bold inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-mint-500"></span>
                                    أونلاين
                                </span>
                            @endif
                        </div>
                        @if($t->last_message_preview)
                            <div class="text-xs text-ink-500 truncate">{{ $t->last_message_preview }}</div>
                        @endif
                    </div>
                    @if($t->last_message_at)
                        <div class="text-[10px] text-ink-400 shrink-0">{{ $t->last_message_at->diffForHumans() }}</div>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
