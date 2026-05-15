@php
    use App\Models\Business as BizModel;
    use App\Support\Geo;

    $bizCatLabel = ($business->categoryMeta()['label'] ?? '') ?: 'نشاط';
    $bizCity     = Geo::businessCityLabel($business);
    $inCity      = Geo::inCity($business->zone);   // "في {city}" — honest geography

    // Category-aware SEO title.
    //  - food: leads with "منيو ورقم" (matches search intent for restaurants)
    //  - medical: leads with name + "العنوان والمواعيد ورقم التليفون"
    //  - other: name + city + brand
    $bizSeoTitle = match (true) {
        in_array($business->category, ['food'], true)
            => 'منيو ورقم '.$business->name.' '.$inCity.' | بنهاوي',
        in_array($business->category, ['medical'], true)
            => $business->name.' '.$inCity.' | العنوان والمواعيد ورقم التليفون',
        default
            => $business->name.' '.$inCity.' | بنهاوي',
    };

    // Category-aware SEO description.
    $bizSeoDesc = match (true) {
        in_array($business->category, ['food'], true)
            => 'شوف رقم '.$business->name.'، العنوان، المواعيد، المنيو، العروض، والاتجاهات على بنهاوي.',
        in_array($business->category, ['medical'], true)
            => 'اعرف عنوان '.$business->name.'، مواعيد العمل، رقم التليفون، والاتجاهات على بنهاوي.',
        default
            => 'اعرف رقم وعنوان ومواعيد '.$business->name.'، وشوف الاتجاهات وتواصل بسهولة على بنهاوي.',
    };
@endphp

@extends('layouts.app', [
    'title'       => $bizSeoTitle,
    'description' => $bizSeoDesc,
    'ogImage'     => $business->photo_url,
    'ogType'      => 'business.business',
    'canonical'   => route('directory.show', $business),
    'keywords'    => $business->name.', '.$bizCatLabel.', '.$bizCity.', بنها, دليل بنها, '.($business->sub_type ?? ''),
])

