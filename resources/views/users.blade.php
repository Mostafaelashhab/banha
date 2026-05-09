@extends('layouts.app', ['title' => 'يوزرز · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-xl font-extrabold text-ink-950 mb-3">👥 يوزرز بنهاوي</h1>

    <form method="GET" action="{{ route('users.index') }}" class="card-light p-3 mb-4 flex items-center gap-2">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4 text-ink-400 ms-2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" name="q" value="{{ $q }}" placeholder="دوّر على يوزر…"
               class="flex-1 bg-transparent outline-0 text-ink-950 placeholder-ink-400 text-sm">
        <button class="btn-primary !py-1.5 !px-3 text-xs">دوّر</button>
    </form>

    @if($users->isEmpty())
        <div class="card-light p-10 text-center text-ink-500 text-sm">مفيش يوزرز.</div>
    @else
        <div id="users-list" class="card-light p-2 space-y-1" data-infinite-scroll>
            @include('partials.users-page', ['users' => $users, 'followingIds' => $followingIds])
        </div>
    @endif
</div>
@endsection
