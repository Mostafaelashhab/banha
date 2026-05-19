<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Craftsmen-section foundation:
 *  • Extra craftsman-specific columns on the `businesses` table
 *  • A new `job_requests` table for the two-sided marketplace
 *
 * Design notes:
 *  • `service_zones` is JSON because most craftsmen serve 2-3 zones; querying
 *    "plumbers serving Toukh" uses JSON_CONTAINS which is fine for our scale.
 *  • `is_verified_paid` is a separate flag from the admin `is_verified` so the
 *    paid badge can be revoked on subscription expiry without touching the
 *    admin-controlled trust signal.
 *  • `JobRequest.matched_business_id` is set when a craftsman is finally
 *    chosen — useful for analytics and for post-job reviews.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->unsignedSmallInteger('years_experience')->nullable()->after('booking_capacity');
            $t->json('service_zones')->nullable()->after('years_experience'); // [zone_id, ...]
            $t->boolean('accepts_emergency')->default(false)->after('service_zones');
            $t->unsignedInteger('min_callout_fee')->nullable()->after('accepts_emergency'); // EGP
            $t->boolean('is_verified_paid')->default(false)->after('min_callout_fee');
            $t->dateTime('verified_paid_until')->nullable()->after('is_verified_paid');
            $t->unsignedInteger('jobs_completed')->default(0)->after('verified_paid_until');
            $t->unsignedSmallInteger('avg_response_minutes')->nullable()->after('jobs_completed');
            $t->index(['category', 'is_verified_paid']);
            $t->index(['category', 'accepts_emergency']);
        });

        Schema::create('job_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name', 80);
            $t->string('phone', 20);
            $t->string('sub_type', 64);            // e.g. 'plumber'
            $t->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $t->string('address', 200)->nullable();
            $t->text('description');
            $t->unsignedInteger('budget_min')->nullable();
            $t->unsignedInteger('budget_max')->nullable();
            $t->enum('urgency', ['asap', 'today', 'this_week', 'flexible'])->default('today');
            $t->enum('status', ['open', 'matched', 'completed', 'cancelled', 'expired'])->default('open');
            $t->foreignId('matched_business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $t->unsignedSmallInteger('responses_count')->default(0);
            $t->unsignedSmallInteger('views_count')->default(0);
            $t->dateTime('expires_at')->nullable();
            $t->timestamps();

            $t->index(['sub_type', 'zone_id', 'status']);
            $t->index(['status', 'created_at']);
            $t->index('phone');
        });

        // Responses by craftsmen — lightweight, just track who saw + replied.
        Schema::create('job_responses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('job_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('quoted_price')->nullable();
            $t->string('note', 300)->nullable();
            $t->dateTime('contacted_at')->nullable();    // when they tapped WhatsApp/call
            $t->timestamps();
            $t->unique(['job_request_id', 'business_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_responses');
        Schema::dropIfExists('job_requests');
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropIndex(['category', 'is_verified_paid']);
            $t->dropIndex(['category', 'accepts_emergency']);
            $t->dropColumn([
                'years_experience', 'service_zones', 'accepts_emergency',
                'min_callout_fee', 'is_verified_paid', 'verified_paid_until',
                'jobs_completed', 'avg_response_minutes',
            ]);
        });
    }
};
