@extends('admin.layouts.admin', ['title' => 'لوحة التحكم · Admin'])

@section('content')

{{-- ─── Hero ─────────────────────────────────────── --}}
<div class="rounded-3xl p-5 md:p-7 mb-5 relative overflow-hidden brand-bg">
    <div class="absolute -top-16 -end-16 w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
    <div class="absolute -bottom-16 -start-16 w-64 h-64 rounded-full bg-honey-400/40 blur-3xl"></div>

    <div class="relative grid md:grid-cols-3 gap-6 items-center">
        <div class="md:col-span-2">
            <div class="text-white/80 text-sm font-bold inline-flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                مباشر · {{ now()->translatedFormat('l j F') }}
            </div>
            <h1 class="text-2xl md:text-4xl font-black text-white leading-tight">
                أهلاً يا {{ auth()->user()->username }} 👋
                <br>
                {{ $stats['users'] }} بنهاوي ضمن العائلة
            </h1>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-ink-950/30 backdrop-blur rounded-2xl p-4 border border-white/15">
                <div class="text-white/85 text-[11px] font-bold mb-1">+ النهاردة</div>
                <div class="text-3xl font-black text-white">{{ $stats['users_today'] }}</div>
                <div class="text-[10px] mt-1 {{ $stats['users_pct'] >= 0 ? 'text-mint-100' : 'text-blush-500' }}">
                    {{ $stats['users_pct'] >= 0 ? '↑' : '↓' }} {{ abs($stats['users_pct']) }}% من امبارح
                </div>
            </div>
            <div class="bg-ink-950/30 backdrop-blur rounded-2xl p-4 border border-white/15">
                <div class="text-white/85 text-[11px] font-bold mb-1">بحاجة لإجراء</div>
                <div class="text-3xl font-black text-white">{{ $stats['reports_open'] + $stats['biz_pending'] + $stats['posts_flagged'] }}</div>
                <div class="text-[10px] text-white/70 mt-1">
                    {{ $stats['reports_open'] }} بلاغ · {{ $stats['biz_pending'] }} نشاط · {{ $stats['posts_flagged'] }} بوست
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Quick action chips ────────────────────────── --}}
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('admin.reports') }}" class="a-pill bg-blush-500/15 text-blush-500 border border-blush-500/30 px-4 py-2 hover:bg-blush-500/25 transition">
        <x-icon name="flag" class="w-3.5 h-3.5"/>
        {{ $stats['reports_open'] }} بلاغ مفتوح
    </a>
    <a href="{{ route('admin.businesses', ['filter'=>'pending']) }}" class="a-pill bg-honey-400/15 text-honey-400 border border-honey-400/30 px-4 py-2 hover:bg-honey-400/25 transition">
        <x-icon name="bag" class="w-3.5 h-3.5"/>
        {{ $stats['biz_pending'] }} نشاط بانتظار
    </a>
    <a href="{{ route('admin.posts', ['status'=>'flagged']) }}" class="a-pill bg-coral-500/15 text-coral-400 border border-coral-500/30 px-4 py-2 hover:bg-coral-500/25 transition">
        <x-icon name="flame" class="w-3.5 h-3.5"/>
        {{ $stats['posts_flagged'] }} بوست مُبلَّغ
    </a>
    <a href="{{ route('admin.broadcast') }}" class="a-pill bg-mint-500/15 text-mint-100 border border-mint-500/30 px-4 py-2 hover:bg-mint-500/25 transition ms-auto">
        <x-icon name="bell" class="w-3.5 h-3.5"/>
        ابعت إشعار
    </a>
</div>

