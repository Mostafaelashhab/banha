@extends('admin.layouts.admin', ['title' => 'لوحة التحكم · Admin'])

@section('content')
<h1 class="text-2xl font-black mb-1">أهلاً يا أدمن 👋</h1>
<p class="text-white/60 text-sm mb-6">إحصاءات بنهاوي ساعة بساعة</p>

{{-- Stats grid --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    @php
        $tiles = [
            ['المستخدمين',     $stats['users'],          '+'.$stats['users_today'].' النهاردة', 'user',      'coral'],
            ['البوستات',       $stats['posts'],          '+'.$stats['posts_today'].' النهاردة', 'flame',     'honey'],
            ['تنبيهات شغّالة', $stats['alerts_active'],  $stats['alerts_verified'].' موثّق',   'bolt',      'mint'],
            ['البلاغات',       $stats['reports_open'],   'مفتوح',                               'flag',      'blush'],
            ['النشاطات',       $stats['businesses'],     $stats['biz_pending'].' بانتظار',     'bag',       'coral'],
            ['الكومنتات',      $stats['comments'],       null,                                  'bell',      'honey'],
            ['أسعار الأسبوع',  $stats['prices_week'],    'من '.$stats['prices'],                'tag',       'mint'],
            ['اشتراكات Push',  $stats['subs_total'],     'جهاز',                                'bell',      'blush'],
        ];
    @endphp
    @foreach($tiles as [$lbl, $val, $sub, $ic, $tone])
        <div class="a-card p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="w-9 h-9 rounded-xl pill-{{ $tone }} grid place-items-center">
                    <x-icon :name="$ic" class="w-4 h-4"/>
                </span>
            </div>
            <div class="text-2xl md:text-3xl font-black">{{ number_format((int) $val) }}</div>
            <div class="text-[11px] text-white/55 mt-1">{{ $lbl }}</div>
            @if($sub)
                <div class="text-[10px] text-white/40 mt-1">{{ $sub }}</div>
            @endif
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-4">
    {{-- Signups chart --}}
    <div class="a-card p-5">
        <h3 class="text-sm font-extrabold mb-4">تسجيلات آخر ٧ أيام</h3>
        @if($signupsByDay->isNotEmpty())
            @php $max = max($signupsByDay->max('c'), 1); @endphp
            <div class="flex items-end gap-1.5 h-32">
                @foreach($signupsByDay as $row)
                    @php $h = max(8, ($row->c / $max) * 100); @endphp
                    <div class="flex-1 flex flex-col items-center gap-1.5">
                        <div class="text-[10px] font-bold text-white/85">{{ $row->c }}</div>
                        <div class="w-full rounded-t-lg brand-bg" style="height: {{ $h }}%"></div>
                        <div class="text-[10px] text-white/40">{{ \Illuminate\Support\Carbon::parse($row->d)->format('d/m') }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-white/50 text-sm">مفيش بيانات.</p>
        @endif
    </div>

    {{-- Top zones --}}
    <div class="a-card p-5">
        <h3 class="text-sm font-extrabold mb-4">أنشط المناطق</h3>
        <div class="space-y-2">
            @foreach($topZones as $z)
                @php
                    $color = \App\Support\AnonSeed::avatarColor($z->name);
                    $init  = \App\Support\AnonSeed::initial($z->name);
                @endphp
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl grid place-items-center text-white font-black text-sm shrink-0"
                          style="background: {{ $color }}">{{ $init }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold">{{ $z->name }}</div>
                        <div class="text-[11px] text-white/50">{{ $z->governorate }}</div>
                    </div>
                    <div class="text-end">
                        <div class="text-lg font-black">{{ $z->posts_count }}</div>
                        <div class="text-[10px] text-white/40">بوست</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-4 mt-4">
    {{-- Recent reports --}}
    <div class="a-card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-extrabold">آخر البلاغات</h3>
            <a href="{{ route('admin.reports') }}" class="text-xs text-coral-400 font-bold">شوف الكل ←</a>
        </div>
        @forelse($recentReports as $r)
            <div class="flex items-center gap-3 py-2 border-b border-white/5 last:border-0">
                <span class="w-8 h-8 rounded-lg pill-blush grid place-items-center text-xs">
                    <x-icon name="flag" class="w-3.5 h-3.5"/>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold">{{ $r->reason }} · {{ $r->target_type }} #{{ $r->target_id }}</div>
                    <div class="text-[11px] text-white/40">{{ $r->created_at->diffForHumans() }}</div>
                </div>
            </div>
        @empty
            <p class="text-white/50 text-sm">مفيش بلاغات مفتوحة.</p>
        @endforelse
    </div>

    {{-- Recent signups --}}
    <div class="a-card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-extrabold">آخر التسجيلات</h3>
            <a href="{{ route('admin.users') }}" class="text-xs text-coral-400 font-bold">شوف الكل ←</a>
        </div>
        @foreach($recentSignups as $u)
            <div class="flex items-center gap-3 py-2 border-b border-white/5 last:border-0">
                <span class="w-8 h-8 rounded-full grid place-items-center text-white font-bold text-xs"
                      style="background: {{ \App\Support\AnonSeed::avatarColor($u->username) }}">
                    {{ \App\Support\AnonSeed::initial($u->username) }}
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold truncate">{{ $u->username }}</div>
                    <div class="text-[11px] text-white/40">{{ $u->phone }} · {{ $u->created_at->diffForHumans() }}</div>
                </div>
                <span class="a-pill bg-white/10 text-white/70">{{ $u->verification_tier }}</span>
            </div>
        @endforeach
    </div>
</div>

{{-- Tools --}}
<div class="a-card p-5 mt-4">
    <h3 class="text-sm font-extrabold mb-3">أدوات</h3>
    <div class="flex flex-wrap gap-2">
        <form method="POST" action="{{ route('admin.recheck.tiers') }}">
            @csrf
            <button type="submit" class="btn-primary !py-2 !px-4 text-sm">
                <x-icon name="check" class="w-4 h-4"/>
                Re-check Silver tiers
            </button>
        </form>
        <a href="{{ route('admin.broadcast') }}" class="btn-dark !py-2 !px-4 text-sm" style="background: rgba(255,255,255,.08)">
            <x-icon name="bell" class="w-4 h-4"/>
            Broadcast push
        </a>
    </div>
</div>
@endsection
