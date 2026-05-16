<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Per-click events: one row each time a user taps "اتصل" / "واتساب" /
        // "الاتجاهات" on a business page. Aggregated nightly into the
        // "popular times" histogram on the business show page.
        //
        // We store hour-of-day + day-of-week directly (denormalized) so the
        // aggregation query is a simple GROUP BY without any timestamp math.
        Schema::create('business_click_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->string('kind', 16);            // phone | whatsapp | directions | order | menu
            $t->unsignedTinyInteger('hour');   // 0-23
            $t->unsignedTinyInteger('dow');    // 0-6 (0=Sun, Cairo TZ)
            $t->timestamp('created_at')->useCurrent();

            $t->index(['business_id', 'hour']);
            $t->index(['business_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_click_events');
    }
};
