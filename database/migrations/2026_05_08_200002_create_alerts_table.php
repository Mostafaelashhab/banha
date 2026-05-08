<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20)->index();
            $table->string('description', 280);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->unsignedInteger('confirmations')->default(1);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index(['zone_id', 'is_resolved', 'expires_at']);
        });

        Schema::create('alert_confirmations', function (Blueprint $table) {
            $table->foreignId('alert_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['alert_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_confirmations');
        Schema::dropIfExists('alerts');
    }
};
