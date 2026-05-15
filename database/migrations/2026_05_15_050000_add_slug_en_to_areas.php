<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $t) {
            // English transliteration of the area name, used in SEO URLs:
            //   /cafes-in-{slug_en}-banha   /doctors-in-{slug_en}-banha   …
            // Nullable so a re-seed isn't required for old envs.
            $t->string('slug_en', 80)->nullable()->after('slug');
            $t->index('slug_en');
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $t) {
            $t->dropIndex(['slug_en']);
            $t->dropColumn('slug_en');
        });
    }
};
