<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->unsignedTinyInteger('sort')->default(0);
            $table->timestamps();
            $table->index(['business_id', 'sort']);
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('menu_categories')->nullOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('photo_url', 255)->nullable();
            $table->boolean('is_available')->default(true);
            $table->unsignedTinyInteger('sort')->default(0);
            $table->timestamps();
            $table->index(['business_id', 'category_id', 'sort']);
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('has_menu')->default(false)->after('is_active')->index();
            $table->string('menu_currency', 3)->default('EGP')->after('has_menu');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['has_menu', 'menu_currency']);
        });
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menu_categories');
    }
};
