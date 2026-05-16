<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a `meta` JSON column to listings so category-specific fields (e.g. jobs:
 * employment_type, salary_min/max, employer, requirements, experience_level)
 * can ride along without bloating the schema with columns the marketplace
 * never reads.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('photo_url');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
