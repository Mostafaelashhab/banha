<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * point_transactions — single source of truth for the user points system.
 *
 * Every points change (positive OR negative) is written here, atomically.
 * The UNIQUE(user_id, reason, target_type, target_id) constraint blocks
 * duplicate awards at the DB level — most abuse patterns die before the
 * service code even sees them.
 *
 * `users.reputation` is a cached SUM(delta) that the PointsService keeps
 * in sync inside the same transaction.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Positive for earnings, negative for spends/penalties
            $table->integer('delta');

            // Reason code — small enum-like string (signup, daily_login, post_upvoted, …)
            $table->string('reason', 40)->index();

            // Polymorphic target the points relate to (Post, Business, Listing, User…)
            // Null when the target is "the user themselves" (e.g. daily login).
            $table->string('target_type', 40)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();

            // For deferred awards (e.g. referral pending invitee proof). 0 = settled.
            $table->boolean('settled')->default(true);

            // Free-form context — IP, original counts, etc. For audit.
            $table->json('meta')->nullable();

            $table->timestamps();

            // ── The single most important constraint in this whole feature ──
            // Identical (user, reason, target) can never be rewarded twice.
            $table->unique(['user_id', 'reason', 'target_type', 'target_id'], 'pt_uniq_dedupe');

            // Common query indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['reason', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
