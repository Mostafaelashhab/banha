@extends('layouts.app', [
    'title' => 'إدارة ' . $business->name . ' · بنهاوي',
])

@php
    $cm = $business->categoryMeta();
    $supportsOrdering = $business->supportsOrdering() && $business->has_menu;
    $supportsBooking  = $business->booking_enabled && $business->bookingApplicable();
@endphp

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- ─── Top: back + business header ─── --}}
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('directory.show', $business) }}" class="w-9 h-9 rounded-full bg-white border border-ink-950/8 grid place-items-center text-ink-950 hover:bg-cream-100 transition" aria-label="رجوع لصفحة النشاط">
            <x-icon name="arrow-right" class="w-4 h-4"/>
        </a>
        <div class="flex-1 min-w-0">
            <div class="text-[10px] font-bold text-ink-500">إدارة النشاط</div>
            <div class="text-sm font-extrabold text-ink-950 truncate">{{ $business->name }}</div>
        </div>
        <a href="{{ route('directory.show', $business) }}"
           class="text-[11px] font-extrabold text-coral-600 hover:underline">
            اعرض الصفحة العامة ←
        </a>
    </div>

    {{-- ─── At-a-glance stats strip ─── --}}
    <div class="grid grid-cols-4 gap-2 mb-5">
        <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 text-center">
            <div class="text-lg font-black text-coral-600">{{ number_format($business->views_count ?? 0) }}</div>
            <div class="text-[9px] font-bold text-ink-500 mt-0.5">مشاهدة</div>
        </div>
        <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 text-center">
            <div class="text-lg font-black text-mint-700">{{ number_format($business->phone_clicks ?? 0) }}</div>
            <div class="text-[9px] font-bold text-ink-500 mt-0.5">اتصال</div>
        </div>
        <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 text-center">
            <div class="text-lg font-black text-honey-700">{{ number_format($business->whatsapp_clicks ?? 0) }}</div>
            <div class="text-[9px] font-bold text-ink-500 mt-0.5">واتساب</div>
        </div>
        <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-3 text-center">
            <div class="text-lg font-black text-ink-950" dir="ltr">
                {{ $business->rating_avg ? number_format((float) $business->rating_avg, 1) : '—' }}
            </div>
            <div class="text-[9px] font-bold text-ink-500 mt-0.5">{{ $business->ratings_count ? $business->ratings_count . ' تقييم' : 'تقييم' }}</div>
        </div>
    </div>

    {{-- ─── Hero CTAs row: orders + bookings (the action-required pages) ─── --}}
    @if($supportsOrdering || $supportsBooking)
        <div class="grid {{ $supportsOrdering && $supportsBooking ? 'grid-cols-2' : 'grid-cols-1' }} gap-2 mb-3">
            @if($supportsOrdering)
                <a href="{{ route('order.owner.index', $business) }}"
                   class="block p-4 rounded-2xl bg-coral-500 text-white hover:scale-[1.01] transition shadow-lg relative">
                    @if($pendingOrders > 0)
                        <span class="absolute top-2 end-2 min-w-[22px] h-[22px] px-1 rounded-full bg-white text-coral-600 text-[11px] font-black grid place-items-center">{{ $pendingOrders }}</span>
                    @endif
                    <span class="w-10 h-10 rounded-2xl bg-white/20 grid place-items-center mb-2">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>
                        </svg>
                    </span>
                    <div class="text-sm font-black">الأوردرات</div>
                    <div class="text-[11px] text-white/85 mt-0.5">
                        @if($pendingOrders > 0)
                            {{ $pendingOrders }} طلب بانتظار التأكيد
                        @else
                            مفيش طلبات شغّالة دلوقتي
                        @endif
                    </div>
                </a>
            @endif

            @if($supportsBooking)
                <a href="{{ route('booking.owner.index', $business) }}"
                   class="block p-4 rounded-2xl bg-mint-600 text-white hover:scale-[1.01] transition shadow-lg relative">
                    @if($upcomingBookings > 0)
                        <span class="absolute top-2 end-2 min-w-[22px] h-[22px] px-1 rounded-full bg-white text-mint-700 text-[11px] font-black grid place-items-center">{{ $upcomingBookings }}</span>
                    @endif
                    <span class="w-10 h-10 rounded-2xl bg-white/20 grid place-items-center mb-2">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </span>
                    <div class="text-sm font-black">الحجوزات</div>
                    <div class="text-[11px] text-white/85 mt-0.5">
                        @if($upcomingBookings > 0)
                            {{ $upcomingBookings }} حجز قادم
                        @else
                            مفيش حجوزات قادمة
                        @endif
                    </div>
                </a>
            @endif
        </div>
    @endif

    {{-- ─── Section: business data ─── --}}
    <h2 class="text-[11px] font-extrabold text-ink-500 mb-2 mt-5 px-1">بيانات النشاط</h2>
    <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 divide-y divide-ink-950/6 overflow-hidden mb-3">

        {{-- Edit data --}}
        <a href="{{ route('directory.edit', $business) }}"
           class="flex items-center gap-3 px-4 py-3.5 hover:bg-cream-100 transition">
            <x-icon-tile icon="edit"/>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-extrabold text-ink-950">تعديل البيانات</div>
                <div class="text-[11px] text-ink-500">الاسم · العنوان · المواعيد · التواصل · الصور</div>
            </div>
            <x-icon name="arrow-left" class="w-4 h-4 text-ink-400 rtl:rotate-180"/>
        </a>

        {{-- Menu --}}
        <a href="{{ route('menu.manage', $business) }}"
           class="flex items-center gap-3 px-4 py-3.5 hover:bg-cream-100 transition">
            <x-icon-tile icon="menu" tone="honey"/>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-extrabold text-ink-950">
                    @php $L = \App\Models\Business::menuLabels($business->category); @endphp
                    {{ $L['title'] }}
                </div>
                <div class="text-[11px] text-ink-500">
                    @if($business->has_menu)
                        {{ $menuItemsCount }} {{ $L['item_label'] }} متاح
                    @else
                        لسه ما فعّلتش المنيو — ضيف أصناف
                    @endif
                </div>
            </div>
            <x-icon name="arrow-left" class="w-4 h-4 text-ink-400 rtl:rotate-180"/>
        </a>

        {{-- Photos quick link (uses the edit page section) --}}
        <a href="{{ route('directory.edit', $business) }}#photos"
           class="flex items-center gap-3 px-4 py-3.5 hover:bg-cream-100 transition">
            <x-icon-tile icon="camera" tone="mint"/>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-extrabold text-ink-950">الصور</div>
                <div class="text-[11px] text-ink-500">
                    {{ $photosCount > 0 ? $photosCount . ' صورة في الجاليري' : 'مفيش صور لسه — ضيف صور للمحل' }}
                </div>
            </div>
            <x-icon name="arrow-left" class="w-4 h-4 text-ink-400 rtl:rotate-180"/>
        </a>

        {{-- Stats --}}
        <a href="{{ route('directory.stats', $business) }}"
           class="flex items-center gap-3 px-4 py-3.5 hover:bg-cream-100 transition">
            <x-icon-tile icon="chart" tone="blush"/>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-extrabold text-ink-950">الإحصائيات</div>
                <div class="text-[11px] text-ink-500">مشاهدات · اتصالات · واتساب · تقييمات</div>
            </div>
            <x-icon name="arrow-left" class="w-4 h-4 text-ink-400 rtl:rotate-180"/>
        </a>
    </div>

    {{-- ─── Section: page link / public view ─── --}}
    <h2 class="text-[11px] font-extrabold text-ink-500 mb-2 mt-5 px-1">صفحتك العامة</h2>
    <div class="bg-white rounded-2xl ring-1 ring-ink-950/8 p-4 mb-3">
        <div class="flex items-center gap-3 mb-3">
            <x-icon-tile icon="globe"/>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-extrabold text-ink-950">رابط صفحتك</div>
                <div class="text-[10px] text-ink-500 truncate" dir="ltr">{{ $business->slug ? url('/biz/'.$business->slug) : route('directory.show', $business) }}</div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('directory.show', $business) }}"
               class="inline-flex items-center justify-center gap-1.5 py-2 rounded-xl bg-cream-100 text-ink-950 text-[12px] font-extrabold hover:bg-cream-200 transition">
                اعرض الصفحة
            </a>
            <button type="button"
                    data-share data-share-url="{{ route('directory.show', $business) }}"
                    data-share-title="{{ $business->name }}"
                    class="inline-flex items-center justify-center gap-1.5 py-2 rounded-xl bg-coral-500 text-white text-[12px] font-extrabold hover:bg-coral-600 transition">
                <x-icon name="share" class="w-3.5 h-3.5"/>
                شارك
            </button>
        </div>
    </div>

    {{-- ─── Danger zone ─── --}}
    <h2 class="text-[11px] font-extrabold text-ink-500 mb-2 mt-5 px-1">المنطقة الحساسة</h2>
    <form method="POST" action="{{ route('directory.destroy', $business) }}"
          data-confirm="حذف النشاط؟"
          data-confirm-body="هينحذف خالص ومش هيرجع تاني."
          data-confirm-action="احذف"
          data-confirm-tone="danger">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-xl bg-white ring-1 ring-blush-500/30 text-blush-600 text-sm font-extrabold hover:bg-blush-50 transition">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
            </svg>
            احذف النشاط نهائياً
        </button>
    </form>

</div>
@endsection
