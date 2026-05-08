@extends('admin.layouts.admin', ['title' => 'المستخدمين · Admin'])

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-black">المستخدمين</h1>
    <span class="text-white/50 text-sm">{{ $users->total() }} مستخدم</span>
</div>

<form method="GET" class="a-card p-3 mb-4 flex items-center gap-2">
    <input type="text" name="q" value="{{ $q }}" placeholder="username أو رقم موبايل…"
           class="flex-1 bg-transparent outline-0 px-3 py-2 text-white placeholder-white/30 text-sm">
    <select name="filter" class="select-styled bg-ink-800 text-white rounded-full px-3 py-2 text-xs border border-white/10">
        <option value="">الكل</option>
        <option value="banned" {{ $filter === 'banned' ? 'selected' : '' }}>محظورين</option>
        <option value="verified" {{ $filter === 'verified' ? 'selected' : '' }}>موثّقين</option>
        <option value="admins" {{ $filter === 'admins' ? 'selected' : '' }}>أدمنز</option>
    </select>
    <button class="btn-primary !py-2 !px-4 text-sm">دوّر</button>
</form>

<div class="a-card overflow-x-auto">
    <table class="a-table w-full">
        <thead>
            <tr>
                <th>المستخدم</th>
                <th>التليفون</th>
                <th>المنطقة</th>
                <th>Tier</th>
                <th>سمعة</th>
                <th>أدمن</th>
                <th>محظور</th>
                <th>إجراء</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
                @php $color = \App\Support\AnonSeed::avatarColor($u->username); $init = \App\Support\AnonSeed::initial($u->username); @endphp
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            @if($u->avatar_url)
                                <img src="{{ $u->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt="">
                            @else
                                <span class="w-8 h-8 rounded-full grid place-items-center text-white font-bold text-xs" style="background: {{ $color }}">{{ $init }}</span>
                            @endif
                            <a href="{{ route('profile.show', $u->username) }}" target="_blank" class="font-bold hover:text-coral-400">{{ $u->username }}</a>
                        </div>
                    </td>
                    <td dir="ltr">{{ $u->phone }}</td>
                    <td>{{ $u->zone?->name ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.tier', $u) }}" class="inline">
                            @csrf
                            <select name="tier" onchange="this.form.submit()" class="bg-ink-800 text-white text-xs rounded-full px-2 py-1 border border-white/10">
                                @foreach(['none','bronze','silver','gold'] as $t)
                                    <option value="{{ $t }}" {{ $u->verification_tier === $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td>{{ $u->reputation }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.admin', $u) }}" class="inline"
                              data-confirm="{{ $u->is_admin ? 'إزالة صلاحية الأدمن؟' : 'تعيين كأدمن؟' }}"
                              data-confirm-action="نعم">
                            @csrf
                            <button type="submit" class="a-pill {{ $u->is_admin ? 'bg-coral-500 text-white' : 'bg-white/10 text-white/40' }}">
                                {{ $u->is_admin ? '★ أدمن' : '—' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        @if($u->is_admin)
                            <span class="a-pill bg-white/5 text-white/30">—</span>
                        @else
                            <form method="POST" action="{{ route('admin.users.ban', $u) }}" class="inline"
                                  data-confirm="{{ $u->is_banned ? 'رفع الحظر عن '.$u->username.'؟' : 'حظر '.$u->username.'؟' }}"
                                  data-confirm-action="نعم"
                                  data-confirm-tone="danger">
                                @csrf
                                <button type="submit" class="a-pill {{ $u->is_banned ? 'pill-blush' : 'bg-white/10 text-white/40' }}">
                                    {{ $u->is_banned ? '⛔ محظور' : '—' }}
                                </button>
                            </form>
                        @endif
                    </td>
                    <td class="text-white/50 text-[11px]">{{ $u->created_at->diffForHumans(short: true) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
