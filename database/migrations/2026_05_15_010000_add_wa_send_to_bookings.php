<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $t) {
            // Mirrors orders.wa_send_status — pending|sent|failed|simulated
            $t->string('wa_send_status', 16)->default('pending')->after('notes');
            $t->timestamp('wa_sent_at')->nullable()->after('wa_send_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $t) {
            $t->dropColumn(['wa_send_status', 'wa_sent_at']);
        });
    }
};
