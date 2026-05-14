<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Business;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    private const TZ = 'Africa/Cairo';
    private const WINDOW_DAYS = 14;

    public function show(Business $business, Request $request)
    {
        abort_unless($business->booking_enabled, 404, 'الحجز غير مفعّل لهذا النشاط');

        // Day window: today → today+13 in Africa/Cairo
        $today = Carbon::now(self::TZ)->startOfDay();
        $days = [];
        for ($i = 0; $i < self::WINDOW_DAYS; $i++) {
            $d = $today->copy()->addDays($i);
            $days[] = [
                'date'    => $d,
                'key'     => $d->format('Y-m-d'),
                'weekday' => Business::WEEKDAYS[['sun','mon','tue','wed','thu','fri','sat'][(int) $d->format('w')]] ?? '',
                'pretty'  => $d->translatedFormat('d M'),
                'is_today' => $d->isSameDay($today),
            ];
        }

        // Selected day (default = first day with any slots, else today)
        $selectedKey = $request->query('day');
        $selected = null;
        if ($selectedKey) {
            try { $selected = Carbon::parse($selectedKey, self::TZ); } catch (\Throwable $e) { $selected = null; }
        }
        if (! $selected || $selected->lt($today) || $selected->diffInDays($today) > self::WINDOW_DAYS) {
            $selected = $today->copy();
        }

        $slots = $business->availableSlots($selected);

        return view('booking.show', [
            'business' => $business,
            'days'     => $days,
            'selected' => $selected,
            'selectedKey' => $selected->format('Y-m-d'),
            'slots'    => $slots,
            'user'     => Auth::user(),
        ]);
    }

    public function store(Business $business, Request $request)
    {
        abort_unless($business->booking_enabled, 404);

        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'name'      => ['required', 'string', 'max:80'],
            'phone'     => ['required', 'string', 'regex:/^01[0125]\d{8}$/'],
            'notes'     => ['nullable', 'string', 'max:500'],
        ], [
            'phone.regex' => 'رقم الموبايل لازم يكون مصري صحيح (11 رقم يبدأ بـ 010/011/012/015).',
        ]);

        $startsAt = Carbon::parse($data['starts_at'], self::TZ);

        // Re-validate against schedule + capacity at submit time (anti-race)
        $slots = $business->availableSlots($startsAt->copy()->startOfDay());
        $matching = collect($slots)->first(fn ($s) => $s['starts_at']->equalTo($startsAt));
        if (! $matching || ! $matching['bookable']) {
            throw ValidationException::withMessages([
                'starts_at' => 'الموعد ده مش متاح. اختر موعد تاني.',
            ]);
        }

        $booking = Booking::create([
            'business_id'      => $business->id,
            'user_id'          => Auth::id(),
            'name'             => trim($data['name']),
            'phone'            => $data['phone'],
            // app.timezone is UTC; convert before storing so reads round-trip correctly
            'starts_at'        => $startsAt->copy()->utc(),
            'duration_minutes' => $business->booking_slot_minutes ?: 30,
            'status'           => 'pending',
            'notes'            => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('booking.show', $business)
            ->with('booking_success', [
                'id'        => $booking->id,
                'starts_at' => $startsAt->format('Y-m-d H:i'),
                'pretty'    => $startsAt->translatedFormat('l d M · h:i a'),
                'wa_link'   => $this->whatsappLinkFor($business, $booking),
            ]);
    }

    public function ownerIndex(Business $business, Request $request)
    {
        $this->authorizeOwner($business);

        $filter = $request->query('filter', 'upcoming');
        $q = $business->bookings();

        if ($filter === 'upcoming') {
            $q->where('starts_at', '>=', now(self::TZ)->subHours(2))->orderBy('starts_at');
        } elseif ($filter === 'past') {
            $q->where('starts_at', '<', now(self::TZ))->orderByDesc('starts_at');
        } else {
            $q->orderByDesc('starts_at');
        }
        if (in_array($filter, ['pending', 'confirmed', 'cancelled', 'completed'])) {
            $q->where('status', $filter);
        }

        $bookings = $q->limit(200)->get();

        // Quick counts for tabs
        $counts = [
            'pending'   => $business->bookings()->where('status', 'pending')->where('starts_at', '>=', now(self::TZ))->count(),
            'upcoming'  => $business->bookings()->where('starts_at', '>=', now(self::TZ))->whereNotIn('status', ['cancelled'])->count(),
            'past'      => $business->bookings()->where('starts_at', '<', now(self::TZ))->count(),
        ];

        return view('booking.owner-index', compact('business', 'bookings', 'filter', 'counts'));
    }

    public function updateStatus(Booking $booking, Request $request)
    {
        $this->authorizeOwner($booking->business);

        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])],
        ]);

        $booking->update(['status' => $data['status']]);

        return back()->with('flash', 'تم تحديث الحجز.');
    }

    private function authorizeOwner(Business $business): void
    {
        $u = Auth::user();
        $isOwner = $u && $business->owner_user_id && $u->id === $business->owner_user_id;
        $isAdmin = $u && $u->is_admin;
        abort_unless($isOwner || $isAdmin, 403);
    }

    private function whatsappLinkFor(Business $business, Booking $booking): ?string
    {
        if (! $business->whatsapp) return null;
        $intl = \App\Services\WaapiService::toIntl($business->whatsapp);
        $msg = "السلام عليكم 👋\nحابب أحجز موعد عند {$business->name}:\n\n"
             . "📅 " . $booking->starts_at->translatedFormat('l d M Y') . "\n"
             . "🕐 " . $booking->starts_at->translatedFormat('h:i a') . "\n"
             . "👤 " . $booking->name . "\n"
             . "📞 " . $booking->phone
             . ($booking->notes ? "\n📝 " . $booking->notes : '')
             . "\n\n(الحجز عبر بنهاوي · رقم #{$booking->id})";
        return 'https://wa.me/' . $intl . '?text=' . urlencode($msg);
    }
}
