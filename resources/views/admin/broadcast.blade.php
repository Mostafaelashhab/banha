@extends('admin.layouts.admin', ['title' => 'إرسال إشعار · Admin'])

@section('content')
<h1 class="text-2xl font-black mb-1">إرسال إشعار</h1>
<p class="text-ink-500 text-sm mb-6">ابعت push notification لكل المستخدمين أو لمنطقة معينة</p>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 a-card p-5">
        <form method="POST" action="{{ route('admin.broadcast.send') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">المنطقة (اختياري)</label>
                <select name="zone" class="select-styled w-full bg-cream-100 text-ink-950 rounded-2xl px-4 py-3 border border-ink-950/8">
                    <option value="">كل المستخدمين ({{ $subsCount }} جهاز)</option>
                    @foreach($zones as $z)
                        @php $c = $subsByZone[$z->id] ?? 0; @endphp
                        <option value="{{ $z->id }}">{{ $z->name }} ({{ $c }} جهاز)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">العنوان (max 80)</label>
                <input type="text" name="title" required maxlength="80"
                       placeholder="🔥 إيه اللي بيحصل في بنها؟"
                       class="w-full bg-cream-100 text-ink-950 rounded-2xl px-4 py-3 border border-ink-950/8 outline-0 focus:border-coral-500 transition placeholder-ink-400">
                @error('title') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">النص (max 200)</label>
                <textarea name="body" required rows="3" maxlength="200"
                          placeholder="نص الإشعار…"
                          class="w-full bg-cream-100 text-ink-950 rounded-2xl px-4 py-3 border border-ink-950/8 outline-0 focus:border-coral-500 transition placeholder-ink-400 resize-none"></textarea>
                @error('body') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">رابط (اختياري)</label>
                <input type="text" name="url" placeholder="/feed"
                       class="w-full bg-cream-100 text-ink-950 rounded-2xl px-4 py-3 border border-ink-950/8 outline-0 focus:border-coral-500 transition placeholder-ink-400">
            </div>

            <button type="submit" class="btn-primary w-full justify-center !py-3"
                    data-confirm="ابعت الإشعار؟" data-confirm-action="ابعت">
                <x-icon name="bell" class="w-4 h-4"/>
                ابعت الإشعار
            </button>
        </form>
    </div>

    <div class="a-card p-5">
        <h3 class="text-sm font-extrabold mb-4">إحصاءات Push</h3>
        <div class="space-y-3">
            <div>
                <div class="text-2xl font-black">{{ $subsCount }}</div>
                <div class="text-[11px] text-ink-500">إجمالي الأجهزة المشتركة</div>
            </div>
            <div class="pt-3 border-t border-ink-950/8 space-y-2">
                @foreach($zones as $z)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-ink-500">{{ $z->name }}</span>
                        <span class="font-bold">{{ $subsByZone[$z->id] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