@push('json-ld')
@php
    // LocalBusiness schema — gets us into Google Map Pack & rich results.
    // Use the most specific Schema.org type we can defensibly claim.
    $schemaType = match ($business->category) {
        'food'    => 'Restaurant',
        'medical' => 'MedicalBusiness',
        'shops'   => 'Store',
        default   => 'LocalBusiness',
    };
    $ld = [
        '@context'       => 'https://schema.org',
        '@type'          => $schemaType,
        '@id'            => route('directory.show', $business),
        'name'           => $business->name,
        'url'            => route('directory.show', $business),
        'image'          => $business->photo_url ?: asset('icons/icon-512.png'),
        'priceRange'     => '$$',
        'address'        => array_filter([
            '@type'           => 'PostalAddress',
            'streetAddress'   => $business->address,
            'addressLocality' => $bizCity,
            'addressRegion'   => 'القليوبية',
            'addressCountry'  => 'EG',
        ]),
    ];
    if ($business->lat && $business->lng) {
        $ld['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float) $business->lat,
            'longitude' => (float) $business->lng,
        ];
    }
    $phones = array_values(array_filter([$business->phone, $business->hotline]));
    if ($phones) $ld['telephone'] = $phones[0];
    if ($business->whatsapp) $ld['contactPoint'] = [
        '@type'       => 'ContactPoint',
        'telephone'   => $business->whatsapp,
        'contactType' => 'customer support',
    ];
    if ($business->rating_avg && $business->ratings_count) {
        $ld['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => round((float) $business->rating_avg, 1),
            'reviewCount' => (int) $business->ratings_count,
            'bestRating'  => 5,
            'worstRating' => 1,
        ];
    }
    if ($business->is_24h) {
        $ld['openingHoursSpecification'] = [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
            'opens'     => '00:00',
            'closes'    => '23:59',
        ];
    }
    // Breadcrumb
    $crumbs = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'بنهاوي', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'الدليل', 'item' => route('directory.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $bizCatLabel, 'item' => route('directory.category', $business->category)],
            ['@type' => 'ListItem', 'position' => 4, 'name' => $business->name, 'item' => route('directory.show', $business)],
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode($crumbs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@php
    $cm = $business->categoryMeta();
    $sm = $business->subTypeMeta();
    $isActualOwner = auth()->check() && $business->owner_user_id && auth()->id() === $business->owner_user_id;
    $isAdminUser   = auth()->check() && auth()->user()->is_admin;
    $isOwner       = $isActualOwner || $isAdminUser; // edit/manage perms
@endphp

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Top action bar --}}
    <div class="flex items-center gap-2 mb-3">
        <a href="{{ route('directory.category', $business->category) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <span class="text-xs font-bold text-ink-500 truncate">{{ $cm['label'] }}</span>

        <button type="button" class="ms-auto w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950 hover:bg-cream-100 transition"
                data-share data-share-url="{{ route('directory.show', $business) }}"
                data-share-title="{{ $business->name }}"
                aria-label="شارك">
            <x-icon name="share" class="w-4 h-4"/>
        </button>
        @php
            $reportTo = config('services.banhawy.support_whatsapp', '01000000000');
            $reportMsg = "بلاغ عن بيانات غلط على بنهاوي\nالنشاط: {$business->name}\nرابط: ".route('directory.show', $business)."\nالغلط: ";
        @endphp
        <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($reportTo) }}?text={{ urlencode($reportMsg) }}"
           target="_blank" rel="noopener"
           class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-500 hover:bg-blush-50 hover:text-blush-600 transition"
           title="بلّغ عن بيانات غلط" aria-label="بلّغ عن بيانات غلط">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                <line x1="4" y1="22" x2="4" y2="15"/>
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
            </svg>
        </a>

        @if($isOwner)
            <a href="{{ route('menu.manage', $business) }}" class="w-9 h-9 rounded-full bg-honey-100 text-honey-700 grid place-items-center hover:bg-honey-500 hover:text-ink-950 transition" title="منيو">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <rect x="6" y="3" width="12" height="18" rx="2"/>
                    <line x1="9" y1="8" x2="15" y2="8"/>
                    <line x1="9" y1="12" x2="15" y2="12"/>
                    <line x1="9" y1="16" x2="13" y2="16"/>
                </svg>
            </a>
            @if($business->has_menu && $business->whatsapp)
                @php $pendingOrders = $business->orders()->where('status', 'pending')->count(); @endphp
                <a href="{{ route('order.owner.index', $business) }}" class="relative w-9 h-9 rounded-full bg-coral-100 text-coral-600 grid place-items-center hover:bg-coral-500 hover:text-white transition" title="الطلبات">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>
                    </svg>
                    @if($pendingOrders > 0)
                        <span class="absolute -top-1 -end-1 min-w-[18px] h-[18px] px-1 rounded-full bg-blush-500 text-white text-[10px] font-black grid place-items-center">{{ $pendingOrders }}</span>
                    @endif
                </a>
            @endif
            <a href="{{ route('directory.stats', $business) }}" class="w-9 h-9 rounded-full bg-mint-100 text-mint-700 grid place-items-center hover:bg-mint-500 hover:text-white transition" title="إحصائيات">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6"  y1="20" x2="6"  y2="14"/><line x1="3"  y1="20" x2="21" y2="20"/>
                </svg>
            </a>
            <a href="{{ route('directory.edit', $business) }}" class="w-9 h-9 rounded-full bg-coral-100 text-coral-700 grid place-items-center hover:bg-coral-500 hover:text-white transition" title="تعديل">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z"/>
                </svg>
            </a>
        @endif
    </div>

    {{-- ───── Admin quick controls (verify / promote / active toggle) ─── --}}
    @if($isAdminUser)
        @php
            $promotedActive = $business->promoted_until && $business->promoted_until->isFuture();
        @endphp
        <div class="card-light p-4 mb-3 bg-coral-50 ring-1 ring-coral-500/15">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-7 h-7 rounded-lg bg-coral-500 text-white grid place-items-center text-xs font-black">★</span>
                <h3 class="text-sm font-extrabold text-ink-950">لوحة الأدمن السريعة</h3>
                <a href="{{ route('admin.businesses') }}" class="ms-auto text-[10px] font-bold text-ink-500 hover:text-coral-600">للوحة الكاملة ←</a>
            </div>

            <div class="grid grid-cols-2 gap-2 mb-2">
                {{-- Verify toggle --}}
                <form method="POST" action="{{ route('admin.businesses.verify', $business) }}">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-extrabold transition
                                   {{ $business->is_verified ? 'bg-mint-500 text-white hover:bg-mint-600' : 'bg-white text-ink-950 ring-1 ring-ink-950/10 hover:ring-mint-500/50' }}">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                            <path d="M12 2 4 6v6c0 5 3.4 9.6 8 11 4.6-1.4 8-6 8-11V6Zm-1 13-3.5-3.5L9 10l2 2 5-5 1.5 1.5Z"/>
                        </svg>
                        {{ $business->is_verified ? 'موثّق · شيل التوثيق' : 'وثّق النشاط' }}
                    </button>
                </form>

                {{-- Active toggle --}}
                <form method="POST" action="{{ route('admin.businesses.toggle', $business) }}">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-extrabold transition
                                   {{ $business->is_active ? 'bg-white text-ink-950 ring-1 ring-ink-950/10 hover:ring-blush-500/50' : 'bg-blush-500 text-white hover:bg-blush-600' }}">
                        @if($business->is_active)
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-4 h-4"><circle cx="12" cy="12" r="9"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                            اقفل النشاط
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" class="w-4 h-4"><polyline points="20 6 9 17 4 12"/></svg>
                            افتح النشاط
                        @endif
                    </button>
                </form>
            </div>

            {{-- Promotion --}}
            <div class="bg-white rounded-xl p-3 ring-1 ring-honey-500/20">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold text-ink-950 inline-flex items-center gap-1.5">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-honey-500">
                            <polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/>
                        </svg>
                        ترويج (Sponsored)
                    </span>
                    @if($promotedActive)
                        <span class="text-[10px] font-bold text-honey-700 bg-honey-100 px-2 py-0.5 rounded-full">
                            مفعّل لحد {{ $business->promoted_until->translatedFormat('d M') }}
                        </span>
                    @else
                        <span class="text-[10px] font-bold text-ink-400">غير مروَّج</span>
                    @endif
                </div>

                <div class="grid grid-cols-4 gap-1.5">
                    @foreach([7, 14, 30, 90] as $days)
                        <form method="POST" action="{{ route('admin.businesses.promote', $business) }}">
                            @csrf
                            <input type="hidden" name="days" value="{{ $days }}">
                            <button type="submit"
                                    class="w-full px-2 py-2 rounded-lg bg-honey-100 text-honey-700 text-[11px] font-extrabold hover:bg-honey-500 hover:text-ink-950 transition">
                                {{ $days }} يوم
                            </button>
                        </form>
                    @endforeach
                </div>

                @if($promotedActive)
                    <form method="POST" action="{{ route('admin.businesses.promote', $business) }}"
                          data-confirm="إلغاء الترويج؟" data-confirm-action="ألغى" data-confirm-tone="danger" class="mt-1.5">
                        @csrf
                        <input type="hidden" name="days" value="0">
                        <button type="submit" class="w-full px-2 py-2 rounded-lg bg-blush-100 text-blush-600 text-[11px] font-extrabold hover:bg-blush-500 hover:text-white transition">
                            ألغي الترويج
                        </button>
                    </form>
                @endif
            </div>

            {{-- ─── Card photo manager ─── --}}
            <div class="bg-white rounded-xl p-3 ring-1 ring-coral-500/15 mt-2">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-extrabold text-ink-950 inline-flex items-center gap-1.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5 text-coral-500">
                            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                        </svg>
                        كارد الـ home (شكله للناس)
                    </span>
                    @php
                        $coverImage = $business->photos->first()->url ?? $business->photo_url ?? null;
                    @endphp
                    @if($coverImage)
                        <span class="text-[10px] font-bold text-mint-700 bg-mint-100 px-2 py-0.5 rounded-full">صورة معيّنة</span>
                    @else
                        <span class="text-[10px] font-bold text-ink-400">فولباك (شوكة الكاتيجوري)</span>
                    @endif
                </div>

                {{-- Live preview — actual card mock at home-page aspect ratio --}}
                <div class="text-[10px] font-bold text-ink-500 mb-1.5">معاينة:</div>
                <div class="relative rounded-2xl overflow-hidden aspect-[4/3] mb-3 ring-1 ring-ink-950/10 shadow-sm bg-cream-100">
                    {{-- gradient fallback (always rendered behind) --}}
                    <div class="absolute inset-0 grid place-items-center"
                         style="background: linear-gradient(135deg, {{ $cm['color'] ?? '#2D5BFF' }}, {{ $cm['color'] ?? '#2D5BFF' }}aa);">
                        <x-icon :name="$cm['icon'] ?? 'bag'" class="w-16 h-16 text-white/80"/>
                    </div>
                    {{-- actual image on top, if any --}}
                    @if($coverImage)
                        <img src="{{ $coverImage }}" alt="" class="absolute inset-0 w-full h-full object-cover"
                             style="object-position: center 30%;">
                    @endif
                    {{-- realistic overlay matching the real card --}}
                    <div class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black/70 to-transparent">
                        <div class="text-white font-extrabold text-sm truncate drop-shadow">
                            {{ $business->name }}
                            @if($business->is_verified)
                                <span class="inline-block text-mint-300 text-xs">✓</span>
                            @endif
                        </div>
                        <div class="text-white/85 text-[10px] truncate">{{ $cm['label'] ?? '' }}</div>
                    </div>
                </div>
                <p class="text-[11px] text-ink-500 mb-3 leading-snug">
                    ⬆ ده الكارد اللي بيظهر في كاروسيل الـ home (مميّزة الأسبوع + الأكتر تقييم).
                </p>

                {{-- Upload new photo --}}
                <form method="POST" action="{{ route('admin.businesses.photo', $business) }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <label class="flex items-center gap-2 bg-cream-100 rounded-lg p-2 cursor-pointer border border-ink-950/8 hover:border-coral-500/40 transition">
                        <span class="w-8 h-8 rounded-lg bg-coral-500 text-white grid place-items-center text-xs font-black shrink-0">+</span>
                        <span class="text-xs font-bold text-ink-950 flex-1" data-photo-name>ارفع صورة جديدة (JPG/PNG/WEBP · حتى 3 ميجا)</span>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" required
                               onchange="this.parentElement.querySelector('[data-photo-name]').textContent = this.files[0]?.name || 'ارفع صورة'; this.form.requestSubmit()">
                    </label>
                </form>

                {{-- Pick from gallery (if any) --}}
                @if($business->photos->isNotEmpty())
                    <div class="mt-3">
                        <div class="text-[10px] font-bold text-ink-500 mb-1.5">أو اختار من الجاليري:</div>
                        <div class="grid grid-cols-5 gap-1.5">
                            @foreach($business->photos->take(10) as $p)
                                <form method="POST" action="{{ route('admin.businesses.photo', $business) }}">
                                    @csrf
                                    <input type="hidden" name="gallery_url" value="{{ $p->url }}">
                                    <button type="submit" class="block w-full aspect-square rounded-lg overflow-hidden ring-1 transition
                                                                 {{ $business->photo_url === $p->url ? 'ring-coral-500 ring-2' : 'ring-ink-950/8 hover:ring-coral-500/50' }}">
                                        <img src="{{ $p->url }}" alt="" class="w-full h-full object-cover">
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Clear → revert to category fallback --}}
                @if($business->photo_url)
                    <form method="POST" action="{{ route('admin.businesses.photo', $business) }}"
                          data-confirm="ارجع الصورة لـ الفولباك؟" data-confirm-action="ارجع" class="mt-2">
                        @csrf
                        <input type="hidden" name="clear" value="1">
                        <button type="submit" class="w-full px-3 py-2 rounded-lg bg-blush-100 text-blush-600 text-[11px] font-extrabold hover:bg-blush-500 hover:text-white transition">
                            🗑️ ارجع الفولباك
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- Hero: branded Banhawy cover when no user photo --}}
    @php
        $heroPhoto = $business->photo_url ?: null;
        $heroInitial = mb_substr(trim($business->name ?: '?'), 0, 1);
        $heroColor   = $cm['color'] ?? '#2D5BFF';
    @endphp
    <div class="relative -mx-4 mb-4 overflow-hidden aspect-[16/10]"
         style="background: linear-gradient(135deg, {{ $heroColor }}, {{ $heroColor }}cc 60%, {{ $heroColor }}88);">
        {{-- Branded fallback (visible underneath the user image) --}}
        <svg class="absolute inset-0 w-full h-full opacity-15" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
            <defs>
                <pattern id="hero-dots-{{ $business->id }}" x="0" y="0" width="28" height="28" patternUnits="userSpaceOnUse">
                    <circle cx="3" cy="3" r="1.8" fill="white"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#hero-dots-{{ $business->id }})"/>
        </svg>
        <div class="absolute inset-0 grid place-items-center">
            <span class="text-white font-black text-[120px] leading-none opacity-95 select-none drop-shadow-lg">{{ $heroInitial }}</span>
        </div>
        @unless($heroPhoto)
            <span class="absolute top-3 end-3 inline-flex items-center gap-1 bg-white/15 backdrop-blur-sm rounded-full px-2.5 py-1 text-white text-[10px] font-extrabold z-30">
                <span class="w-4 h-4 rounded-md bg-white text-[10px] grid place-items-center font-black" style="color: {{ $heroColor }};">ب</span>
                بنهاوي
            </span>
        @endunless

        {{-- User-uploaded photo, if any --}}
        @if($heroPhoto)
            <img src="{{ $heroPhoto }}" alt="{{ $business->name }}" loading="eager"
                 class="absolute inset-0 w-full h-full object-cover z-10"
                 onerror="this.style.display='none'">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent z-20"></div>

        <div class="absolute top-3 start-3 flex flex-col gap-1.5 z-30">
            @if($business->isPromoted())
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-honey-500 text-ink-950 w-fit">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                    مُروَّج
                </span>
            @endif
            @if($business->is_verified)
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-mint-500 text-white w-fit">
                    <x-icon name="check" class="w-3 h-3"/> موثّق
                </span>
            @endif
        </div>

        <div class="absolute bottom-0 inset-x-0 p-4 z-30">
            <h1 class="text-2xl md:text-3xl font-black text-white leading-tight drop-shadow-lg">{{ $business->name }}</h1>
            <div class="flex items-center gap-2 mt-1.5 text-white/90 text-sm">
                <span>{{ $business->displayType() }}</span>
                @if($business->zone)
                    <span class="text-white/60">·</span>
                    <span class="inline-flex items-center gap-1"><x-icon name="map-pin" class="w-3 h-3"/> {{ $business->zone->name }}</span>
                @endif
                @if($business->ratings_count > 0)
                    <span class="text-white/60">·</span>
                    <span class="inline-flex items-center gap-0.5">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3 text-honey-400"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                        <span class="font-bold">{{ $business->rating_avg }}</span>
                        <span class="text-white/70 text-xs">({{ $business->ratings_count }})</span>
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Order CTA (food/shops with menu + WhatsApp) — replaces booking for these categories --}}
    @if($business->supportsOrdering() && $business->has_menu)
        <a href="{{ route('menu.public', $business) }}" class="block mb-3 p-4 rounded-2xl bg-coral-500 text-white text-center hover:scale-[1.01] transition shadow-lg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 mx-auto">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>
            </svg>
            <div class="text-base font-extrabold mt-2">{{ $business->category === 'food' ? 'اطلب أوردر' : 'اطلب منتجاتك' }}</div>
            <div class="text-xs text-white/85 mt-0.5">اختار اللي عاوزه — هنبعت الأوردر للنشاط مباشرة</div>
        </a>
    @endif

    {{-- Delivery info card — shows BEFORE the menu so the user knows if they're covered ──── --}}
    @if($business->offersDelivery())
        @php
            $deliveryFees   = (array) ($business->delivery_fees ?? []);
            $deliveryAreaCt = count($deliveryFees);
            $feeValues      = array_map('floatval', array_values($deliveryFees));
            $feeMin         = $feeValues ? min($feeValues) : 0;
            $feeMax         = $feeValues ? max($feeValues) : 0;
            $minOrder       = (int) ($business->delivery_min_order ?? 0);
            // If the visitor is signed in and has a preferred area, show "to your area"
            $myArea = null;
            $myFee  = null;
            if (auth()->check() && auth()->user()->default_area_id) {
                $myFee = $business->deliveryFeeFor((int) auth()->user()->default_area_id);
                if ($myFee !== null) {
                    $myArea = \App\Models\Area::find(auth()->user()->default_area_id);
                }
            }
            $fmt = fn ($n) => $n == (int) $n ? (string) (int) $n : number_format($n, 2, '.', '');
        @endphp
        <div class="card-light p-4 mb-3">
            <div class="flex items-center gap-3 mb-2">
                <span class="w-9 h-9 rounded-xl bg-coral-50 text-coral-600 grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <rect x="1" y="3" width="15" height="13" rx="1"/>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-extrabold text-ink-950">بيوصّل دليفري</div>
                    <div class="text-[11px] text-ink-500">
                        لـ {{ $deliveryAreaCt }} منطقة في
                        @php
                            $parents = \App\Models\Area::whereIn('id', array_keys($deliveryFees))->distinct()->pluck('parent')->all();
                        @endphp
                        {{ implode(' · ', $parents) ?: 'بنها' }}
                    </div>
                </div>
            </div>

            @if($myArea && $myFee !== null)
                {{-- We know exactly what this user pays --}}
                <div class="bg-mint-50 ring-1 ring-mint-500/20 rounded-2xl p-3 flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full bg-mint-500 text-white grid place-items-center shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3.5 h-3.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] text-ink-500">لمنطقتك ({{ $myArea->name }})</div>
                        <div class="text-sm font-extrabold text-mint-700">
                            شحن:
                            @if($myFee == 0)
                                <span class="text-mint-700">مجاناً 🎉</span>
                            @else
                                <span dir="ltr">{{ $fmt($myFee) }} ج</span>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-cream-100/70 rounded-xl p-2.5">
                        <div class="text-[10px] font-bold text-ink-500">سعر الشحن</div>
                        <div class="text-sm font-extrabold text-ink-950 mt-0.5" dir="ltr">
                            @if($feeMin == 0 && $feeMax == 0)
                                مجاناً
                            @elseif($feeMin === $feeMax)
                                {{ $fmt($feeMin) }} ج
                            @else
                                {{ $fmt($feeMin) }}–{{ $fmt($feeMax) }} ج
                            @endif
                        </div>
                    </div>
                    <div class="bg-cream-100/70 rounded-xl p-2.5">
                        <div class="text-[10px] font-bold text-ink-500">الحد الأدنى</div>
                        <div class="text-sm font-extrabold text-ink-950 mt-0.5" dir="ltr">
                            @if($minOrder > 0)
                                {{ $minOrder }} ج
                            @else
                                مفيش حد أدنى
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if($business->has_menu)
                <a href="{{ route('menu.public', $business) }}"
                   class="inline-flex items-center gap-1 text-[11px] font-extrabold text-coral-600 hover:underline mt-2">
                    شوف كل أسعار الشحن في المنيو
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3 h-3 rtl:rotate-180">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
            @endif
        </div>
    @endif

    {{-- Booking CTA (only for non-food categories where owner enabled bookings) --}}
    @if($business->booking_enabled && $business->bookingApplicable())
        <a href="{{ route('booking.show', $business) }}" class="block mb-3 p-4 rounded-2xl bg-mint-500 text-white text-center hover:scale-[1.01] transition shadow-lg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 mx-auto">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
                <polyline points="9 16 11 18 15 14"/>
            </svg>
            <div class="text-base font-extrabold mt-2">احجز موعد إلكتروني</div>
            <div class="text-xs text-white/85 mt-0.5">اختار اليوم والساعة المناسبة — تأكيد عبر واتساب</div>
        </a>
    @endif

    {{-- Menu / Services CTA (adaptive label per business category) --}}
    @if($business->has_menu)
        @php $L = \App\Models\Business::menuLabels($business->category); @endphp
        <a href="{{ route('menu.public', $business) }}" class="block mb-3 p-4 rounded-2xl bg-coral-500 text-white text-center hover:scale-[1.01] transition shadow-lg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 mx-auto">
                <rect x="6" y="3" width="12" height="18" rx="2"/>
                <line x1="9" y1="8" x2="15" y2="8"/>
                <line x1="9" y1="12" x2="15" y2="12"/>
                <line x1="9" y1="16" x2="13" y2="16"/>
            </svg>
            <div class="text-base font-extrabold mt-2">{{ $L['cta_show'] }}</div>
            <div class="text-xs text-white/80 mt-0.5">{{ $business->menuCategories()->count() }} {{ $L['category_label'] }} · {{ $business->menuItems()->where('is_available', true)->count() }} {{ $L['item_label'] }}</div>
        </a>
    @endif

    {{-- Quick contact CTAs --}}
    @php
        $callNumber  = $business->phone ?: $business->hotline;
        $callLabel   = $business->phone ? 'اتصل' : 'الخط الساخن';
        $hasDir      = $business->lat && $business->lng;
        $actions     = [];
        if ($callNumber)         $actions[] = 'call';
        if ($business->whatsapp) $actions[] = 'whatsapp';
        if ($hasDir)             $actions[] = 'directions';
        $cols        = count($actions);
        // Tailwind needs literal class names so JIT picks them up.
        $gridCols    = ['', 'grid-cols-1', 'grid-cols-2', 'grid-cols-3'][$cols] ?? 'grid-cols-3';
    @endphp
    @if($cols > 0)
        <div class="grid {{ $gridCols }} gap-2 mb-3">
            @if($callNumber)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $callNumber) }}" data-track-click="phone" data-business="{{ $business->id }}" class="btn-dark justify-center !py-3.5 text-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    {{ $callLabel }}
                </a>
            @endif
            @if($business->whatsapp)
                <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}" target="_blank"
                   data-track-click="whatsapp" data-business="{{ $business->id }}"
                   class="inline-flex items-center justify-center gap-2 py-3.5 rounded-full font-bold text-white text-sm transition hover:scale-[1.02] bg-mint-600 hover:bg-mint-500">
                    <x-icon name="whatsapp" class="w-4 h-4"/> واتساب
                </a>
            @endif
            @if($hasDir)
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $business->lat }},{{ $business->lng }}"
                   target="_blank" rel="noopener"
                   data-track-click="directions" data-business="{{ $business->id }}"
                   class="inline-flex items-center justify-center gap-2 py-3.5 rounded-full font-bold text-coral-600 text-sm transition bg-coral-50 hover:bg-coral-100">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="M21.71 11.29 12.71 2.29a1 1 0 0 0-1.42 0l-9 9a1 1 0 0 0 0 1.42l9 9a1 1 0 0 0 1.42 0l9-9a1 1 0 0 0 0-1.42z"/>
                        <polyline points="9 12 11 14 15 10"/>
                    </svg>
                    الاتجاهات
                </a>
            @endif
        </div>
    @endif

    {{-- About --}}
    @if($business->description)
        <div class="card-light p-4 mb-3">
            <p class="text-ink-950 text-sm leading-relaxed whitespace-pre-line">{{ $business->description }}</p>
        </div>
    @endif

    {{-- Type-specific extras (hotel stars, cuisine, doctor specialty, etc.) --}}
    @php
        $extras    = (array) ($business->extra ?? []);
        $extraDefs = \App\Models\Business::extraFieldsFor($business->sub_type);
        $visible   = collect($extraDefs)
            ->filter(fn ($def, $key) => array_key_exists($key, $extras) && $extras[$key] !== null && $extras[$key] !== '')
            ->all();
    @endphp
    @if(! empty($visible))
        <div class="card-light p-4 mb-3">
            <h3 class="text-sm font-extrabold text-ink-950 mb-3 inline-flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-coral-100 text-coral-600 grid place-items-center shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/>
                    </svg>
                </span>
                تفاصيل {{ $cm['label'] }}
            </h3>

            <dl class="grid grid-cols-2 gap-3">
                @foreach($visible as $key => $def)
                    @php $v = $extras[$key]; @endphp
                    <div class="bg-cream-100/70 rounded-xl p-3 {{ $def['type'] === 'checkbox' ? 'col-span-1' : '' }}">
                        <dt class="text-[10px] font-bold text-ink-500">{{ $def['label'] }}</dt>
                        <dd class="text-sm font-extrabold text-ink-950 mt-0.5">
                            @if($def['type'] === 'checkbox')
                                @if($v)
                                    <span class="inline-flex items-center gap-1 text-mint-700">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        متوفر
                                    </span>
                                @else
                                    <span class="text-ink-400">مش متوفر</span>
                                @endif
                            @elseif($def['type'] === 'select' && isset($def['options'][$v]))
                                {{ $def['options'][$v] }}
                            @elseif($key === 'website')
                                <a href="{{ str_starts_with($v, 'http') ? $v : 'https://'.$v }}" target="_blank" rel="noopener"
                                   class="text-coral-600 hover:underline break-all" dir="ltr">
                                    {{ \Illuminate\Support\Str::limit(preg_replace('#^https?://#', '', $v), 32) }}
                                </a>
                            @else
                                {{ $v }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endif

    {{-- Info rows --}}
    @php
        $openNow      = $business->isOpenNow();             // null when no schedule
        $statusLabel  = $business->openStatusLabel();        // "مفتوح · 9ص-11م" / "مغلق · يفتح 9ص"
        $hasHoursInfo = $business->hours_schedule || $business->hours || $business->is_24h;
    @endphp
    @if($business->address || $hasHoursInfo || $business->phone || $business->hotline)
        <div class="card-light p-4 mb-3 space-y-3">
            @if($business->address)
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-xl pill-coral grid place-items-center shrink-0">
                        <x-icon name="map-pin" class="w-4 h-4"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] text-ink-500">العنوان</div>
                        <div class="text-sm font-bold text-ink-950">{{ $business->address }}</div>
                    </div>
                </div>
            @endif

            @if($hasHoursInfo)
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-xl {{ $openNow === true || $business->is_24h ? 'pill-mint' : ($openNow === false ? 'pill-blush' : 'pill-honey') }} grid place-items-center shrink-0">
                        <x-icon name="bell" class="w-4 h-4"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] text-ink-500 inline-flex items-center gap-1.5">
                            المواعيد
                            @if($openNow === true)
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-mint-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-mint-500"></span>
                                    مفتوح
                                </span>
                            @elseif($openNow === false)
                                <span class="text-[10px] font-extrabold text-blush-500">مغلق</span>
                            @endif
                        </div>
                        <div class="text-sm font-bold text-ink-950">
                            @if($business->is_24h)
                                <span class="text-mint-700">٢٤ ساعة · مفتوح دلوقتي</span>
                            @elseif($statusLabel)
                                {{ $statusLabel }}
                            @else
                                {{ $business->hours }}
                            @endif
                        </div>

                        @if($business->hours_schedule)
                            <details class="mt-2">
                                <summary class="text-[11px] font-bold text-coral-600 cursor-pointer list-none inline-flex items-center gap-1">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="w-3 h-3 transition group-open:rotate-180">
                                        <polyline points="6 9 12 15 18 9"/>
                                    </svg>
                                    شوف الجدول الأسبوعي
                                </summary>
                                <div class="mt-2 space-y-1 text-[11px]">
                                    @php $todayKey = ['sun','mon','tue','wed','thu','fri','sat'][(int) now('Africa/Cairo')->format('w')]; @endphp
                                    @foreach(\App\Models\Business::WEEKDAYS as $key => $label)
                                        @php $shift = $business->hours_schedule[$key] ?? null; @endphp
                                        <div class="flex items-center gap-2 {{ $key === $todayKey ? 'font-extrabold text-ink-950' : 'text-ink-500' }}">
                                            <span class="w-12">{{ $label }}</span>
                                            <span dir="ltr" class="font-mono">
                                                {{ $shift ?: 'مغلق' }}
                                            </span>
                                            @if($key === $todayKey)
                                                <span class="text-[9px] text-coral-600">· النهارده</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </div>
                </div>
            @endif

            @if($business->phone)
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-xl pill-blush grid place-items-center shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] text-ink-500">رقم التليفون</div>
                        <div class="text-sm font-bold text-ink-950" dir="ltr">{{ $business->phone }}</div>
                    </div>
                </div>
            @endif

            @if($business->hotline)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $business->hotline) }}" data-track-click="phone" data-business="{{ $business->id }}"
                   class="flex items-start gap-3 hover:bg-cream-100 transition rounded-xl -mx-2 px-2 py-1">
                    <span class="w-9 h-9 rounded-xl pill-coral grid place-items-center shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] text-ink-500">الخط الساخن</div>
                        <div class="text-sm font-bold text-ink-950" dir="ltr">{{ $business->hotline }}</div>
                    </div>
                </a>
            @endif
        </div>
    @endif

    {{-- Location map (read-only) --}}
    @if($business->lat && $business->lng)
        @push('head')
            <link rel="preconnect" href="https://unpkg.com" crossorigin>
            <link rel="preconnect" href="https://tile.openstreetmap.org" crossorigin>
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
            <link rel="preload" as="script" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin>
            <style>
                .biz-map { height: 240px; border-radius: 18px; background: #FFF7F1; z-index: 0; overflow: hidden; }
                .biz-map .leaflet-tile { filter: saturate(.75) brightness(1.04) contrast(.95); }
                .biz-map-card .leaflet-control-attribution { font-size: 9px; opacity: .6; }
                .biz-map-info {
                    position: absolute;
                    top: 8px; inset-inline-start: 8px;
                    z-index: 500;
                    background: #fff;
                    border-radius: 12px;
                    padding: 6px 10px;
                    font-size: 11px;
                    font-weight: 800;
                    color: #0B0B0C;
                    box-shadow: 0 6px 18px -4px rgba(11,11,12,.18);
                    display: inline-flex; align-items: center; gap: 6px;
                    max-width: calc(100% - 16px);
                }
                .biz-map-info[hidden] { display: none; }
                .biz-map-info .dot { width: 8px; height: 8px; border-radius: 999px; background: #FF7A4D; }
                .biz-map-info.is-error { color: #DC2626; }
                .biz-user-pin {
                    width: 18px; height: 18px;
                    border-radius: 50%;
                    background: #2D5BFF;
                    border: 3px solid #fff;
                    box-shadow: 0 0 0 2px rgba(45,91,255,.25), 0 4px 10px -2px rgba(45,91,255,.5);
                }
                @keyframes biz-route-spin { to { transform: rotate(360deg); } }
                .biz-route-spinner {
                    width: 14px; height: 14px;
                    border: 2px solid currentColor;
                    border-inline-end-color: transparent;
                    border-radius: 999px;
                    animation: biz-route-spin .7s linear infinite;
                    display: inline-block;
                }
            </style>
        @endpush

        <div class="card-light p-4 mb-3 biz-map-card">
            <h3 class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-2 mb-3">
                <span class="w-7 h-7 rounded-lg pill-coral grid place-items-center shrink-0">
                    <x-icon name="map-pin" class="w-4 h-4"/>
                </span>
                المكان على الخريطة
            </h3>

            <div id="biz-map-{{ $business->id }}" class="biz-map mb-3 relative"
                 data-biz-map
                 data-lat="{{ $business->lat }}"
                 data-lng="{{ $business->lng }}"
                 data-color="{{ $cm['color'] ?? '#2D5BFF' }}"
                 data-name="{{ $business->name }}">
                <div class="biz-map-info" data-route-info hidden></div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button type="button"
                        data-route-trigger="biz-map-{{ $business->id }}"
                        class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-coral-500 text-white text-xs font-extrabold hover:bg-coral-600 transition disabled:opacity-60">
                    <span data-route-icon class="inline-flex">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                            <polygon points="3 11 22 2 13 21 11 13 3 11"/>
                        </svg>
                    </span>
                    <span data-route-label>شوف الطريق</span>
                </button>
                <a href="{{ route('directory.map') }}?focus={{ $business->id }}&lat={{ $business->lat }}&lng={{ $business->lng }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white text-ink-950 ring-1 ring-ink-950/10 text-xs font-extrabold hover:ring-coral-500/50 transition">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    افتح خريطة بنها
                </a>
            </div>
        </div>

        @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
        (function () {
            const instances = new Map(); // mapId → { map, lat, lng, color, info, layers }

            function fmtKm(meters) {
                if (meters < 1000) return Math.round(meters) + ' متر';
                return (meters / 1000).toFixed(meters < 10000 ? 1 : 0) + ' كم';
            }
            function fmtMin(seconds) {
                const m = Math.round(seconds / 60);
                if (m < 60) return m + ' دقيقة';
                const h = Math.floor(m / 60);
                const rm = m % 60;
                return h + ' س' + (rm ? ' و ' + rm + ' د' : '');
            }
            function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

            function initBizMap(el) {
                if (el.dataset.inited) return;
                el.dataset.inited = '1';
                const lat = parseFloat(el.dataset.lat);
                const lng = parseFloat(el.dataset.lng);
                if (isNaN(lat) || isNaN(lng)) return;
                const color = el.dataset.color || '#2D5BFF';
                const name = el.dataset.name || '';
                const info = el.querySelector('[data-route-info]');

                const map = L.map(el, {
                    center: [lat, lng],
                    zoom: 16,
                    scrollWheelZoom: false,
                    attributionControl: true,
                });
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    crossOrigin: true,
                    keepBuffer: 4,
                    updateWhenIdle: true,
                    attribution: '&copy; OpenStreetMap',
                }).addTo(map);

                const pinIcon = L.divIcon({
                    html: '<div style="width:34px;height:34px;background:' + color + ';border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 12px -2px rgba(0,0,0,.35);border:3px solid #fff;display:grid;place-items:center;"><div style="width:10px;height:10px;background:#fff;border-radius:50%;transform:rotate(45deg);"></div></div>',
                    className: '',
                    iconSize: [34, 34],
                    iconAnchor: [17, 32],
                });
                const marker = L.marker([lat, lng], { icon: pinIcon }).addTo(map);
                if (name) marker.bindPopup('<strong>' + escapeHtml(name) + '</strong>');

                map.on('click', () => map.scrollWheelZoom.enable());
                map.on('mouseout', () => map.scrollWheelZoom.disable());

                instances.set(el.id, {
                    map, lat, lng, color, info,
                    userMarker: null, accuracyCircle: null, routeLine: null, fallbackLine: null,
                });
            }

            function setInfo(info, html, isError) {
                if (!info) return;
                info.innerHTML = html;
                info.hidden = false;
                info.classList.toggle('is-error', !!isError);
            }

            async function fetchRoute(fromLat, fromLng, toLat, toLng) {
                const url = 'https://router.project-osrm.org/route/v1/driving/'
                    + fromLng + ',' + fromLat + ';' + toLng + ',' + toLat
                    + '?overview=full&geometries=geojson';
                const ctrl = new AbortController();
                const t = setTimeout(() => ctrl.abort(), 8000);
                try {
                    const r = await fetch(url, { signal: ctrl.signal });
                    if (!r.ok) throw new Error('osrm http ' + r.status);
                    const j = await r.json();
                    const route = j.routes && j.routes[0];
                    if (!route) throw new Error('no route');
                    return {
                        coords: route.geometry.coordinates.map(([lng, lat]) => [lat, lng]),
                        distance: route.distance,
                        duration: route.duration,
                    };
                } finally {
                    clearTimeout(t);
                }
            }

            function haversine(lat1, lng1, lat2, lng2) {
                const R = 6371000;
                const toRad = (d) => d * Math.PI / 180;
                const dLat = toRad(lat2 - lat1);
                const dLng = toRad(lng2 - lng1);
                const a = Math.sin(dLat / 2) ** 2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2;
                return 2 * R * Math.asin(Math.sqrt(a));
            }

            async function runRoute(btn) {
                const targetId = btn.dataset.routeTrigger;
                const inst = instances.get(targetId);
                if (!inst) return;
                if (!navigator.geolocation) {
                    setInfo(inst.info, 'متصفحك مش بيدعم تحديد الموقع', true);
                    return;
                }

                const iconWrap = btn.querySelector('[data-route-icon]');
                const labelEl  = btn.querySelector('[data-route-label]');
                const origIcon = iconWrap ? iconWrap.innerHTML : '';
                const origLabel = labelEl ? labelEl.textContent : '';
                btn.disabled = true;
                if (iconWrap) iconWrap.innerHTML = '<span class="biz-route-spinner"></span>';
                if (labelEl) labelEl.textContent = 'بحدّد موقعك…';
                setInfo(inst.info, '<span class="biz-route-spinner" style="color:#FF7A4D"></span> بحدّد موقعك…');

                navigator.geolocation.getCurrentPosition(async (pos) => {
                    const ulat = pos.coords.latitude;
                    const ulng = pos.coords.longitude;
                    const accuracy = pos.coords.accuracy || 0;

                    // Clear prior route artefacts
                    if (inst.userMarker)     { inst.map.removeLayer(inst.userMarker);     inst.userMarker = null; }
                    if (inst.accuracyCircle) { inst.map.removeLayer(inst.accuracyCircle); inst.accuracyCircle = null; }
                    if (inst.routeLine)      { inst.map.removeLayer(inst.routeLine);      inst.routeLine = null; }
                    if (inst.fallbackLine)   { inst.map.removeLayer(inst.fallbackLine);   inst.fallbackLine = null; }

                    // User marker + accuracy halo
                    inst.accuracyCircle = L.circle([ulat, ulng], {
                        radius: Math.max(accuracy, 20),
                        color: '#2D5BFF', weight: 1, opacity: .25,
                        fillColor: '#2D5BFF', fillOpacity: .08,
                    }).addTo(inst.map);
                    const userIcon = L.divIcon({
                        html: '<div class="biz-user-pin"></div>',
                        className: '', iconSize: [18, 18], iconAnchor: [9, 9],
                    });
                    inst.userMarker = L.marker([ulat, ulng], { icon: userIcon, interactive: false, zIndexOffset: 1000 }).addTo(inst.map);

                    if (labelEl) labelEl.textContent = 'بارسم الطريق…';
                    setInfo(inst.info, '<span class="biz-route-spinner" style="color:#FF7A4D"></span> بارسم الطريق…');

                    let drewRouted = false;
                    try {
                        const r = await fetchRoute(ulat, ulng, inst.lat, inst.lng);
                        inst.routeLine = L.polyline(r.coords, {
                            color: '#FF7A4D', weight: 5, opacity: .9,
                            lineCap: 'round', lineJoin: 'round',
                        }).addTo(inst.map);
                        L.polyline(r.coords, { color: '#fff', weight: 8, opacity: .6 }).addTo(inst.map).bringToBack();
                        setInfo(inst.info, '<span class="dot"></span> ' + fmtKm(r.distance) + ' · ' + fmtMin(r.duration));
                        drewRouted = true;
                    } catch (e) {
                        // Fallback: straight line + haversine distance
                        inst.fallbackLine = L.polyline([[ulat, ulng], [inst.lat, inst.lng]], {
                            color: '#FF7A4D', weight: 4, opacity: .85, dashArray: '6 6',
                        }).addTo(inst.map);
                        const d = haversine(ulat, ulng, inst.lat, inst.lng);
                        setInfo(inst.info, '<span class="dot"></span> ' + fmtKm(d) + ' (خط مستقيم)');
                    }

                    const bounds = L.latLngBounds([[ulat, ulng], [inst.lat, inst.lng]]);
                    if (inst.routeLine) bounds.extend(inst.routeLine.getBounds());
                    inst.map.fitBounds(bounds, { padding: [30, 30], maxZoom: 17 });

                    btn.disabled = false;
                    if (iconWrap) iconWrap.innerHTML = origIcon;
                    if (labelEl)  labelEl.textContent = drewRouted ? 'حدّث الطريق' : 'حاول تاني';
                }, (err) => {
                    btn.disabled = false;
                    if (iconWrap) iconWrap.innerHTML = origIcon;
                    if (labelEl)  labelEl.textContent = origLabel;
                    let msg = 'مش قادر أوصل لموقعك';
                    if (err.code === 1) msg = 'لازم تسمح بتحديد الموقع';
                    else if (err.code === 3) msg = 'الموقع طوّل، حاول تاني';
                    setInfo(inst.info, msg, true);
                }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
            }

            function bootAll() {
                document.querySelectorAll('[data-biz-map]').forEach(initBizMap);
                document.querySelectorAll('[data-route-trigger]').forEach((btn) => {
                    btn.addEventListener('click', () => runRoute(btn));
                });
            }
            if (typeof L !== 'undefined') bootAll();
            else window.addEventListener('load', bootAll);
        })();
        </script>
        @endpush
    @endif

    {{-- Gallery --}}
    @if($business->photos->isNotEmpty() || $isOwner)
        <div class="card-light p-4 mb-3">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-coral-600">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                    </svg>
                    صور النشاط
                </h3>
                @if($isOwner && $business->photos->count() < 6)
                    <form method="POST" action="{{ route('business.photo.store', $business) }}" enctype="multipart/form-data" class="inline">
                        @csrf
                        <label class="cursor-pointer text-xs font-bold text-coral-600 hover:underline">
                            + أضف صورة
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="this.form.submit()">
                        </label>
                    </form>
                @endif
            </div>
            @if($business->photos->isNotEmpty())
                <div class="grid grid-cols-3 gap-2">
                    @foreach($business->photos as $ph)
                        <div class="relative aspect-square">
                            <img src="{{ $ph->url }}" alt="" loading="lazy" class="w-full h-full object-cover rounded-xl">
                            @if($isOwner)
                                <form method="POST" action="{{ route('business.photo.destroy', $ph) }}"
                                      data-confirm="حذف الصورة؟" data-confirm-tone="danger"
                                      class="absolute top-1 end-1">
                                    @csrf @method('DELETE')
                                    <button class="w-7 h-7 rounded-full bg-white/90 grid place-items-center text-blush-500 hover:bg-blush-500 hover:text-white transition" aria-label="حذف">
                                        <x-icon name="trash" class="w-3.5 h-3.5"/>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if($isOwner)
                    <p class="text-[10px] text-ink-400 mt-2">{{ $business->photos->count() }}/6</p>
                @endif
            @else
                <p class="text-xs text-ink-400 text-center py-4">مفيش صور لسه — أضف أول صورة.</p>
            @endif
        </div>
    @endif

    {{-- Rating form (logged-in users; only the actual owner can't rate themselves) --}}
    @auth
        @if(! $isActualOwner)
            @php $myRating = (int) ($myReview->rating ?? 0); @endphp
            <div class="card-light p-4 mb-3">
                <h3 class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-2 mb-1">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-honey-500"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                    {{ $myReview ? 'تقييمك' : 'قيّم النشاط' }}
                </h3>
                <p class="text-[11px] text-ink-500 mb-3">{{ $myReview ? 'تقدر تعدّل تقييمك في أي وقت.' : 'دوسلك على نجمة وقول رأيك.' }}</p>

                <form method="POST" action="{{ route('business.review.store', $business) }}" class="space-y-3" data-rate-form>
                    @csrf
                    <input type="hidden" name="rating" value="{{ $myRating }}" data-rate-input>

                    <div class="flex items-center gap-1.5" dir="ltr" data-rate-stars>
                        @for($i=1; $i<=5; $i++)
                            <button type="button" data-rate-value="{{ $i }}"
                                    class="w-10 h-10 rounded-full grid place-items-center transition {{ $i <= $myRating ? 'text-honey-500' : 'text-ink-300' }} hover:text-honey-500 hover:bg-honey-100/40"
                                    aria-label="{{ $i }} نجوم">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                            </button>
                        @endfor
                        <span class="ms-2 text-xs font-bold text-ink-500" data-rate-label>{{ $myRating ? $myRating.'/5' : '' }}</span>
                    </div>

                    <textarea name="body" rows="3" maxlength="1000" placeholder="رأيك (اختياري)"
                              class="w-full bg-cream-100 rounded-2xl px-4 py-3 text-ink-950 placeholder-ink-400 outline-0 border border-ink-950/8 focus:border-coral-500 transition resize-none">{{ old('body', $myReview->body ?? '') }}</textarea>

                    @error('rating') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror
                    @error('body') <p class="text-blush-500 text-xs">{{ $message }}</p> @enderror

                    <div class="flex items-center gap-2">
                        <button type="submit" class="btn-primary !py-2.5 text-xs">
                            {{ $myReview ? 'حدّث التقييم' : 'إرسال التقييم' }}
                            <x-icon name="check" class="w-3.5 h-3.5"/>
                        </button>
                        @if($myReview)
                            <button type="submit" formaction="{{ route('business.review.destroy', $business) }}" formmethod="POST"
                                    class="text-xs font-bold text-blush-500 hover:underline"
                                    data-confirm="حذف تقييمك؟" data-confirm-tone="danger">
                                @method('DELETE')
                                احذف
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <script>
                (function () {
                    const form = document.querySelector('[data-rate-form]');
                    if (!form) return;
                    const input = form.querySelector('[data-rate-input]');
                    const label = form.querySelector('[data-rate-label]');
                    form.querySelectorAll('[data-rate-value]').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const v = parseInt(btn.dataset.rateValue, 10);
                            input.value = v;
                            if (label) label.textContent = v + '/5';
                            form.querySelectorAll('[data-rate-value]').forEach(b => {
                                const bv = parseInt(b.dataset.rateValue, 10);
                                b.classList.toggle('text-honey-500', bv <= v);
                                b.classList.toggle('text-ink-300', bv > v);
                            });
                        });
                    });
                })();
            </script>
        @endif
    @endauth

    {{-- Reviews --}}
    @if(isset($reviews) && $reviews->isNotEmpty())
        <div class="card-light p-4 mb-3">
            <h3 class="text-sm font-extrabold text-ink-950 inline-flex items-center gap-2 mb-3">
                <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-coral-500"><polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/></svg>
                آراء الناس
                <span class="text-ink-400 font-normal">({{ $reviews->count() }})</span>
            </h3>
            <div class="space-y-3">
                @foreach($reviews as $r)
                    <div class="border-b border-ink-950/8 last:border-0 pb-3 last:pb-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-7 h-7 rounded-full pill-honey grid place-items-center text-xs font-bold shrink-0">
                                {{ mb_substr($r->maskedPhone(), 0, 1) }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-ink-950" dir="ltr">{{ $r->maskedPhone() }}</div>
                                @if($r->reviewed_at)
                                    <div class="text-[10px] text-ink-400">{{ $r->reviewed_at->translatedFormat('d M Y') }}</div>
                                @endif
                            </div>
                            @if($r->rating > 0)
                                <div class="text-xs font-bold text-coral-600 shrink-0">
                                    @for($i=0; $i<$r->rating; $i++)★@endfor<span class="text-ink-300">@for($i=$r->rating; $i<5; $i++)★@endfor</span>
                                </div>
                            @endif
                        </div>
                        @if($r->body)
                            <p class="text-sm text-ink-950 leading-relaxed">{{ $r->body }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Owner link (small footer chip) --}}
    @if($business->owner)
        <a href="{{ route('profile.show', $business->owner->username) }}" class="card-light p-3 mb-3 flex items-center gap-2 hover:bg-cream-100 transition">
            <x-icon name="user" class="w-4 h-4 text-ink-400"/>
            <span class="text-xs text-ink-500">صاحب النشاط</span>
            <span class="font-bold text-ink-950 text-sm">{{ '@'.$business->owner->username }}</span>
        </a>
    @else
        {{-- Category-aware claim CTA. Public landmarks (mosque/transport/etc.)
             get a "report wrong data" framing instead of an ownership pitch. --}}
        @php
            $claim       = \App\Support\ClaimCta::forBusiness($business);
            $reportTo    = config('services.banhawy.support_whatsapp', '01000000000');
            $correctMsg  = "تبليغ تحديث بيانات على بنهاوي\nالنشاط: {$business->name}\nرابط: ".route('directory.show', $business)."\nالغلط: ";
            $claimUrl    = auth()->check()
                ? route('directory.claim.show', $business)
                : route('login').'?redirect='.urlencode(route('directory.claim.show', $business));
            $ctaHref     = $claim['mode'] === 'correction'
                ? 'https://wa.me/'.\App\Services\WaapiService::toIntl($reportTo).'?text='.urlencode($correctMsg)
                : $claimUrl;
            $ctaTone     = $claim['mode'] === 'correction'
                ? ['bg' => 'bg-honey-50', 'ring' => 'ring-honey-500/20', 'pill' => 'bg-honey-500', 'arrow' => 'text-honey-700']
                : ['bg' => 'bg-coral-50', 'ring' => 'ring-coral-500/20', 'pill' => 'bg-coral-500', 'arrow' => 'text-coral-600'];
        @endphp
        <a href="{{ $ctaHref }}"
           @if($claim['mode'] === 'correction') target="_blank" rel="noopener" @endif
           data-track-click="{{ $claim['mode'] === 'correction' ? 'business_report' : 'business_claim' }}"
           data-business="{{ $business->id }}"
           class="card-light p-4 mb-3 flex items-center gap-3 hover:bg-cream-100 transition ring-1 {{ $ctaTone['ring'] }} {{ $ctaTone['bg'] }}">
            <span class="w-10 h-10 rounded-2xl {{ $ctaTone['pill'] }} grid place-items-center text-white shrink-0">
                @if($claim['mode'] === 'correction')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <line x1="4" y1="22" x2="4" y2="15"/>
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                    </svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z"/>
                    </svg>
                @endif
            </span>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-extrabold text-ink-950">{{ $claim['title'] }}</div>
                <p class="text-[11px] text-ink-500 leading-relaxed">{{ $claim['desc'] }}</p>
            </div>
            <x-icon name="arrow-left" class="w-4 h-4 {{ $ctaTone['arrow'] }} shrink-0"/>
        </a>
    @endif

    {{-- Similar --}}
    @if($similar->isNotEmpty())
        <h3 class="text-sm font-extrabold text-ink-950 mb-2 mt-5">{{ $sm['label'] }} تاني في نفس المنطقة</h3>
        <div class="space-y-3">
            @foreach($similar as $b)
                @include('directory.partials.business-row', ['business' => $b])
            @endforeach
        </div>
    @endif

    {{-- Spacer so the sticky CTA bar doesn't hide last content row on mobile --}}
    <div class="h-24 lg:hidden" aria-hidden="true"></div>
</div>

{{-- ─── Sticky mobile CTA bar ───────────────────────────────────────
     Builds up to 3 actions in priority order. When a primary action is
     unavailable, falls back to "report wrong data" or "claim this page" so
     the bar always offers something useful instead of showing fewer buttons. --}}
@php
    $callNumberSticky = $business->phone ?: $business->hotline;
    $hasPhone = (bool) $callNumberSticky;
    $hasWa    = (bool) $business->whatsapp;
    $hasDir   = $business->lat && $business->lng;

    $reportTo  = config('services.banhawy.support_whatsapp', '01000000000');
    $reportMsg = "بلاغ عن بيانات غلط على بنهاوي\nالنشاط: {$business->name}\nرابط: ".route('directory.show', $business)."\nالغلط: ";
    $reportUrl = 'https://wa.me/'.\App\Services\WaapiService::toIntl($reportTo).'?text='.urlencode($reportMsg);
    $claimUrl  = auth()->check()
        ? route('directory.claim.show', $business)
        : route('login').'?redirect='.urlencode(route('directory.claim.show', $business));

    $stickyActions = [];
    if ($hasPhone) {
        $stickyActions[] = ['kind' => 'phone', 'label' => 'اتصال',
            'href'  => 'tel:'.preg_replace('/[^0-9+]/', '', $callNumberSticky),
            'cls'   => 'bg-ink-950 text-white hover:bg-ink-800',
            'track' => 'business_call'];
    }
    if ($hasWa) {
        $stickyActions[] = ['kind' => 'whatsapp', 'label' => 'واتساب',
            'href'  => 'https://wa.me/'.\App\Services\WaapiService::toIntl($business->whatsapp),
            'cls'   => 'bg-mint-600 text-white hover:bg-mint-500',
            'track' => 'business_whatsapp', 'external' => true];
    }
    if ($hasDir) {
        $stickyActions[] = ['kind' => 'directions', 'label' => 'الاتجاهات',
            'href'  => "https://www.google.com/maps/dir/?api=1&destination={$business->lat},{$business->lng}",
            'cls'   => 'bg-coral-50 text-coral-600 hover:bg-coral-100',
            'track' => 'business_directions', 'external' => true];
    }
    // Fallbacks fill up to 3 slots so the bar never looks empty.
    if (count($stickyActions) < 3) {
        if (! $hasPhone && ! $business->owner) {
            $stickyActions[] = ['kind' => 'claim', 'label' => 'امتلك الصفحة',
                'href' => $claimUrl,
                'cls'  => 'bg-coral-500 text-white hover:bg-coral-600',
                'track' => 'business_claim'];
        }
        if (count($stickyActions) < 3) {
            $stickyActions[] = ['kind' => 'report', 'label' => 'بلّغ عن خطأ',
                'href'  => $reportUrl,
                'cls'   => 'bg-honey-100 text-honey-700 hover:bg-honey-500 hover:text-ink-950',
                'track' => 'business_report', 'external' => true];
        }
    }
    // Hard cap at 3 — keeps the row compact on small screens.
    $stickyActions = array_slice($stickyActions, 0, 3);
@endphp
@if(! empty($stickyActions))
    <div class="lg:hidden fixed inset-x-0 z-30"
         style="bottom: calc(4.5rem + env(safe-area-inset-bottom));">
        <div class="mx-3 bg-white rounded-2xl shadow-2xl ring-1 ring-ink-950/8 p-1.5 flex items-center gap-1.5">
            @foreach($stickyActions as $a)
                <a href="{{ $a['href'] }}"
                   @if(! empty($a['external'])) target="_blank" rel="noopener" @endif
                   data-track-click="{{ $a['track'] }}" data-business="{{ $business->id }}"
                   class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl text-xs font-extrabold transition {{ $a['cls'] }}">
                    @switch($a['kind'])
                        @case('phone')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            @break
                        @case('whatsapp')
                            <x-icon name="whatsapp" class="w-4 h-4"/>
                            @break
                        @case('directions')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                <path d="M21.71 11.29 12.71 2.29a1 1 0 0 0-1.42 0l-9 9a1 1 0 0 0 0 1.42l9 9a1 1 0 0 0 1.42 0l9-9a1 1 0 0 0 0-1.42z"/>
                                <polyline points="9 12 11 14 15 10"/>
                            </svg>
                            @break
                        @case('claim')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                <path d="M12 2 4 6v6c0 5 3.4 9.6 8 11 4.6-1.4 8-6 8-11V6Z"/>
                            </svg>
                            @break
                        @case('report')
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                <line x1="4" y1="22" x2="4" y2="15"/>
                                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                            </svg>
                            @break
                    @endswitch
                    {{ $a['label'] }}
                </a>
            @endforeach
        </div>
    </div>
@endif
@endsection
