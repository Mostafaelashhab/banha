@extends('admin.layouts.admin', ['title' => 'النشاطات · Admin'])

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-black">النشاطات</h1>
    <span class="text-ink-500 text-sm">{{ $businesses->total() }}</span>
</div>

<div class="flex gap-2 mb-4">
    <a href="{{ route('admin.businesses') }}"
       class="px-3 py-1.5 rounded-full text-xs font-bold {{ ! $filter ? 'bg-coral-500 text-ink-950' : 'bg-cream-100 text-ink-500 border border-ink-950/8' }}">الكل</a>
    @foreach(['pending'=>'بانتظار التوثيق','verified'=>'موثّقة','inactive'=>'مخفية'] as $key => $label)
        <a href="{{ route('admin.businesses', ['filter'=>$key]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-bold {{ $filter === $key ? 'bg-coral-500 text-ink-950' : 'bg-cream-100 text-ink-500 border border-ink-950/8' }}">
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
                <th>أنشئ</th>
                <th class="text-end">إجراءات</th>
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
                    <td class="text-ink-500">{{ $sm['label'] }}</td>
                    <td>{{ $b->zone?->name ?? '—' }}</td>
                    <td>
                        @if($b->owner)
                            <a href="{{ route('profile.show', $b->owner->username) }}" target="_blank" class="text-coral-400 text-xs">{{ $b->owner->username }}</a>
                        @else
                            <span class="text-ink-400 text-xs">seed</span>
                        @endif
                    </td>
                    <td dir="ltr" class="text-ink-500 text-xs">{{ $b->phone ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.businesses.verify', $b) }}" class="inline">
                            @csrf
                            <button class="a-pill {{ $b->is_verified ? 'pill-mint' : 'bg-cream-200 text-ink-400' }}">
                                {{ $b->is_verified ? '✓ موثّق' : '— غير موثّق' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.businesses.toggle', $b) }}" class="inline">
                            @csrf
                            <button class="a-pill {{ $b->is_active ? 'bg-cream-200 text-ink-500' : 'pill-blush' }}">
                                {{ $b->is_active ? 'نشط' : 'مخفي' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        @if($b->isPromoted())
                            <span class="a-pill bg-honey-500 text-ink-950" title="ينتهي {{ $b->promoted_until->translatedFormat('d M') }}">
                                ⭐ {{ (int) now()->diffInDays($b->promoted_until, false) }} يوم
                            </span>
                            <form method="POST" action="{{ route('admin.businesses.promote', $b) }}" class="inline">
                                @csrf
                                <input type="hidden" name="days" value="0">
                                <button class="a-pill bg-blush-100 text-blush-500 px-2 py-1" title="إلغاء الترويج">✕</button>
                            </form>
                        @else
                            <div class="inline-flex gap-1">
                                @foreach([7, 30] as $d)
                                    <form method="POST" action="{{ route('admin.businesses.promote', $b) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="days" value="{{ $d }}">
                                        <button class="a-pill bg-honey-100 text-honey-700 hover:bg-honey-500 hover:text-ink-950 transition px-2 py-1" title="روّج لمدة {{ $d }} يوم">
                                            ⭐ {{ $d }}ي
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="text-[10px] text-ink-400">{{ $b->created_at->diffForHumans(short: true) }}</td>
                    <td class="text-end">
                        <div class="inline-flex items-center gap-1">
                            {{-- "ابعت دعوة" — only for unowned businesses that have a phone we can WhatsApp.
                                 Always renders the default state — re-sending is allowed. --}}
                            @if(! $b->owner_user_id && ($b->whatsapp || $b->phone))
                                <button type="button"
                                        data-invite-open
                                        data-invite-url="{{ route('admin.businesses.invite.preview', $b) }}"
                                        data-invite-send-url="{{ route('admin.businesses.invite.send', $b) }}"
                                        class="a-pill px-2.5 py-1.5 transition bg-honey-100 text-honey-700 hover:bg-honey-500 hover:text-ink-950"
                                        title="ابعت دعوة استلام الصفحة على واتساب">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z"/>
                                    </svg>
                                    دعوة
                                </button>
                            @endif
                            <a href="{{ route('directory.edit', $b) }}"
                               class="a-pill bg-coral-100 text-coral-700 hover:bg-coral-500 hover:text-white px-2.5 py-1.5 transition" title="تعديل">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                    <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z"/>
                                </svg>
                                تعديل
                            </a>
                            <form method="POST" action="{{ route('directory.destroy', $b) }}" class="inline"
                                  data-confirm="حذف النشاط نهائياً؟"
                                  data-confirm-body="مش هيرجع تاني — لكن البيانات هتفضل في الـ DB كـ inactive."
                                  data-confirm-action="احذف"
                                  data-confirm-tone="danger">
                                @csrf @method('DELETE')
                                <button type="submit" class="a-pill bg-cream-200 text-ink-400 hover:bg-blush-500 hover:text-white px-2 py-1.5 transition" title="حذف">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $businesses->links() }}</div>

@include('admin.partials.invite-modal')
@endsection
