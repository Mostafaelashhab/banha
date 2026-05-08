<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->string('tag', 60)->unique();
            $table->unsignedInteger('uses_count')->default(0);
            $table->timestamps();
            $table->index('uses_count');
        });

        Schema::create('hashtag_post', function (Blueprint $table) {
            $table->foreignId('hashtag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->primary(['hashtag_id', 'post_id']);
            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hashtag_post');
        Schema::dropIfExists('hashtags');
    }
};
