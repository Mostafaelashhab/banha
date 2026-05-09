<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat_threads', function (Blueprint $table) {
            $table->id();
            // Pair key for lookup: smaller_id-larger_id (so 1-1 chats are unique)
            $table->string('pair_key', 32)->nullable()->unique();
            $table->foreignId('last_message_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('last_message_preview', 200)->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('chat_thread_users', function (Blueprint $table) {
            $table->foreignId('thread_id')->constrained('chat_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->primary(['thread_id', 'user_id']);
            $table->index(['user_id', 'thread_id']);
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('chat_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['thread_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_thread_users');
        Schema::dropIfExists('chat_threads');
    }
};
