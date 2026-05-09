@extends('layouts.app', ['title' => 'متابعينك · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-extrabold text-ink-950">👥 اللي بتتابعهم</h1>
        <a href="{{ route('feed') }}" class="text-xs text-coral-600 font-bold">الفيد العام</a>
    </div>

    @if(($empty ?? false) || $posts->isEmpty())
        <div class="card-light p-10 text-center">
            <div class="icon-tile mx-auto mb-4 text-coral-600 w-16 h-16">
                <x-icon name="user" class="w-7 h-7"/>
            </div>
            <h3 class="text-xl font-extrabold text-ink-950 mb-1">مفيش حد بتتابعه</h3>
            <p class="text-ink-500 text-sm mb-4">روح أي بروفايل واضغط "تابع" عشان تشوف بوستاته هنا.</p>
            <a href="{{ route('discover') }}" class="btn-primary mx-auto">اكتشف يوزرز</a>
        </div>
    @else
        <div>
            @foreach($posts as $p)
                @include('partials.post-card', ['post' => $p, 'userVotes' => $userVotes])
            @endforeach
        </div>
        <div class="mt-4">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
