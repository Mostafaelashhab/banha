<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('question', 200);
            // Options stored as JSON array of strings (max 4)
            $table->json('options');
            $table->timestamp('closes_at')->nullable();
            $table->timestamps();
        });

        Schema::create('poll_votes', function (Blueprint $table) {
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('option_index');
            $table->timestamp('created_at')->nullable();

            $table->primary(['poll_id', 'user_id']);
            $table->index('poll_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('polls');
    }
};
