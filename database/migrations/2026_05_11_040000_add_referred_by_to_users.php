<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Self-referencing relation: who invited each user.
 *
 * - `referred_by` = the inviter's user id (nullable).
 * - `referral_settled` = whether the inviter has already been credited.
 *   Inviter credit is deferred — only paid out after the invitee earns
 *   50+ points from their own organic activity. This prevents the
 *   simple "create N alt accounts and farm signup bonuses" exploit.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('referred_by')->nullable()->after('zone_id')
                  ->constrained('users')->nullOnDelete();
            $table->boolean('referral_settled')->default(false)->after('referred_by')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['referred_by', 'referral_settled']);
        });
    }
};
