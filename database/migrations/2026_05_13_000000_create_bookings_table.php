<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->boolean('booking_enabled')->default(false)->after('has_menu');
            $t->unsignedSmallInteger('booking_slot_minutes')->default(30)->after('booking_enabled');
            $t->unsignedSmallInteger('booking_lead_hours')->default(2)->after('booking_slot_minutes');
            $t->unsignedSmallInteger('booking_capacity')->default(1)->after('booking_lead_hours');
        });

        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name', 80);
            $t->string('phone', 20);
            $t->dateTime('starts_at');
            $t->unsignedSmallInteger('duration_minutes')->default(30);
            $t->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])
              ->default('pending');
            $t->text('notes')->nullable();
            $t->timestamps();

            $t->index(['business_id', 'starts_at']);
            $t->index(['phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropColumn(['booking_enabled', 'booking_slot_minutes', 'booking_lead_hours', 'booking_capacity']);
        });
    }
};
