<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One commission per order (unique order_id), so an order can never pay commission twice.
     */
    public function up(): void
    {
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('order_total', 15, 2);
            $table->decimal('rate', 5, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->index(['referrer_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
