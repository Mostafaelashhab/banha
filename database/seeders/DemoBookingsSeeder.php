<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Business;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seed a doctor + dentist + barber with booking enabled,
 * plus a realistic spread of bookings across past/today/upcoming.
 *
 *   php artisan db:seed --class=DemoBookingsSeeder
 */
class DemoBookingsSeeder extends Seeder
{
    private const TZ = 'Africa/Cairo';

    private array $standardWeek = [
        'sat' => '09:00-21:00',
        'sun' => '09:00-21:00',
        'mon' => '09:00-21:00',
        'tue' => '09:00-21:00',
        'wed' => '09:00-21:00',
        'thu' => '09:00-21:00',
        'fri' => '14:00-21:00',
    ];

    public function run(): void
    {
        // ── Pick 1 doctor + 1 dentist + 1 barber/salon dynamically ────
        $doctor  = Business::whereIn('sub_type', ['doctor', 'gynecologist', 'pediatrician', 'orthopedic'])->first();
        $dentist = Business::where('sub_type', 'dentist')->first();
        $salon   = Business::whereIn('sub_type', ['barber', 'salon', 'beauty_clinic'])->first();

        $configs = array_values(array_filter([
            $doctor  ? ['id' => $doctor->id,  'slot' => 30, 'cap' => 1, 'lead' => 1, 'schedule' => $this->standardWeek] : null,
            $dentist ? ['id' => $dentist->id, 'slot' => 45, 'cap' => 1, 'lead' => 2, 'schedule' => $this->standardWeek] : null,
            $salon   ? ['id' => $salon->id,   'slot' => 30, 'cap' => 2, 'lead' => 0, 'schedule' => [
                'sat' => '10:00-22:00', 'sun' => '10:00-22:00', 'mon' => '10:00-22:00',
                'tue' => '10:00-22:00', 'wed' => '10:00-22:00', 'thu' => '10:00-22:00',
                'fri' => '16:00-23:00',
            ]] : null,
        ]));

        if (empty($configs)) {
            $this->command->error('No doctor/dentist/salon found to seed bookings on.');
            return;
        }

        $businesses = [];
        $owner = \App\Models\User::where('is_admin', true)->first();

        foreach ($configs as $cfg) {
            $b = Business::find($cfg['id']);
            if (! $b) { $this->command->warn("Skip: business {$cfg['id']} not found"); continue; }

            $b->update([
                'booking_enabled'      => true,
                'booking_slot_minutes' => $cfg['slot'],
                'booking_capacity'     => $cfg['cap'],
                'booking_lead_hours'   => $cfg['lead'],
                'hours_schedule'       => $cfg['schedule'],
                'whatsapp'             => $b->whatsapp ?: '01093565088',
                'owner_user_id'        => $b->owner_user_id ?: $owner?->id,
            ]);
            $businesses[] = $b;
            $this->command->info("✓ Enabled booking on #{$b->id} {$b->name}");
        }

        // ── Clear prior demo bookings on these businesses ─────
        $bizIds = collect($businesses)->pluck('id')->all();
        Booking::whereIn('business_id', $bizIds)->delete();

        // ── Seed a believable spread of bookings ──────────────
        $samples = [
            ['أحمد سيد',        '01012345678', 'كشف أول مرة'],
            ['ماريم حسن',       '01198765432', 'متابعة لـ آخر زيارة'],
            ['محمد إبراهيم',    '01225555444', null],
            ['نهال محسن',       '01006543210', 'سؤال بخصوص الأشعة'],
            ['عبد الرحمن خالد', '01511223344', null],
            ['سارة فؤاد',       '01098765432', 'حلاقة فاضي + لحية'],
            ['يوسف عمر',        '01155667788', null],
            ['هاجر طارق',       '01233445566', 'تنظيف أسنان'],
            ['كريم نبيل',       '01089991111', null],
            ['دينا أيمن',       '01277889911', 'حلاقة قصيرة'],
        ];

        $now = Carbon::now(self::TZ);
        foreach ($businesses as $b) {
            $rows = $this->buildSchedule($b, $now, $samples);
            foreach ($rows as $r) {
                Booking::create([
                    'business_id'      => $b->id,
                    'user_id'          => null,
                    'name'             => $r['name'],
                    'phone'            => $r['phone'],
                    'starts_at'        => $r['starts_at']->copy()->utc(),
                    'duration_minutes' => $b->booking_slot_minutes,
                    'status'           => $r['status'],
                    'notes'            => $r['notes'],
                ]);
            }
            $count = Booking::where('business_id', $b->id)->count();
            $this->command->info("  → {$count} bookings on #{$b->id}");
        }

        $this->command->line('');
        $this->command->info('🎯 Demo URLs:');
        foreach ($businesses as $b) {
            $this->command->line("  ► " . $b->name);
            $this->command->line("    • صفحة النشاط:      /directory/business/{$b->id}");
            $this->command->line("    • صفحة الحجز:        /directory/business/{$b->id}/book");
            $this->command->line("    • لوحة الأونر:       /directory/business/{$b->id}/bookings  (admin/owner)");
            $this->command->line('');
        }
    }

    private function buildSchedule(Business $business, Carbon $now, array $samples): array
    {
        $slot = $business->booking_slot_minutes;
        $rows = [];
        $pick = function () use (&$samples) {
            return $samples[array_rand($samples)];
        };

        // 2 past bookings (yesterday) — completed / no_show
        for ($i = 0; $i < 2; $i++) {
            [$name, $phone, $notes] = $pick();
            $when = $now->copy()->subDay()->setTime(10 + $i * 2, 0);
            $rows[] = compact('name', 'phone', 'notes') + [
                'starts_at' => $when,
                'status'    => $i === 0 ? 'completed' : 'no_show',
            ];
        }

        // Today: 1 past completed + 1 upcoming pending + 1 upcoming confirmed (if hours allow)
        $today10 = $now->copy()->setTime(10, 0);
        if ($today10->lt($now)) {
            [$name, $phone, $notes] = $pick();
            $rows[] = compact('name', 'phone', 'notes') + [
                'starts_at' => $today10,
                'status'    => 'completed',
            ];
        }
        $todayFuture = $now->copy()->addHours(3)->setMinute(0)->setSecond(0);
        if ($todayFuture->hour < 21) {
            [$name, $phone, $notes] = $pick();
            $rows[] = compact('name', 'phone', 'notes') + [
                'starts_at' => $todayFuture,
                'status'    => 'confirmed',
            ];
            [$name, $phone, $notes] = $pick();
            $rows[] = compact('name', 'phone', 'notes') + [
                'starts_at' => $todayFuture->copy()->addMinutes($slot * 2),
                'status'    => 'pending',
            ];
        }

        // Tomorrow: 3 mixed (pending heavy — what owner needs to action)
        $tomorrowBase = $now->copy()->addDay()->setTime(11, 0);
        foreach (['pending', 'pending', 'confirmed'] as $i => $st) {
            [$name, $phone, $notes] = $pick();
            $rows[] = compact('name', 'phone', 'notes') + [
                'starts_at' => $tomorrowBase->copy()->addMinutes($slot * $i * 2),
                'status'    => $st,
            ];
        }

        // Day after tomorrow: 2 (one cancelled to test the filter)
        $dayAfter = $now->copy()->addDays(2)->setTime(15, 0);
        foreach (['confirmed', 'cancelled'] as $i => $st) {
            [$name, $phone, $notes] = $pick();
            $rows[] = compact('name', 'phone', 'notes') + [
                'starts_at' => $dayAfter->copy()->addMinutes($slot * $i),
                'status'    => $st,
            ];
        }

        return $rows;
    }
}
