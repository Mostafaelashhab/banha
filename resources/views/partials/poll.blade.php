@php
    /** @var \App\Models\Poll $poll */
    $counts = $poll->counts();
    $total  = $poll->totalVotes();
    $userVote = $poll->userVote();
    $closed = $poll->isClosed();
    $hasVoted = $userVote !== null;
    $showResults = $hasVoted || $closed;
@endphp

<div class="card-light p-4 mb-3" data-poll="{{ $poll->id }}">
    <h3 class="font-extrabold text-ink-950 mb-3">{{ $poll->question }}</h3>
    <form method="POST" action="{{ route('polls.vote', $poll) }}" class="space-y-2" data-poll-form>
        @csrf
        @foreach($poll->options as $i => $opt)
            @php
                $c = (int) ($counts[$i] ?? 0);
                $pct = $total > 0 ? round(($c / $total) * 100) : 0;
                $mine = $userVote === $i;
            @endphp
            <button type="submit" name="option" value="{{ $i }}"
                    {{ $closed || ! auth()->check() ? 'disabled' : '' }}
                    class="w-full text-start relative overflow-hidden rounded-2xl border {{ $mine ? 'border-coral-500' : 'border-ink-950/8' }} bg-cream-100 px-3 py-2.5 text-sm font-bold text-ink-950 hover:bg-coral-50 transition disabled:cursor-default {{ $showResults ? 'cursor-default' : '' }}">
                @if($showResults)
                    <span class="absolute inset-0 bg-coral-500/10" style="width: {{ $pct }}%"></span>
                @endif
                <span class="relative flex items-center justify-between">
                    <span>{{ $opt }} @if($mine) ✓ @endif</span>
                    @if($showResults)
                        <span class="text-xs text-ink-500">{{ $pct }}% ({{ $c }})</span>
                    @endif
                </span>
            </button>
        @endforeach
    </form>
    <div class="text-[10px] text-ink-400 mt-2 flex items-center justify-between">
        <span>{{ $total }} صوت</span>
        @if($closed)
            <span>قفل</span>
        @elseif($poll->closes_at)
            <span>يقفل {{ $poll->closes_at->diffForHumans() }}</span>
        @endif
    </div>
</div>
