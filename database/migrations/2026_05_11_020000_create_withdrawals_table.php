<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cash-out queue. Every withdrawal is admin-approved manually — there is no
 * automatic payout path. This is the single biggest fraud safeguard.
 *
 * Lifecycle:
 *   pending  → admin reviews
 *     → approved → operator sends real money via InstaPay/V-Cash, writes reference, status = paid
 *     → rejected → user notified, points returned (no deduction had happened)
 *     → cancelled → user backed out before review
 *
 * Points are NOT deducted at request time. They are RESERVED (the user can't
 * spend them again because available_balance = sum(deltas) - sum(pending_points)).
 * Actual point deduction happens when an admin approves, atomically inside
 * the same DB transaction as the status flip.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // What the user gets paid (in EGP) and what it costs from their balance
            $table->unsignedInteger('amount_egp');
            $table->unsignedInteger('points_cost');

            // Where to send the money
            $table->enum('method', ['instapay', 'vcash']);
            $table->string('payout_handle', 64);   // phone number or username/IBAN

            // Lifecycle
            $table->enum('status', ['pending', 'approved', 'paid', 'rejected', 'cancelled'])
                  ->default('pending')->index();

            // Admin processing
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->string('payout_reference', 64)->nullable();  // bank/op ref

            // Timing
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Audit context (IP, user-agent at request time, etc)
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
