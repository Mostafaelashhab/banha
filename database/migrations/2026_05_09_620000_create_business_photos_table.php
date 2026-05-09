<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('url', 255);
            $table->unsignedTinyInteger('sort')->default(0);
            $table->timestamps();
            $table->index(['business_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_photos');
    }
};
