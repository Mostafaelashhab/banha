<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_anonymous')->default(false);
            $table->string('anon_seed', 60)->nullable();
            $table->string('category', 30)->index();
            $table->string('title', 180)->nullable();
            $table->text('body');
            $table->integer('upvotes')->default(0);
            $table->integer('downvotes')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->double('hot_score')->default(0)->index();
            $table->string('status', 16)->default('active');
            $table->unsignedTinyInteger('flag_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['zone_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
