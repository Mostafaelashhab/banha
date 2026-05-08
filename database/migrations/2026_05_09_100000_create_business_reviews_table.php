<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_name', 60)->nullable();
            $table->string('author_phone', 20)->nullable();
            $table->unsignedTinyInteger('rating')->default(0);
            $table->text('body')->nullable();
            $table->string('source', 20)->default('user');
            $table->string('external_id', 64)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'reviewed_at']);
            $table->unique(['business_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_reviews');
    }
};
