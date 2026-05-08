<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->string('governorate', 50)->default('القليوبية');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 15)->unique();
            $table->string('username', 40)->unique();
            $table->string('password');
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('avatar_seed', 60);
            $table->string('persona', 20)->nullable();
            $table->unsignedInteger('reputation')->default(50);
            $table->unsignedTinyInteger('level')->default(1);
            $table->boolean('is_banned')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->rememberToken();
            $table->timestamps();

            $table->index('zone_id');
            $table->index('reputation');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('zones');
    }
};
