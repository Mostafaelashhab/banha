<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Restaurant-side: per-area delivery fee map ──
        // Stored as JSON `{ "<area_id>": fee, ... }`. Empty/null = no delivery.
        Schema::table('businesses', function (Blueprint $t) {
            $t->json('delivery_fees')->nullable()->after('menu_currency');
            $t->unsignedSmallInteger('delivery_min_order')->default(0)->after('delivery_fees');
        });

        // ── Customer-side: remember preferred area on the user ──
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('default_area_id')->nullable()->after('avatar_url')
              ->constrained('areas')->nullOnDelete();
        });

        // ── Orders: which area + how much delivery cost ──
        Schema::table('orders', function (Blueprint $t) {
            $t->foreignId('area_id')->nullable()->after('customer_address')
              ->constrained('areas')->nullOnDelete();
            $t->decimal('delivery_fee', 8, 2)->default(0)->after('area_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            $t->dropForeign(['area_id']);
            $t->dropColumn(['area_id', 'delivery_fee']);
        });
        Schema::table('users', function (Blueprint $t) {
            $t->dropForeign(['default_area_id']);
            $t->dropColumn('default_area_id');
        });
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropColumn(['delivery_fees', 'delivery_min_order']);
        });
    }
};
