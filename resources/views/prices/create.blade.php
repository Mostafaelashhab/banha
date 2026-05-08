@extends('layouts.app', ['title' => 'ضيف سعر · بنهاوي'])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('prices.index') }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950">ضيف سعر</h1>
    </div>

    <form method="POST" action="{{ route('prices.store') }}" class="card-light p-5 space-y-4">
        @csrf

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">المنتج *</label>
            <select name="product_id" required
                    class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                <option value="">اختار</option>
                @foreach($products->groupBy('category') as $cat => $items)
                    <optgroup label="{{ \App\Models\Product::CATEGORIES[$cat] ?? $cat }}">
                        @foreach($items as $p)
                            <option value="{{ $p->id }}" {{ (string) $preselect === (string) $p->id ? 'selected' : '' }}>
                                {{ $p->emoji }} {{ $p->name }} · {{ $p->unit }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('product_id') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">السعر (ج) *</label>
                <input type="number" name="price" required step="0.01" min="0.01" max="99999.99"
                       inputmode="decimal" placeholder="12.50"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition text-2xl font-black text-center">
                @error('price') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">المنطقة *</label>
                <select name="zone_id" required
                        class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" {{ auth()->user()->zone_id == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">المحل / السوق</label>
            <input type="text" name="shop_name" maxlength="100" placeholder="مثلاً: السوق البلدي · أبو محمد"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">ملاحظة (اختياري)</label>
            <input type="text" name="notes" maxlength="200" placeholder="مثلاً: السعر للجملة"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-3">
            سجّل السعر
            <x-icon name="arrow-left" class="w-4 h-4"/>
        </button>

        <p class="text-ink-400 text-xs text-center pt-1">
            تقاريرك بتدخل في المتوسط مع باقي البنهاوية. متشاركش أسعار غلط.
        </p>
    </form>
</div>
@endsection
