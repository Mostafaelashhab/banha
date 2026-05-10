@extends('layouts.app', [
    'title'       => $business->name . ' · ' . ($business->zone->name ?? 'بنها') . ' · بنهاوي',
    'description' => 'كل اللي تحتاج تعرفه عن ' . $business->name . ' في ' . ($business->zone->name ?? 'بنها') . ': المواعيد، الأسعار، التواصل، التقييمات.',
    'ogImage'     => $business->photo_url,
    'canonical'   => route('directory.show', $business),
])

@php
    $cm = $business->categoryMeta();
    $sm = $business->subTypeMeta();
    $isActualOwner = auth()->check() && $business->owner_user_id && auth()->id() === $business->owner_user_id;
    $isOwner       = $isActualOwner || (auth()->check() && auth()->user()->is_admin); // edit/manage perms
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

        @if($isOwner)
            <a href="{{ route('menu.manage', $business) }}" class="w-9 h-9 rounded-full bg-honey-100 text-honey-700 grid place-items-center hover:bg-honey-500 hover:text-ink-950 transition" title="منيو">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                    <rect x="6" y="3" width="12" height="18" rx="2"/>
                    <line x1="9" y1="8" x2="15" y2="8"/>
                    <line x1="9" y1="12" x2="15" y2="12"/>
                    <line x1="9" y1="16" x2="13" y2="16"/>
                </svg>
            </a>
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

    {{-- Hero: branded Banhawy cover when no user photo --}}
    @php
        $heroPhoto = ($business->photo_url && ! str_contains($business->photo_url, 'd-innova.com')) ? $business->photo_url : null;
        $heroInitial = mb_substr(trim($business->name ?: '?'), 0, 1);
        $heroColor   = $cm['color'] ?? '#FF7A4D';
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

    {{-- Menu CTA (huge, the goal of this page for restaurants) --}}
    @if($business->has_menu)
        <a href="{{ route('menu.public', $business) }}" class="block mb-3 p-4 rounded-2xl bg-gradient-to-r from-coral-500 to-honey-500 text-white text-center hover:scale-[1.01] transition shadow-lg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8 mx-auto">
                <rect x="6" y="3" width="12" height="18" rx="2"/>
                <line x1="9" y1="8" x2="15" y2="8"/>
                <line x1="9" y1="12" x2="15" y2="12"/>
                <line x1="9" y1="16" x2="13" y2="16"/>
            </svg>
            <div class="text-base font-extrabold mt-2">شوف المنيو والأسعار</div>
            <div class="text-xs text-white/80 mt-0.5">{{ $business->menuCategories()->count() }} قسم · {{ $business->menuItems()->where('is_available', true)->count() }} صنف</div>
        </a>
    @endif

    {{-- Quick contact CTAs --}}
    @if($business->phone || $business->whatsapp)
        <div class="grid grid-cols-{{ ($business->phone && $business->whatsapp) ? '2' : '1' }} gap-2 mb-3">
            @if($business->phone)
                <a href="tel:{{ $business->phone }}" data-track-click="phone" data-business="{{ $business->id }}" class="btn-primary justify-center !py-3.5 text-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    اتصل
                </a>
            @endif
            @if($business->whatsapp)
                <a href="https://wa.me/{{ \App\Services\WaapiService::toIntl($business->whatsapp) }}" target="_blank"
                   data-track-click="whatsapp" data-business="{{ $business->id }}"
                   class="inline-flex items-center justify-center gap-2 py-3.5 rounded-full font-bold text-white text-sm transition hover:scale-[1.02]"
                   style="background: linear-gradient(135deg, #25D366, #128C7E)">
                    <x-icon name="whatsapp" class="w-4 h-4"/> واتساب
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

    {{-- Info rows --}}
    @if($business->address || $business->hours || $business->is_24h || $business->phone)
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

            @if($business->hours || $business->is_24h)
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-xl {{ $business->is_24h ? 'pill-mint' : 'pill-honey' }} grid place-items-center shrink-0">
                        <x-icon name="bell" class="w-4 h-4"/>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] text-ink-500">المواعيد</div>
                        <div class="text-sm font-bold text-ink-950">
                            @if($business->is_24h)
                                <span class="text-mint-700">٢٤ ساعة · مفتوح دلوقتي</span>
                            @else
                                {{ $business->hours }}
                            @endif
                        </div>
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
        </div>
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
</div>
@endsection
