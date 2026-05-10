<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Per-day weekly schedule (overrides the freeform `hours` text when set).
            // Shape: {"sat":"09:00-23:00","sun":"09:00-23:00","mon":...,"fri":null}
            $table->json('hours_schedule')->nullable()->after('hours');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('hours_schedule');
        });
    }
};
