<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('category', 30)->index();    // craftsmen|food|medical|shops|services
            $table->string('sub_type', 30)->index();    // plumber, restaurant, doctor, etc.
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('description')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('address', 200)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->string('hours', 100)->nullable();
            $table->boolean('is_24h')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);

            $table->decimal('rating_avg', 2, 1)->default(0);
            $table->unsignedInteger('ratings_count')->default(0);

            $table->string('emoji', 8)->nullable();
            $table->string('photo_url', 255)->nullable();

            $table->timestamps();

            $table->index(['category', 'sub_type']);
            $table->index(['zone_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
