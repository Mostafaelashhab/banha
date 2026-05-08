@extends('admin.layouts.admin', ['title' => 'النشاطات · Admin'])

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-black">النشاطات</h1>
    <span class="text-white/50 text-sm">{{ $businesses->total() }}</span>
</div>

<div class="flex gap-2 mb-4">
    <a href="{{ route('admin.businesses') }}"
       class="px-3 py-1.5 rounded-full text-xs font-bold {{ ! $filter ? 'bg-coral-500 text-white' : 'bg-white/5 text-white/60 border border-white/10' }}">الكل</a>
    @foreach(['pending'=>'بانتظار التوثيق','verified'=>'موثّقة','inactive'=>'مخفية'] as $key => $label)
        <a href="{{ route('admin.businesses', ['filter'=>$key]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-bold {{ $filter === $key ? 'bg-coral-500 text-white' : 'bg-white/5 text-white/60 border border-white/10' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="a-card overflow-x-auto">
    <table class="a-table w-full">
        <thead>
            <tr>
                <th>النشاط</th>
                <th>النوع</th>
                <th>المنطقة</th>
                <th>المالك</th>
                <th>تليفون</th>
                <th>التوثيق</th>
                <th>نشط</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($businesses as $b)
                @php $sm = $b->subTypeMeta(); $cm = $b->categoryMeta(); @endphp
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            @if($b->photo_url)
                                <img src="{{ $b->photo_url }}" class="w-9 h-9 rounded-lg object-cover" alt="">
                            @else
                                <span class="w-9 h-9 rounded-lg grid place-items-center text-lg" style="background: {{ $cm['color'] }}30">{{ $b->emoji ?: $sm['emoji'] }}</span>
                            @endif
                            <a href="{{ route('directory.show', $b) }}" target="_blank" class="font-bold hover:text-coral-400">{{ $b->name }}</a>
                        </div>
                    </td>
                    <td class="text-white/70">{{ $sm['label'] }}</td>
                    <td>{{ $b->zone?->name ?? '—' }}</td>
                    <td>
                        @if($b->owner)
                            <a href="{{ route('profile.show', $b->owner->username) }}" target="_blank" class="text-coral-400 text-xs">{{ $b->owner->username }}</a>
                        @else
                            <span class="text-white/40 text-xs">seed</span>
                        @endif
                    </td>
                    <td dir="ltr" class="text-white/70 text-xs">{{ $b->phone ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.businesses.verify', $b) }}" class="inline">
                            @csrf
                            <button class="a-pill {{ $b->is_verified ? 'pill-mint' : 'bg-white/10 text-white/40' }}">
                                {{ $b->is_verified ? '✓ موثّق' : '— غير موثّق' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.businesses.toggle', $b) }}" class="inline">
                            @csrf
                            <button class="a-pill {{ $b->is_active ? 'bg-white/10 text-white/70' : 'pill-blush' }}">
                                {{ $b->is_active ? 'نشط' : 'مخفي' }}
                            </button>
                        </form>
                    </td>
                    <td class="text-[10px] text-white/40">{{ $b->created_at->diffForHumans(short: true) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $businesses->links() }}</div>
@endsection
