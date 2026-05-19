@extends('layouts.app', [
    'title'       => 'فعّل شارة موثّق · ' . $business->name . ' · بنها.shop',
    'description' => 'فعّل شارة موثّق على نشاطك واظهر في أعلى نتايج البحث. ادفع بـ InstaPay أو فودافون كاش أو نقدي.',
])

@section('content')
<div class="max-w-2xl mx-auto" data-no-edge-swipe>

    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('directory.show', $business) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <span class="text-xs font-bold text-ink-500 truncate">{{ $business->name }}</span>
    </div>

    @if(session('flash'))
        <div class="card-light bg-mint-50 ring-1 ring-mint-500/30 p-3 mb-3 text-sm font-bold text-mint-700">
            {{ session('flash') }}
        </div>
    @endif

    {{-- ───── Hero ───── --}}
    <div class="card-light p-5 mb-4 text-center"
         style="background: linear-gradient(135deg, #F5BA12 0%, #FFD440 100%); color: #0B0B0C;">
        <div class="w-16 h-16 rounded-full bg-white grid place-items-center mx-auto mb-3 shadow-lg text-3xl">★</div>
        <h1 class="text-2xl font-black mb-1">شارة "موثّق"</h1>
        <p class="text-sm font-bold mb-3">ظهور أول · ثقة العملاء · تواصل أكتر</p>
        <div class="inline-flex items-baseline gap-1 bg-white/30 backdrop-blur rounded-full px-4 py-2 font-black">
            <span class="text-2xl">{{ $price }}</span>
            <span class="text-sm font-bold">جنيه</span>
            <span class="text-xs font-bold opacity-70">/ {{ $months }} شهر</span>
        </div>
    </div>

    {{-- ───── Status of current verification ───── --}}
    @if($business->hasPaidVerified())
        <div class="card-light p-4 mb-4 bg-mint-50 ring-1 ring-mint-500/30">
            <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-full bg-mint-500 text-white grid place-items-center text-lg shrink-0">★</span>
                <div>
                    <div class="text-sm font-extrabold text-mint-700">نشاطك موثّق دلوقتي</div>
                    <div class="text-xs text-ink-500 mt-0.5">
                        الشارة سارية حتى
                        <strong class="text-ink-950">{{ $business->verified_paid_until->translatedFormat('d M Y') }}</strong>
                        ({{ $business->verified_paid_until->diffForHumans() }})
                    </div>
                    <p class="text-[11px] text-ink-500 mt-2 leading-relaxed">تقدر تجدّد قبل ميعاد الانتهاء عشان الفترة الجديدة تتضاف.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ───── Pending submission ───── --}}
    @if($pending)
        <div class="card-light p-4 mb-4 bg-honey-100/40 ring-1 ring-honey-500/30">
            <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-full bg-honey-500 text-ink-950 grid place-items-center text-lg shrink-0">⌛</span>
                <div class="flex-1">
                    <div class="text-sm font-extrabold text-ink-950">طلبك قيد المراجعة</div>
                    <div class="text-xs text-ink-500 mt-0.5">
                        {{ $pending->methodLabel() }} ·
                        {{ $pending->created_at->diffForHumans() }} ·
                        هنرد عليك خلال 24 ساعة بإذن الله
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ───── Benefits ───── --}}
    <div class="card-light p-4 mb-4">
        <h3 class="text-sm font-extrabold text-ink-950 mb-3">إيه اللي بتاخده؟</h3>
        <ul class="space-y-2.5 text-sm">
            <li class="flex items-start gap-2">
                <span class="w-5 h-5 rounded-full bg-honey-500 text-white grid place-items-center text-xs shrink-0 font-black mt-0.5">★</span>
                <span><strong class="text-ink-950">شارة "موثّق" ذهبية</strong> على اسم نشاطك في كل مكان</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="w-5 h-5 rounded-full bg-honey-500 text-white grid place-items-center text-xs shrink-0 font-black mt-0.5">★</span>
                <span><strong class="text-ink-950">ظهور أول</strong> في نتايج البحث وقوائم التخصص</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="w-5 h-5 rounded-full bg-honey-500 text-white grid place-items-center text-xs shrink-0 font-black mt-0.5">★</span>
                <span><strong class="text-ink-950">إشعارات أسرع</strong> بالطلبات الجديدة في تخصصك</span>
            </li>
            <li class="flex items-start gap-2">
                <span class="w-5 h-5 rounded-full bg-honey-500 text-white grid place-items-center text-xs shrink-0 font-black mt-0.5">★</span>
                <span><strong class="text-ink-950">ثقة أعلى</strong> — العملاء بيتواصلوا مع الموثّقين أكتر بـ 3x</span>
            </li>
        </ul>
    </div>

    {{-- ───── Payment form ───── --}}
    @if(! $pending)
    <form method="POST" action="{{ route('verify.store', $business) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div class="card-light p-4">
            <h3 class="text-sm font-extrabold text-ink-950 mb-3">١. اختار طريقة الدفع</h3>

            <div class="space-y-2" data-method-group>
                {{-- InstaPay --}}
                <label class="block cursor-pointer">
                    <input type="radio" name="method" value="instapay" class="sr-only peer" required @checked(old('method') === 'instapay')>
                    <div class="border-2 border-ink-950/8 peer-checked:border-coral-500 peer-checked:bg-coral-50/50 rounded-2xl p-4 transition" data-method="instapay">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-coral-500 to-coral-700 grid place-items-center text-white font-black text-lg">IP</div>
                            <div class="flex-1">
                                <div class="text-sm font-extrabold text-ink-950">InstaPay</div>
                                <div class="text-[11px] text-ink-500">حوّل من أي بنك مصري · فوري</div>
                            </div>
                        </div>
                        <div class="hidden peer-checked:block mt-3 pt-3 border-t border-ink-950/8" data-instructions="instapay">
                            <div class="bg-cream-100 rounded-xl p-3 text-xs">
                                <div class="text-ink-500 mb-1">حوّل {{ $price }} جنيه على:</div>
                                <div class="flex items-center justify-between gap-2">
                                    <code class="text-sm font-extrabold text-coral-700 select-all" dir="ltr">{{ $instapay }}</code>
                                    <button type="button" data-copy="{{ $instapay }}" class="text-xs font-bold text-coral-600 hover:underline shrink-0">نسخ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </label>

                {{-- Vodafone Cash --}}
                <label class="block cursor-pointer">
                    <input type="radio" name="method" value="vodafone_cash" class="sr-only peer" required @checked(old('method') === 'vodafone_cash')>
                    <div class="border-2 border-ink-950/8 peer-checked:border-coral-500 peer-checked:bg-coral-50/50 rounded-2xl p-4 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blush-500 to-blush-600 grid place-items-center text-white font-black text-lg">VC</div>
                            <div class="flex-1">
                                <div class="text-sm font-extrabold text-ink-950">فودافون كاش</div>
                                <div class="text-[11px] text-ink-500">حوّل من أي محفظة فودافون كاش</div>
                            </div>
                        </div>
                        <div class="hidden peer-checked:block mt-3 pt-3 border-t border-ink-950/8">
                            <div class="bg-cream-100 rounded-xl p-3 text-xs">
                                <div class="text-ink-500 mb-1">حوّل {{ $price }} جنيه على:</div>
                                <div class="flex items-center justify-between gap-2">
                                    <code class="text-sm font-extrabold text-coral-700 font-mono select-all" dir="ltr">{{ $vodafone }}</code>
                                    <button type="button" data-copy="{{ $vodafone }}" class="text-xs font-bold text-coral-600 hover:underline shrink-0">نسخ</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </label>

                {{-- Cash --}}
                <label class="block cursor-pointer">
                    <input type="radio" name="method" value="cash" class="sr-only peer" required @checked(old('method') === 'cash')>
                    <div class="border-2 border-ink-950/8 peer-checked:border-coral-500 peer-checked:bg-coral-50/50 rounded-2xl p-4 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-mint-500 to-mint-600 grid place-items-center text-white text-2xl">💵</div>
                            <div class="flex-1">
                                <div class="text-sm font-extrabold text-ink-950">نقدي</div>
                                <div class="text-[11px] text-ink-500">ادفع كاش — نتفق على المكان عبر واتساب</div>
                            </div>
                        </div>
                        <div class="hidden peer-checked:block mt-3 pt-3 border-t border-ink-950/8">
                            <div class="bg-cream-100 rounded-xl p-3">
                                <p class="text-xs text-ink-600 mb-2 leading-relaxed">
                                    أرسل الطلب الآن، وكلّمنا على واتساب نتفق على المكان والميعاد.
                                </p>
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $cashWa) }}?text=عاوز أفعّل شارة موثّق لنشاط {{ urlencode($business->name) }} (نقدي)"
                                   target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 py-2 px-4 rounded-full text-white text-xs font-extrabold"
                                   style="background: linear-gradient(135deg, #25D366, #128C7E)">
                                    <x-icon name="whatsapp" class="w-4 h-4"/>
                                    كلّمنا على واتساب
                                </a>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
            @error('method') <p class="text-blush-500 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- Proof --}}
        <div class="card-light p-4 space-y-3" data-proof-section>
            <h3 class="text-sm font-extrabold text-ink-950">٢. أرسل إثبات التحويل</h3>
            <p class="text-[11px] text-ink-500 leading-relaxed">
                لـ InstaPay وفودافون كاش: ضع رقم العملية <strong>أو</strong> ارفع screenshot من شاشة التحويل.
                لـ نقدي: سيب ده فاضي، هنتأكد بعد الاجتماع.
            </p>

            <input type="text" name="transaction_id" maxlength="80"
                   value="{{ old('transaction_id') }}"
                   placeholder="رقم العملية / Transaction ID"
                   class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition font-mono" dir="ltr">

            <label class="flex items-center gap-3 bg-cream-100 rounded-2xl p-3 cursor-pointer hover:bg-cream-200 transition">
                <span class="w-10 h-10 rounded-xl bg-coral-500 text-white grid place-items-center text-xl shrink-0">📷</span>
                <span class="text-sm font-bold text-ink-950 flex-1" data-proof-name>ارفع screenshot من التحويل</span>
                <input type="file" name="proof" accept="image/jpeg,image/png,image/webp" class="hidden"
                       onchange="this.parentElement.querySelector('[data-proof-name]').textContent = this.files[0]?.name || 'ارفع screenshot من التحويل'">
            </label>
            @error('proof') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror
            @error('transaction_id') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

            <textarea name="note" rows="2" maxlength="300"
                      placeholder="ملاحظات إضافية (اختياري)"
                      class="w-full bg-cream-100 rounded-2xl px-4 py-2.5 text-sm text-ink-950 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('note') }}</textarea>
        </div>

        <button type="submit" class="btn-primary w-full justify-center !py-4 text-sm shadow-lg shadow-coral-500/30">
            📨 أرسل طلب التفعيل
        </button>
        <p class="text-[10px] text-ink-400 text-center leading-relaxed">
            هنراجع الطلب خلال 24 ساعة. لما يتم التفعيل، الشارة هتظهر فوراً على نشاطك في كل مكان.
        </p>
    </form>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-copy]');
    if (!btn) return;
    e.preventDefault();
    const text = btn.dataset.copy;
    navigator.clipboard?.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✓ اتنسخ';
        setTimeout(() => btn.textContent = orig, 1500);
    }).catch(() => {});
});
</script>
@endpush
@endsection
