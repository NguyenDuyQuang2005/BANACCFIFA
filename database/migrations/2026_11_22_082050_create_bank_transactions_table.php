<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Every incoming bank transfer reported by the payment provider (SePay).
     * The unique (provider, provider_transaction_id) pair stops a webhook from being processed twice.
     */
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30);
            $table->string('provider_transaction_id', 100);
            $table->string('gateway', 60)->nullable();
            $table->string('account_number', 40)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('content', 500)->nullable();
            $table->string('reference_code', 120)->nullable();
            $table->timestamp('transacted_at')->nullable();
            $table->string('status', 20);
            $table->string('note')->nullable();
            $table->foreignId('deposit_id')->nullable()->constrained()->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_transaction_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
