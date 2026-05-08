@extends('admin.layouts.admin', ['title' => 'البوستات · Admin'])

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-black">البوستات</h1>
    <span class="text-white/50 text-sm">{{ $posts->total() }}</span>
</div>

<div class="flex gap-2 mb-4 overflow-x-auto scrollbar-hide">
    @foreach(['active'=>'نشط','flagged'=>'فيه بلاغات','removed'=>'محذوف'] as $key => $label)
        <a href="{{ route('admin.posts', ['status'=>$key]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap
                  {{ $status === $key ? 'bg-coral-500 text-white' : 'bg-white/5 text-white/60 border border-white/10' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="space-y-2">
    @foreach($posts as $p)
        <div class="a-card p-4">
            <div class="flex items-start gap-3 mb-2">
                <span class="a-pill {{ $p->is_anonymous ? 'pill-coral' : 'bg-white/10 text-white/70' }}">
                    {{ $p->is_anonymous ? '🤫 مجهول' : ($p->user?->username ?? '—') }}
                </span>
                <span class="a-pill bg-white/10 text-white/60">{{ \App\Models\Post::CATEGORIES[$p->category] ?? $p->category }}</span>
                @if($p->zone)<span class="a-pill bg-white/10 text-white/60">{{ $p->zone->name }}</span>@endif
                @if($p->status === 'flagged')<span class="a-pill pill-blush">⚑ {{ $p->flag_count }} بلاغ</span>@endif
                @if($p->status === 'removed')<span class="a-pill bg-blush-500 text-white">محذوف</span>@endif
                <span class="ms-auto text-[11px] text-white/40">{{ $p->created_at->diffForHumans() }}</span>
            </div>
            @if($p->title)<h3 class="font-bold mb-1">{{ $p->title }}</h3>@endif
            <p class="text-white/85 text-sm leading-relaxed">{{ \Illuminate\Support\Str::limit($p->body, 200) }}</p>
            <div class="flex items-center gap-3 mt-3 text-xs text-white/50">
                <span>↑ {{ $p->upvotes }}</span>
                <span>↓ {{ $p->downvotes }}</span>
                <span>💬 {{ $p->comments_count }}</span>
                <a href="{{ route('posts.show', $p) }}" target="_blank" class="ms-auto text-coral-400 font-bold hover:underline">عرض ←</a>
                @if($p->status !== 'removed')
                    <form method="POST" action="{{ route('admin.posts.remove', $p) }}" class="inline"
                          data-confirm="حذف البوست؟" data-confirm-action="احذف" data-confirm-tone="danger">
                        @csrf
                        <button class="a-pill pill-blush">احذف</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.posts.restore', $p) }}" class="inline">
                        @csrf
                        <button class="a-pill pill-mint">رجّع</button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="mt-4">{{ $posts->links() }}</div>
@endsection
