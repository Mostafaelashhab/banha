<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();

            // sale | buy | lost | found
            $table->string('kind', 12)->index();
            // electronics, furniture, mobile, clothing, vehicles, services, etc.
            $table->string('category', 30)->index();

            $table->string('title', 120);
            $table->text('description')->nullable();

            // Price (only for sale/buy). NULL or 0 = "بسعر مفاوض" / negotiable
            $table->unsignedInteger('price')->nullable();
            $table->string('currency', 3)->default('EGP');
            $table->boolean('negotiable')->default(true);

            // Single primary photo (we keep storage tight)
            $table->string('photo_url', 255)->nullable();

            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_whatsapp', 20)->nullable();

            // active | sold | expired | removed
            $table->string('status', 12)->default('active')->index();

            $table->unsignedInteger('views')->default(0);
            $table->unsignedTinyInteger('flag_count')->default(0);

            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['kind', 'status', 'created_at']);
            $table->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
