@extends('layouts.app', [
    'title' => $side === 'hiring' ? 'أعلن عن وظيفة · بنهاوي' : 'ابحث عن شغل · بنهاوي',
    'description' => 'انشر وظيفة في بنها أو دور على شغل قربك — مجاناً.',
])

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- ─── Header ─── --}}
    <div class="flex items-center gap-2 mb-4 rise rise-1">
        <a href="{{ route('jobs.index', ['side' => $side]) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <div>
            <h1 class="text-xl font-extrabold text-ink-950 leading-tight">
                @if($side === 'hiring') أعلن عن وظيفة @else دور على شغل @endif
            </h1>
            <p class="text-[11px] text-ink-500 leading-tight">
                @if($side === 'hiring') املأ تفاصيل الوظيفة عشان توصل لأنسب ناس @else عرّف نفسك واتنشرت قدام أصحاب النشاطات @endif
            </p>
        </div>
    </div>

    {{-- ─── Side switcher ─── --}}
    <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-1 grid grid-cols-2 gap-1 mb-4 rise rise-1">
        <a href="{{ route('jobs.create', ['side' => 'hiring']) }}"
           class="text-center py-2 rounded-xl text-xs font-extrabold transition
                  {{ $side === 'hiring' ? 'bg-coral-500 text-white' : 'text-ink-500 hover:bg-cream-100' }}">
            أعلن عن وظيفة
        </a>
        <a href="{{ route('jobs.create', ['side' => 'seeking']) }}"
           class="text-center py-2 rounded-xl text-xs font-extrabold transition
                  {{ $side === 'seeking' ? 'bg-coral-500 text-white' : 'text-ink-500 hover:bg-cream-100' }}">
            بدور على شغل
        </a>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-2xl bg-blush-50 ring-1 ring-blush-500/30 p-3 text-[12px] text-blush-700">
            <ul class="space-y-1">
                @foreach($errors->all() as $err)
                    <li>· {{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('jobs.store') }}" class="card-light p-5 space-y-5">
        @csrf
        <input type="hidden" name="side" value="{{ $side }}">

        {{-- Title --}}
        <div>
            <label for="title" class="text-xs font-bold text-ink-500 mb-1.5 block">
                @if($side === 'hiring') اسم الوظيفة * @else عنوان البوست * @endif
            </label>
            <input type="text" name="title" id="title" required maxlength="120"
                   value="{{ old('title') }}"
                   placeholder="{{ $side === 'hiring' ? 'مثلاً: محتاج كاشير في مطعم — بنها' : 'مثلاً: بدور على شغل سواق توصيل — معايا موتوسيكل' }}"
                   class="w-full px-4 py-3 rounded-2xl bg-cream-100 border border-ink-950/8 focus:border-coral-500 focus:bg-white outline-none text-sm font-bold text-ink-950 placeholder:text-ink-400 placeholder:font-normal transition">
        </div>

        @if($side === 'hiring')
        {{-- Employer name --}}
        <div>
            <label for="employer_name" class="text-xs font-bold text-ink-500 mb-1.5 block">اسم النشاط / الشركة</label>
            <input type="text" name="employer_name" id="employer_name" maxlength="80"
                   value="{{ old('employer_name') }}"
                   placeholder="مثلاً: مطعم الأصيل — شارع فريد ندا"
                   class="w-full px-4 py-3 rounded-2xl bg-cream-100 border border-ink-950/8 focus:border-coral-500 focus:bg-white outline-none text-sm text-ink-950 placeholder:text-ink-400 transition">
        </div>
        @endif

        {{-- Employment type --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">
                @if($side === 'hiring') نوع الدوام * @else النوع الي بدور عليه * @endif
            </label>
            <div class="flex flex-wrap gap-2">
                @foreach($employmentTypes as $key => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="employment_type" value="{{ $key }}" class="peer sr-only" {{ old('employment_type', 'full_time') === $key ? 'checked' : '' }} required>
                        <span class="px-3.5 py-2 rounded-full text-xs font-bold bg-cream-100 border border-ink-950/8 peer-checked:bg-coral-500 peer-checked:text-white peer-checked:border-coral-500 transition inline-block">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Experience level --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">
                @if($side === 'hiring') مستوى الخبرة المطلوب @else مستوى خبرتك @endif
            </label>
            <div class="flex flex-wrap gap-2">
                @foreach($experienceLevels as $key => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="experience_level" value="{{ $key }}" class="peer sr-only" {{ old('experience_level', 'any') === $key ? 'checked' : '' }}>
                        <span class="px-3.5 py-2 rounded-full text-xs font-bold bg-cream-100 border border-ink-950/8 peer-checked:bg-mint-600 peer-checked:text-white peer-checked:border-mint-600 transition inline-block">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Salary range --}}
        <div>
            <label class="text-xs font-bold text-ink-500 mb-2 block">
                @if($side === 'hiring') المرتب الشهري (ج.م) — اختياري @else المرتب المتوقع (ج.م) — اختياري @endif
            </label>
            <div class="grid grid-cols-2 gap-2">
                <div class="relative">
                    <input type="number" name="salary_min" min="0" max="1000000"
                           value="{{ old('salary_min') }}"
                           placeholder="من"
                           inputmode="numeric"
                           class="w-full px-4 py-3 pe-12 rounded-2xl bg-cream-100 border border-ink-950/8 focus:border-coral-500 focus:bg-white outline-none text-sm font-bold text-ink-950 placeholder:text-ink-400 placeholder:font-normal transition">
                    <span class="absolute inset-y-0 end-3 grid place-items-center text-[11px] text-ink-400 font-bold">ج</span>
                </div>
                <div class="relative">
                    <input type="number" name="salary_max" min="0" max="1000000"
                           value="{{ old('salary_max') }}"
                           placeholder="إلى"
                           inputmode="numeric"
                           class="w-full px-4 py-3 pe-12 rounded-2xl bg-cream-100 border border-ink-950/8 focus:border-coral-500 focus:bg-white outline-none text-sm font-bold text-ink-950 placeholder:text-ink-400 placeholder:font-normal transition">
                    <span class="absolute inset-y-0 end-3 grid place-items-center text-[11px] text-ink-400 font-bold">ج</span>
                </div>
            </div>
            <p class="text-[10px] text-ink-400 mt-1.5">سيبها فاضية لو حسب الاتفاق.</p>
        </div>

        {{-- Description --}}
        <div>
            <label for="description" class="text-xs font-bold text-ink-500 mb-1.5 block">
                @if($side === 'hiring') وصف الوظيفة @else عرّف نفسك @endif
            </label>
            <textarea name="description" id="description" rows="4" maxlength="2000"
                      placeholder="{{ $side === 'hiring' ? 'مهام الوظيفة، مواعيد العمل، المكان، أي تفاصيل تساعد المتقدم...' : 'خبرتك، شغلك السابق، الي بتعرف تعمله...' }}"
                      class="w-full px-4 py-3 rounded-2xl bg-cream-100 border border-ink-950/8 focus:border-coral-500 focus:bg-white outline-none text-sm text-ink-950 placeholder:text-ink-400 transition resize-none">{{ old('description') }}</textarea>
        </div>

        {{-- Requirements / Skills --}}
        <div>
            <label for="requirements" class="text-xs font-bold text-ink-500 mb-1.5 block">
                @if($side === 'hiring') الشروط والمهارات المطلوبة @else مهاراتك @endif
            </label>
            <textarea name="requirements" id="requirements" rows="3" maxlength="1500"
                      placeholder="{{ $side === 'hiring' ? 'مثلاً: مؤهل متوسط، خبرة سنة، يعرف يتعامل مع كاش، رخصة قيادة...' : 'مثلاً: عربي وإنجليزي، Word/Excel، خبرة كاشير، أعرف أسوق...' }}"
                      class="w-full px-4 py-3 rounded-2xl bg-cream-100 border border-ink-950/8 focus:border-coral-500 focus:bg-white outline-none text-sm text-ink-950 placeholder:text-ink-400 transition resize-none">{{ old('requirements') }}</textarea>
        </div>

        {{-- Contact --}}
        <div class="rounded-2xl bg-mint-50 ring-1 ring-mint-500/20 p-4 space-y-3">
            <div class="flex items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4 text-mint-700">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                <span class="text-[12px] font-extrabold text-mint-800">طريقة التواصل</span>
            </div>

            <div>
                <label for="contact_phone" class="text-[11px] font-bold text-ink-500 mb-1 block">موبايل</label>
                <input type="tel" name="contact_phone" id="contact_phone" maxlength="20"
                       value="{{ old('contact_phone', Auth::user()->phone) }}"
                       placeholder="01XXXXXXXXX"
                       dir="ltr"
                       class="w-full px-4 py-2.5 rounded-xl bg-white border border-ink-950/8 focus:border-mint-600 outline-none text-sm font-bold text-ink-950 placeholder:text-ink-400 placeholder:font-normal transition">
            </div>

            <div>
                <label for="contact_whatsapp" class="text-[11px] font-bold text-ink-500 mb-1 block">واتساب (لو مختلف)</label>
                <input type="tel" name="contact_whatsapp" id="contact_whatsapp" maxlength="20"
                       value="{{ old('contact_whatsapp') }}"
                       placeholder="01XXXXXXXXX"
                       dir="ltr"
                       class="w-full px-4 py-2.5 rounded-xl bg-white border border-ink-950/8 focus:border-mint-600 outline-none text-sm font-bold text-ink-950 placeholder:text-ink-400 placeholder:font-normal transition">
            </div>

            <p class="text-[10px] text-ink-500 leading-relaxed">
                لازم رقم واحد على الأقل (موبايل أو واتساب) عشان الناس يقدروا يوصلولك.
            </p>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full py-3.5 rounded-2xl bg-coral-500 text-white text-sm font-extrabold hover:bg-coral-600 active:scale-[.99] transition inline-flex items-center justify-center gap-2">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            @if($side === 'hiring') انشر الوظيفة @else انشر بوستي @endif
        </button>
    </form>

    <p class="text-[11px] text-ink-400 text-center mt-3">
        النشر مجاني، والبوست بيفضل ٣٠ يوم. ممنوع المعلومات المضللة أو التواصل المباشر بفلوس.
    </p>
</div>
@endsection
