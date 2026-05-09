@extends('layouts.app', ['title' => 'إدارة المنيو · ' . $business->name])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.show', $business) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950">📋 منيو {{ $business->name }}</h1>
    </div>

    {{-- QR + share section --}}
    <div class="card-light p-4 mb-4 bg-gradient-to-br from-coral-100 to-honey-100 border-coral-500/20">
        <h3 class="font-extrabold text-ink-950 mb-2 inline-flex items-center gap-2">
            🔳 QR Code للمنيو
        </h3>
        <p class="text-xs text-ink-500 mb-3">طبع الـQR ده وحطّه على الترابيزات. أي زبون يصوّره → المنيو يفتحله مباشرة.</p>
        @php $menuUrl = route('menu.public', $business); @endphp
        <div class="flex items-center gap-3">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=8&color=0B0B0C&bgcolor=FFFFFF&data={{ urlencode($menuUrl) }}"
                 alt="QR" class="w-24 h-24 rounded-2xl bg-white p-1 shrink-0">
            <div class="flex-1 min-w-0 space-y-2">
                <a href="{{ $menuUrl }}" target="_blank" class="text-xs text-coral-600 font-bold hover:underline break-all">{{ $menuUrl }}</a>
                <div class="flex gap-2">
                    <a href="https://api.qrserver.com/v1/create-qr-code/?size=600x600&margin=20&data={{ urlencode($menuUrl) }}"
                       download="qr-{{ $business->id }}.png" target="_blank"
                       class="btn-primary !py-1.5 !px-3 text-xs">⬇ نزّل QR</a>
                    <button type="button" data-share data-share-url="{{ $menuUrl }}"
                            data-share-title="منيو {{ $business->name }}"
                            class="card-light !shadow-none !py-1.5 !px-3 text-xs font-bold text-ink-950 hover:bg-cream-100 transition inline-flex items-center gap-1">
                        <x-icon name="share" class="w-3 h-3"/> شارك
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add category --}}
    <form method="POST" action="{{ route('menu.category.store', $business) }}" class="card-light p-3 mb-3 flex gap-2">
        @csrf
        <input type="text" name="name" required maxlength="80" placeholder="اسم القسم — مثلاً: بيتزا، مشروبات، حلو…"
               class="flex-1 bg-cream-100 rounded-xl px-3 py-2 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-sm">
        <button class="btn-primary !py-2 !px-4 text-xs">+ قسم</button>
    </form>

    {{-- Categories with their items --}}
    @forelse($business->menuCategories as $cat)
        <div class="card-light p-4 mb-3">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-extrabold text-ink-950">{{ $cat->name }}</h3>
                <form method="POST" action="{{ route('menu.category.destroy', $cat) }}" data-confirm="حذف القسم؟ كل الأصناف اللي فيه هتفقد القسم بس مش هتتمسح." data-confirm-tone="danger">
                    @csrf @method('DELETE')
                    <button class="text-xs text-blush-500 font-bold hover:underline">احذف القسم</button>
                </form>
            </div>

            <div class="space-y-2 mb-3">
                @foreach($cat->items as $item)
                    @include('menu.partials.item-row', ['item' => $item])
                @endforeach
            </div>

            @include('menu.partials.add-item-form', ['business' => $business, 'categoryId' => $cat->id])
        </div>
    @empty
        <div class="card-light p-8 text-center text-ink-500 text-sm">
            ابدأ ضيف قسم (مثلاً: "بيتزا" أو "مشروبات") عشان تنظّم منيوك.
        </div>
    @endforelse

    {{-- Items without category --}}
    @if(isset($looseItems) && $looseItems->isNotEmpty())
        <div class="card-light p-4 mb-3 border-coral-500/20">
            <h3 class="font-bold text-ink-500 mb-3 text-sm">أصناف بدون قسم</h3>
            <div class="space-y-2">
                @foreach($looseItems as $item)
                    @include('menu.partials.item-row', ['item' => $item])
                @endforeach
            </div>
        </div>
    @endif

    {{-- Add item without category --}}
    <div class="card-light p-4">
        <h3 class="font-bold text-ink-950 mb-3 text-sm">+ أضف صنف</h3>
        @include('menu.partials.add-item-form', ['business' => $business, 'categoryId' => null])
    </div>
</div>
@endsection
