<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * subtotal = list price, discount_total = voucher discount, total = amount actually paid.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->nullable()->after('code');
            $table->decimal('discount_total', 15, 2)->default(0)->after('subtotal');
            $table->string('voucher_code', 40)->nullable()->after('discount_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'discount_total', 'voucher_code']);
        });
    }
};
