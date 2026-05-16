<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->json('photos')->nullable()->after('photo_url');
            $table->json('features')->nullable()->after('photos');
            $table->unsignedTinyInteger('capacity')->nullable()->after('features');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['photos', 'features', 'capacity']);
        });
    }
};
