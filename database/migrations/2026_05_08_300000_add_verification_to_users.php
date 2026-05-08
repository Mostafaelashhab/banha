<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('verification_tier', 10)->default('none')->after('is_verified');
            $table->timestamp('verified_at')->nullable()->after('verification_tier');
            $table->unsignedTinyInteger('valid_reports_count')->default(0)->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['verification_tier', 'verified_at', 'valid_reports_count']);
        });
    }
};