{{-- ─── Sparkline stat tiles ──────────────────────── --}}
@php
    $renderSparkline = function (array $series, string $color = '#FF7A4D') {
        $max = max(array_column($series, 'c')) ?: 1;
        $w = 100; $h = 32;
        $step = $w / max(count($series) - 1, 1);
        $points = [];
        foreach ($series as $i => $r) {
            $x = round($i * $step, 2);
            $y = round($h - ($r['c'] / $max) * ($h - 4) - 2, 2);
            $points[] = "{$x},{$y}";
        }
        $line = implode(' ', $points);
        return '<svg viewBox="0 0 '.$w.' '.$h.'" preserveAspectRatio="none" class="w-full h-8" fill="none">
            <polyline points="'.$line.'" stroke="'.$color.'" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <polygon points="'.$line.' '.($w-0.01).','.$h.' 0,'.$h.'" fill="'.$color.'" fill-opacity=".12" stroke="none"/>
        </svg>';
    };

    $tiles = [
        ['label'=>'مستخدمين',        'value'=>$stats['users'],         'today'=>$stats['users_today'],   'pct'=>$stats['users_pct'],   'series'=>$charts['users'],  'color'=>'#FF7A4D', 'icon'=>'user'],
        ['label'=>'بوستات',          'value'=>$stats['posts'],         'today'=>$stats['posts_today'],   'pct'=>$stats['posts_pct'],   'series'=>$charts['posts'],  'color'=>'#FFB85C', 'icon'=>'flame'],
        ['label'=>'تنبيهات شغّالة', 'value'=>$stats['alerts_active'], 'today'=>$stats['alerts_today'],  'pct'=>$stats['alerts_pct'],  'series'=>$charts['alerts'], 'color'=>'#1FA857', 'icon'=>'bolt'],
        ['label'=>'أسعار مرفوعة',    'value'=>$stats['prices'],        'today'=>$stats['prices_today'],  'pct'=>$stats['prices_pct'],  'series'=>$charts['prices'], 'color'=>'#7C3AED', 'icon'=>'tag'],
    ];
@endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    @foreach($tiles as $t)
        <div class="a-card p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="w-9 h-9 rounded-xl grid place-items-center" style="background: {{ $t['color'] }}25; color: {{ $t['color'] }}">
                    <x-icon :name="$t['icon']" class="w-4 h-4"/>
                </span>
                <span class="text-[10px] font-bold {{ $t['pct'] >= 0 ? 'text-mint-100' : 'text-blush-500' }}">
                    {{ $t['pct'] >= 0 ? '↑' : '↓' }} {{ abs($t['pct']) }}%
                </span>
            </div>
            <div class="text-2xl md:text-3xl font-black">{{ number_format($t['value']) }}</div>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-[11px] text-white/50">{{ $t['label'] }}</span>
                <span class="text-[10px] text-white/40">+{{ $t['today'] }} اليوم</span>
            </div>
            <div class="mt-2">{!! $renderSparkline($t['series'], $t['color']) !!}</div>
        </div>
    @endforeach
</div>

