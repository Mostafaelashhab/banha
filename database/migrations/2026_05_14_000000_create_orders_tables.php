<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name', 80);
            $table->string('customer_phone', 20);
            $table->string('customer_address', 255)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->string('currency', 3)->default('EGP');
            // pending → confirmed → preparing → out_for_delivery → completed | cancelled
            $table->string('status', 20)->default('pending')->index();
            $table->string('wa_send_status', 16)->default('pending'); // pending|sent|failed|simulated
            $table->timestamp('wa_sent_at')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
            // Snapshot at order time — survives item edits/deletions
            $table->string('name', 120);
            $table->decimal('unit_price', 8, 2);
            $table->unsignedSmallInteger('qty');
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
