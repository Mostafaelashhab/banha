@extends('layouts.app', ['title' => 'متابعينك · بنهاوي'])

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-extrabold text-ink-950">👥 اللي بتتابعهم</h1>
        <a href="{{ route('feed') }}" class="text-xs text-coral-600 font-bold">الفيد العام</a>
    </div>

    @if(($empty ?? false) || $posts->isEmpty())
        <x-empty-state size="lg" icon="user"
                       title="مفيش حد بتتابعه"
                       hint='روح أي بروفايل واضغط "تابع" عشان تشوف بوستاته هنا.'>
            <x-slot:cta>
                <x-button :href="route('discover')">اكتشف يوزرز</x-button>
            </x-slot:cta>
        </x-empty-state>
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
