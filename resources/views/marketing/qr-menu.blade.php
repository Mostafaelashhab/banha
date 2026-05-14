@extends('layouts.app', [
    'title' => 'منيو QR لمطعمك أو كافيهك على بنهاوي · بنها',
    'description' => 'منيو رقمي جاهز للطبع QR Code، صور، أسعار، زر طلب على واتساب، عروض، وإحصائيات مشاهدات — في صفحة واحدة لمطعمك في بنها.',
    'keywords' => 'منيو QR بنها, QR menu, منيو رقمي, منيو مطعم, طلب واتساب, مطاعم بنها',
])

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ─── Headline ─── --}}
    <div class="mb-6 rise rise-1">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-coral-50 text-coral-600 text-[11px] font-extrabold mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-coral-500"></span>
            للمطاعم والكافيهات في بنها
        </div>
        <h1 class="text-3xl md:text-4xl font-black text-ink-950 leading-tight mb-3">
            منيو QR لمطعمك<br>أو كافيهك على بنهاوي
        </h1>
        <p class="text-sm md:text-base text-ink-500 leading-relaxed max-w-xl">
            ضيف لافتة QR على كل ترابيزة. الضيف بيمسح، يقرا المنيو من موبايله، ويبعت طلبه على واتساب — كله من غير ما تطبع منيو من جديد.
        </p>
    </div>

    {{-- ─── Mock preview ─── --}}
    <div class="rounded-3xl p-5 mb-8 bg-cream-100 ring-1 ring-ink-950/8 rise rise-2">
        <div class="flex items-center gap-3">
            <div class="w-24 h-24 rounded-2xl bg-white grid place-items-center shrink-0 ring-1 ring-ink-950/8">
                {{-- Lo-fi QR mock --}}
                <svg viewBox="0 0 100 100" class="w-20 h-20 text-ink-950">
                    <rect width="100" height="100" fill="white"/>
                    <g fill="currentColor">
                        <rect x="10" y="10" width="20" height="20"/>
                        <rect x="15" y="15" width="10" height="10" fill="white"/>
                        <rect x="70" y="10" width="20" height="20"/>
                        <rect x="75" y="15" width="10" height="10" fill="white"/>
                        <rect x="10" y="70" width="20" height="20"/>
                        <rect x="15" y="75" width="10" height="10" fill="white"/>
                        <rect x="40" y="10" width="5" height="5"/>
                        <rect x="50" y="15" width="5" height="5"/>
                        <rect x="45" y="25" width="10" height="5"/>
                        <rect x="40" y="40" width="20" height="5"/>
                        <rect x="65" y="40" width="5" height="10"/>
                        <rect x="40" y="55" width="5" height="5"/>
                        <rect x="55" y="55" width="10" height="10"/>
                        <rect x="40" y="70" width="5" height="20"/>
                        <rect x="50" y="75" width="20" height="5"/>
                        <rect x="75" y="55" width="10" height="5"/>
                        <rect x="85" y="65" width="5" height="20"/>
                    </g>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[10px] font-bold text-ink-500">معاينة</div>
                <div class="text-sm font-extrabold text-ink-950">امسح من الموبايل</div>
                <div class="text-[11px] text-ink-500 leading-snug mt-0.5">يفتح صفحة منيوك بسرعة، حتى لو الشبكة ضعيفة.</div>
                @if($sampleMenu)
                    <a href="{{ route('menu.public', $sampleMenu) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 mt-2 text-[12px] font-extrabold text-coral-600 hover:underline">
                        شوف نموذج فعلي
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3 h-3 rtl:rotate-180">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── What you get ─── --}}
    <section class="mb-8 rise rise-2">
        <h2 class="text-xl font-black text-ink-950 mb-3">المطعم بياخد إيه؟</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach([
                ['📷', 'QR Code جاهز للطبع'],
                ['📜', 'صفحة منيو رقمي على بنهاوي'],
                ['🗂️', 'أقسام وأصناف بأسعار وصور'],
                ['💬', 'زر طلب على واتساب (تلقائي)'],
                ['🛒', 'استقبال أوردرات مباشرة'],
                ['🎯', 'نشر عروض ضمن المنيو'],
                ['⚡', 'تحديث الأسعار في ثانية'],
                ['📊', 'إحصائيات المشاهدات والطلبات'],
            ] as [$em, $txt])
                <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 flex items-center gap-3">
                    <span class="text-xl">{{ $em }}</span>
                    <span class="text-sm font-bold text-ink-950">{{ $txt }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ─── How it works ─── --}}
    <section class="mb-8 rise rise-3">
        <h2 class="text-xl font-black text-ink-950 mb-3">إزاي بيشتغل؟</h2>
        <div class="space-y-2">
            @foreach([
                ['١', 'فعّل نشاطك على بنهاوي', 'دقيقتين، رقم موبايلك، وكود واتساب.'],
                ['٢', 'حط أقسام المنيو وصورهم وأسعارهم', 'تقدر تعدّل أي وقت بدون مطبعة.'],
                ['٣', 'طبع لافتة QR للترابيزات', 'منرسلهالك جاهزة للطبع.'],
                ['٤', 'الضيف يمسح ويطلب', 'الطلب بيوصلك على واتساب مباشرة.'],
            ] as [$num, $title, $desc])
                <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-4 flex items-start gap-3">
                    <span class="w-9 h-9 rounded-full bg-coral-50 text-coral-600 grid place-items-center text-base font-black shrink-0">{{ $num }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-extrabold text-ink-950">{{ $title }}</div>
                        <div class="text-[12px] text-ink-500 mt-0.5 leading-snug">{{ $desc }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ─── Pricing ─── --}}
    <section class="mb-8 rise rise-3">
        <h2 class="text-xl font-black text-ink-950 mb-3">الأسعار</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="bg-white rounded-3xl ring-1 ring-ink-950/8 p-5">
                <div class="text-[11px] font-bold text-ink-500 mb-1">تكلفة التفعيل (مرة واحدة)</div>
                <div class="text-3xl font-black text-coral-600" dir="ltr">٣٠٠–٥٠٠ ج</div>
                <p class="text-[12px] text-ink-500 mt-2 leading-relaxed">
                    تجهيز المنيو، تصميم الـ QR، ولافتة جاهزة للطبع.
                </p>
            </div>
            <div class="bg-white rounded-3xl ring-1 ring-coral-500 p-5 relative">
                <span class="absolute -top-2 start-5 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-coral-500 text-white text-[10px] font-extrabold">
                    الأكثر طلباً
                </span>
                <div class="text-[11px] font-bold text-ink-500 mb-1">الاشتراك الشهري</div>
                <div class="text-3xl font-black text-coral-600" dir="ltr">١٥٠–٣٠٠ ج</div>
                <p class="text-[12px] text-ink-500 mt-2 leading-relaxed">
                    استضافة المنيو، التحديثات، الطلبات على واتساب، والإحصائيات.
                </p>
            </div>
        </div>
    </section>

    {{-- ─── Final CTA ─── --}}
    <section class="rise rise-4 mb-6">
        <div class="rounded-3xl p-6 relative overflow-hidden text-center" style="background: #1F46DB;">
            <div class="absolute -top-16 -end-16 w-56 h-56 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-16 -start-16 w-56 h-56 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative">
                <h2 class="text-xl font-black text-white mb-2">جاهز تعمل منيو QR؟</h2>
                <p class="text-[13px] text-white/85 mb-4 max-w-sm mx-auto">
                    سيبلنا رقمك على واتساب وفي يومين منيوك على بنهاوي.
                </p>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 max-w-sm mx-auto">
                    <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl(config('services.banhawy.support_whatsapp', '01000000000')) }}?text={{ urlencode('عايز أعمل منيو QR لمطعمي على بنهاوي') }}"
                       target="_blank" rel="noopener"
                       class="flex-1 inline-flex items-center justify-center gap-1.5 px-5 py-3 rounded-full bg-white text-coral-600 text-sm font-extrabold hover:bg-cream-100 transition">
                        <x-icon name="whatsapp" class="w-4 h-4"/>
                        اعمل منيو QR لمطعمك
                    </a>
                    <a href="{{ Auth::check() ? route('directory.create') : route('signup') }}"
                       class="inline-flex items-center justify-center gap-1.5 px-5 py-3 rounded-full bg-white/15 text-white text-sm font-extrabold hover:bg-white/25 transition backdrop-blur ring-1 ring-white/20">
                        فعّل نشاطك أولاً
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
