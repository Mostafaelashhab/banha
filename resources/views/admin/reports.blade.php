@extends('admin.layouts.admin', ['title' => 'البلاغات · Admin'])

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-black">البلاغات</h1>
    <span class="text-white/50 text-sm">{{ $reports->total() }}</span>
</div>

<div class="flex gap-2 mb-4">
    @foreach(['open'=>'مفتوحة','resolved'=>'محلولة','dismissed'=>'مرفوضة'] as $key => $label)
        <a href="{{ route('admin.reports', ['status'=>$key]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-bold
                  {{ $status === $key ? 'bg-coral-500 text-white' : 'bg-white/5 text-white/60 border border-white/10' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="space-y-2">
    @foreach($reports as $r)
        @php $post = $r->target_type === 'post' ? ($posts[$r->target_id] ?? null) : null; $reporter = $reporters[$r->reporter_id] ?? null; @endphp
        <div class="a-card p-4">
            <div class="flex items-start gap-3 mb-2">
                <span class="a-pill pill-blush">{{ $r->reason }}</span>
                <span class="a-pill bg-white/10 text-white/60">{{ $r->target_type }} #{{ $r->target_id }}</span>
                <span class="text-[11px] text-white/40">من {{ $reporter?->username ?? 'مستخدم' }}</span>
                <span class="ms-auto text-[11px] text-white/40">{{ $r->created_at->diffForHumans() }}</span>
            </div>

            @if($r->details)
                <p class="text-white/70 text-xs italic mb-3">"{{ $r->details }}"</p>
            @endif

            @if($post)
                <div class="bg-ink-800 rounded-xl p-3 mb-3 text-sm">
                    <div class="text-[10px] text-white/40 mb-1">المحتوى المُبلَّغ:</div>
                    @if($post->is_anonymous)
                        <span class="a-pill pill-coral mb-2 inline-block">🤫 مجهول</span>
                    @else
                        <span class="text-white/60 text-xs">{{ $post->user?->username }} ·</span>
                    @endif
                    <p class="text-white/90">{{ \Illuminate\Support\Str::limit($post->body, 200) }}</p>
                </div>
            @endif

            @if($r->status === 'open')
                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.reports.resolve', $r) }}">
                        @csrf
                        <input type="hidden" name="action" value="dismiss">
                        <button class="a-pill bg-white/10 text-white/60 hover:bg-white/20 px-3 py-1.5">تجاهل</button>
                    </form>
                    @if($r->target_type === 'post')
                        <form method="POST" action="{{ route('admin.reports.resolve', $r) }}"
                              data-confirm="حذف البوست؟" data-confirm-action="احذف" data-confirm-tone="danger">
                            @csrf
                            <input type="hidden" name="action" value="remove_post">
                            <button class="a-pill pill-blush px-3 py-1.5">احذف البوست</button>
                        </form>
                        <form method="POST" action="{{ route('admin.reports.resolve', $r) }}"
                              data-confirm="حظر صاحب البوست؟" data-confirm-action="احظر" data-confirm-tone="danger">
                            @csrf
                            <input type="hidden" name="action" value="ban_user">
                            <button class="a-pill bg-blush-500 text-white px-3 py-1.5">احظر اليوزر</button>
                        </form>
                    @endif
                </div>
            @else
                <span class="a-pill {{ $r->status === 'resolved' ? 'pill-mint' : 'bg-white/10 text-white/40' }}">
                    {{ $r->status === 'resolved' ? '✓ تم الحل' : 'تم التجاهل' }}
                </span>
            @endif
        </div>
    @endforeach
</div>

<div class="mt-4">{{ $reports->links() }}</div>
@endsection
