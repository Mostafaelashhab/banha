<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Businesses: paid promotion + analytics counters
        Schema::table('businesses', function (Blueprint $table) {
            $table->timestamp('promoted_until')->nullable()->after('is_verified')->index();
            $table->unsignedInteger('views_count')->default(0)->after('ratings_count');
            $table->unsignedInteger('phone_clicks')->default(0)->after('views_count');
            $table->unsignedInteger('whatsapp_clicks')->default(0)->after('phone_clicks');
        });

        // Listings: featured + simple view counter (already has views, add featured)
        Schema::table('listings', function (Blueprint $table) {
            $table->timestamp('featured_until')->nullable()->after('status')->index();
            $table->unsignedInteger('phone_clicks')->default(0)->after('views');
            $table->unsignedInteger('whatsapp_clicks')->default(0)->after('phone_clicks');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['promoted_until', 'views_count', 'phone_clicks', 'whatsapp_clicks']);
        });
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['featured_until', 'phone_clicks', 'whatsapp_clicks']);
        });
    }
};
