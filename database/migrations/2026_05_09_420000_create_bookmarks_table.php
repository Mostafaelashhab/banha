<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // post | business | listing
            $table->string('target_type', 16);
            $table->unsignedBigInteger('target_id');
            $table->timestamp('created_at')->nullable();

            $table->unique(['user_id', 'target_type', 'target_id']);
            $table->index(['user_id', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
