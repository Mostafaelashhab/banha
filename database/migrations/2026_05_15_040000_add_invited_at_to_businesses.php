<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            // Last time an admin sent the "claim your page" invite via WAAPI.
            // Used to disable the button so we don't spam the same number.
            $t->timestamp('invited_at')->nullable()->after('promoted_until');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropColumn('invited_at');
        });
    }
};
