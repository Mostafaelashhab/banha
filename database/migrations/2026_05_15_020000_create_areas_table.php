<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $t) {
            $t->id();
            $t->string('name', 80);
            $t->string('slug', 90)->unique();
            // Free-text parent (city/markaz) — kept loose so it doesn't
            // depend on zones being seeded consistently across envs.
            $t->string('parent', 80)->default('بنها');
            // Lat/lng nullable — we have good coords for Banha city blocks
            // but only rough estimates for villages.
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();
            $t->unsignedSmallInteger('sort')->default(0);
            $t->timestamps();

            $t->index('parent');
            $t->index(['lat', 'lng']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
