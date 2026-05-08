@extends('layouts.app', ['title' => 'تعديل ' . $business->name])

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.show', $business) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <h1 class="text-xl font-extrabold text-ink-950">تعديل النشاط</h1>
    </div>

    <form method="POST" action="{{ route('directory.update', $business) }}" class="card-light p-5 space-y-4" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        {{-- Photo --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">صورة النشاط</label>
            <div class="flex items-center gap-3">
                @if($business->photo_url)
                    <img src="{{ $business->photo_url }}" alt="" class="w-16 h-16 rounded-xl object-cover shrink-0">
                @else
                    <span class="w-16 h-16 rounded-xl pill-coral grid place-items-center text-2xl shrink-0">{{ ($business->emoji && $business->emoji !== '🔥📦') ? $business->emoji : '📍' }}</span>
                @endif
                <label class="flex-1 cursor-pointer bg-cream-100 rounded-2xl p-3 border border-ink-950/8 hover:border-coral-500/40 transition text-sm font-bold text-ink-950">
                    <span data-photo-name>{{ $business->photo_url ? 'استبدل الصورة' : 'ارفع صورة' }}</span>
                    <span class="block text-[10px] text-ink-500 font-normal mt-0.5">JPG / PNG / WEBP · حتى ٣ ميجا</span>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="this.parentElement.querySelector('[data-photo-name]').textContent = this.files[0]?.name || 'استبدل الصورة'">
                </label>
            </div>
            @error('photo') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div data-subtype-picker>
            <label class="text-xs font-bold text-ink-500 mb-2 block">نوع النشاط *</label>
            @foreach(\App\Models\Business::CATEGORIES as $catKey => $catMeta)
                <div class="mb-4">
                    <div class="text-[11px] font-bold text-ink-400 mb-2">{{ $catMeta['label'] }}</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($subTypes as $key => $st)
                            @if($st['category'] === $catKey)
                                @php $isOther = str_ends_with($key, '_other'); @endphp
                                <label class="cursor-pointer">
                                    <input type="radio" name="sub_type" value="{{ $key }}" class="peer sr-only"
                                           {{ old('sub_type', $business->sub_type) === $key ? 'checked' : '' }}
                                           data-is-other="{{ $isOther ? '1' : '0' }}">
                                    <span class="block px-3 py-1.5 rounded-full text-xs font-bold bg-cream-100 border border-ink-950/8 peer-checked:bg-coral-500 peer-checked:text-white peer-checked:border-coral-500 transition inline-flex items-center gap-1.5">
                                        <x-icon :name="$st['icon'] ?? 'briefcase'" class="w-3.5 h-3.5"/>
                                        {{ $st['label'] }}
                                    </span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="mt-2 {{ str_ends_with(old('sub_type', $business->sub_type), '_other') ? '' : 'hidden' }}" data-custom-subtype-wrap>
                <label class="text-xs font-bold text-ink-500 mb-1 block">اكتب نوع نشاطك</label>
                <input type="text" name="custom_sub_type" maxlength="80"
                       value="{{ old('custom_sub_type', $business->custom_sub_type) }}"
                       placeholder="مثلاً: محل ساعات، مدرّس عربي…"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                @error('custom_sub_type') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">اسم النشاط *</label>
            <input type="text" name="name" required minlength="3" maxlength="120" value="{{ old('name', $business->name) }}"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            @error('name') <p class="text-blush-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">المنطقة *</label>
            <select name="zone_id" required
                    class="select-styled w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" {{ old('zone_id', $business->zone_id) == $z->id ? 'selected' : '' }}>{{ $z->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">العنوان</label>
            <input type="text" name="address" maxlength="200" value="{{ old('address', $business->address) }}"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">رقم تليفون</label>
                <input type="tel" name="phone" inputmode="numeric" maxlength="11" value="{{ old('phone', $business->phone) }}"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            </div>
            <div>
                <label class="text-xs font-bold text-ink-500 mb-1 block">رقم واتساب</label>
                <input type="tel" name="whatsapp" inputmode="numeric" maxlength="11" value="{{ old('whatsapp', $business->whatsapp) }}"
                       class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
            </div>
        </div>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">المواعيد</label>
            <input type="text" name="hours" maxlength="100" value="{{ old('hours', $business->hours) }}"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition">
        </div>

        <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer border border-ink-950/8 has-[:checked]:bg-mint-100/50 has-[:checked]:border-mint-500/40 transition">
            <input type="checkbox" name="is_24h" value="1" {{ old('is_24h', $business->is_24h) ? 'checked' : '' }} class="sr-only peer">
            <span class="w-12 h-7 rounded-full bg-ink-300 relative transition peer-checked:bg-mint-500">
                <span class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white shadow transition peer-checked:translate-x-[-1.25rem] rtl:peer-checked:translate-x-5"></span>
            </span>
            <span class="text-sm font-bold text-ink-950">شغّال ٢٤ ساعة</span>
        </label>

        <div>
            <label class="text-xs font-bold text-ink-500 mb-1 block">وصف مختصر</label>
            <textarea name="description" rows="3" maxlength="1000"
                      class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('description', $business->description) }}</textarea>
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-3">
            احفظ التعديلات
            <x-icon name="check" class="w-4 h-4"/>
        </button>
    </form>

    <form method="POST" action="{{ route('directory.destroy', $business) }}"
          class="mt-3"
          data-confirm="حذف النشاط؟"
          data-confirm-body="هينحذف خالص ومش هيرجع تاني."
          data-confirm-action="احذف"
          data-confirm-tone="danger">
        @csrf
        @method('DELETE')
        <button type="submit" class="card-light p-3 w-full text-blush-500 font-bold text-sm hover:bg-blush-100/50 transition flex items-center justify-center gap-2">
            <x-icon name="trash" class="w-4 h-4"/>
            احذف النشاط نهائياً
        </button>
    </form>
</div>
@endsection
