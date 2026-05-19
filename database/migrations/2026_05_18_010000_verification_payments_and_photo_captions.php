<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two unrelated craftsmen-tier features sharing a migration:
 *
 *   1) verification_payments — paid-verified badge purchases.
 *      Owner submits proof of payment (InstaPay / Vodafone Cash / cash)
 *      and admin approves → flips business.is_verified_paid for 1 year.
 *
 *   2) business_photos.caption — optional one-line caption per portfolio photo
 *      so craftsmen can label their work ("حمام بعد التشطيب", "أسقف جبس بورد").
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('verification_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('method', ['instapay', 'vodafone_cash', 'cash'])->index();
            $t->unsignedInteger('amount');                         // EGP
            $t->unsignedSmallInteger('months')->default(12);
            $t->string('transaction_id', 80)->nullable();          // for digital methods
            $t->string('proof_url', 500)->nullable();              // screenshot
            $t->string('note', 300)->nullable();
            $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $t->string('reviewed_by_admin', 60)->nullable();
            $t->dateTime('reviewed_at')->nullable();
            $t->string('reject_reason', 300)->nullable();
            $t->timestamps();
            $t->index(['business_id', 'status']);
            $t->index(['status', 'created_at']);
        });

        Schema::table('business_photos', function (Blueprint $t) {
            $t->string('caption', 120)->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('business_photos', function (Blueprint $t) {
            $t->dropColumn('caption');
        });
        Schema::dropIfExists('verification_payments');
    }
};
