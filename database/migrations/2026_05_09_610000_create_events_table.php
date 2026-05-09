<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();

            // wedding | concert | sports | community | religious | other
            $table->string('kind', 20)->default('community')->index();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('location', 200)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();

            $table->string('cover_url', 255)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->unsignedInteger('attendees_count')->default(0);

            // active | cancelled | archived
            $table->string('status', 12)->default('active')->index();
            $table->timestamps();

            $table->index(['status', 'starts_at']);
            $table->index(['zone_id', 'starts_at']);
        });

        Schema::create('event_attendees', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->primary(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendees');
        Schema::dropIfExists('events');
    }
};