{{-- ─── Secondary stats ───────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
    @php
        $smalls = [
            ['موثّقين',         $stats['users_verified'],   'mint',   'فضي + ذهبي'],
            ['محظورين',         $stats['users_banned'],     'blush',  'حسابات مقفولة'],
            ['كومنتات',         $stats['comments'],         'coral',  'إجمالي'],
            ['نشاطات',         $stats['businesses'],       'honey',  $stats['biz_pending'].' بانتظار'],
            ['تنبيهات موثّقة',  $stats['alerts_verified'],  'mint',   '٣+ تأكيدات'],
            ['أسعار الأسبوع',   $stats['prices_week'],      'coral',  'من '.$stats['prices'].' إجمالي'],
            ['Push Subs',      $stats['subs_total'],       'honey',  'جهاز مشترك'],
            ['البوستات',       $stats['posts'],            'mint',   'إجمالي'],
        ];
    @endphp
    @foreach($smalls as [$lbl, $val, $tone, $sub])
        <div class="a-card p-3.5">
            <div class="text-xl font-black">{{ is_numeric($val) ? number_format((int)$val) : $val }}</div>
            <div class="text-[10px] text-white/50 mt-0.5">{{ $lbl }}</div>
            <div class="text-[9px] text-white/35 mt-1">{{ $sub }}</div>
        </div>
    @endforeach
</div>

{{-- ─── Charts row + activity feed ────────────────── --}}
<div class="grid lg:grid-cols-3 gap-4 mb-4">

    {{-- Big chart: Posts vs Signups --}}
    <div class="a-card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-extrabold">نمو المنصة (٧ أيام)</h3>
            <div class="flex items-center gap-3 text-[10px] font-bold">
                <span class="inline-flex items-center gap-1.5 text-coral-400"><span class="w-2 h-2 rounded-full bg-coral-500"></span>تسجيلات</span>
                <span class="inline-flex items-center gap-1.5 text-honey-400"><span class="w-2 h-2 rounded-full bg-honey-400"></span>بوستات</span>
            </div>
        </div>
        @php
            $allPoints = array_merge(array_column($charts['users'], 'c'), array_column($charts['posts'], 'c'));
            $maxC = max(max($allPoints), 1);
        @endphp
        <div class="flex items-end gap-3 h-40">
            @foreach($charts['users'] as $i => $u)
                @php
                    $p = $charts['posts'][$i];
                    $hu = max(2, ($u['c'] / $maxC) * 100);
                    $hp = max(2, ($p['c'] / $maxC) * 100);
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1.5">
                    <div class="w-full flex items-end gap-1 flex-1">
                        <div class="flex-1 rounded-t-md transition" style="height: {{ $hu }}%; background: linear-gradient(to top, #FF7A4D, #FFB85C)"></div>
                        <div class="flex-1 rounded-t-md transition" style="height: {{ $hp }}%; background: linear-gradient(to top, #FFB85C, #FFC97A); opacity: .75"></div>
                    </div>
                    <div class="flex gap-1.5 text-[10px] font-bold">
                        <span class="text-coral-400">{{ $u['c'] }}</span>
                        <span class="text-white/30">/</span>
                        <span class="text-honey-400">{{ $p['c'] }}</span>
                    </div>
                    <div class="text-[10px] text-white/35">{{ \Illuminate\Support\Carbon::parse($u['d'])->format('d/m') }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Activity timeline --}}
    <div class="a-card p-5 lg:row-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-extrabold">نشاط حي</h3>
            <span class="w-2 h-2 rounded-full bg-mint-500 animate-pulse"></span>
        </div>
        @if($timeline->isEmpty())
            <p class="text-white/50 text-sm">مفيش نشاط لسه.</p>
        @else
            <div class="relative">
                <div class="absolute top-2 bottom-2 start-[18px] w-px bg-white/8"></div>

                <div class="space-y-2">
                    @foreach($timeline as $e)
                        <a href="{{ $e['url'] }}" class="flex items-start gap-3 group hover:bg-white/[.04] -mx-2 px-2 py-1.5 rounded-xl transition relative">
                            <span class="w-9 h-9 rounded-full pill-{{ $e['tone'] }} grid place-items-center shrink-0 z-10 ring-4 ring-ink-900">
                                <x-icon :name="$e['icon']" class="w-3.5 h-3.5"/>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-white truncate group-hover:text-coral-400 transition">{{ $e['title'] }}</div>
                                @if(! empty($e['sub']))
                                    <div class="text-[11px] text-white/50 truncate">{{ $e['sub'] }}</div>
                                @endif
                                <div class="text-[10px] text-white/35 mt-0.5">{{ \Illuminate\Support\Carbon::parse($e['at'])->diffForHumans(['short'=>true]) }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Top zones --}}
    <div class="a-card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-extrabold">أنشط المناطق</h3>
            <a href="{{ route('zones') }}" target="_blank" class="text-xs text-coral-400 font-bold">شوف الكل ←</a>
        </div>
        <div class="space-y-2.5">
            @foreach($topZones as $z)
                @php
                    $pct   = ($z->posts_count / $maxZonePosts) * 100;
                    $color = \App\Support\AnonSeed::avatarColor($z->name);
                    $init  = \App\Support\AnonSeed::initial($z->name);
                @endphp
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl grid place-items-center text-white font-black text-sm shrink-0"
                          style="background: {{ $color }}">{{ $init }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between mb-1">
                            <span class="text-sm font-bold truncate">{{ $z->name }}</span>
                            <span class="text-xs font-black ms-2">{{ $z->posts_count }}</span>
                        </div>
                        <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition" style="width: {{ $pct }}%; background: {{ $color }}"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

{{-- ─── Tools row ─────────────────────────────────── --}}
<div class="a-card p-4 flex flex-wrap items-center gap-2">
    <span class="text-xs text-white/50 font-bold me-2">أدوات سريعة:</span>
    <form method="POST" action="{{ route('admin.recheck.tiers') }}" class="inline">
        @csrf
        <button type="submit" class="a-pill bg-white/10 hover:bg-white/15 text-white px-3 py-1.5">
            <x-icon name="check" class="w-3.5 h-3.5"/>
            Re-check Silver tiers
        </button>
    </form>
    <a href="{{ route('admin.users') }}" class="a-pill bg-white/10 hover:bg-white/15 text-white px-3 py-1.5">
        <x-icon name="user" class="w-3.5 h-3.5"/>
        إدارة المستخدمين
    </a>
    <a href="{{ route('admin.broadcast') }}" class="a-pill bg-white/10 hover:bg-white/15 text-white px-3 py-1.5">
        <x-icon name="bell" class="w-3.5 h-3.5"/>
        إرسال إشعار
    </a>
</div>

@endsection
