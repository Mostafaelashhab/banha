<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('promo_banners', function (Blueprint $t) {
            // Links a banner to a specific business. When set, the banner's
            // href is auto-resolved to that business's page and the legacy
            // overlay (title/tag/desc/cta) is hidden — image-only rendering.
            $t->foreignId('business_id')->nullable()->after('id')
              ->constrained('businesses')->nullOnDelete();
        });

        // Title was NOT NULL in the original schema, but image-only banners
        // don't need one. Relax it.
        Schema::table('promo_banners', function (Blueprint $t) {
            $t->string('title')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('promo_banners', function (Blueprint $t) {
            $t->dropForeign(['business_id']);
            $t->dropColumn('business_id');
        });
        // We leave title as nullable on rollback — making it NOT NULL again
        // could fail if image-only banners exist.
    }
};
