<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_sponsored')->default(false)->after('image_url');
            $table->boolean('is_announcement')->default(false)->after('is_sponsored');
            $table->index(['is_announcement', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['is_announcement', 'created_at']);
            $table->dropColumn(['is_sponsored', 'is_announcement']);
        });
    }
};
