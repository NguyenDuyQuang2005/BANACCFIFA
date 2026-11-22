<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('affiliate_code', 16)->nullable()->unique()->after('role');
            $table->foreignId('referred_by_id')->nullable()->after('affiliate_code')->constrained('users')->nullOnDelete();
            $table->decimal('affiliate_rate', 5, 2)->nullable()->after('referred_by_id');
            $table->decimal('commission_balance', 15, 2)->default(0)->after('affiliate_rate');
            $table->unsignedInteger('affiliate_clicks')->default(0)->after('commission_balance');
        });

        DB::table('users')->whereNull('affiliate_code')->orderBy('id')->eachById(function (object $user): void {
            do {
                $code = Str::upper(Str::random(8));
            } while (DB::table('users')->where('affiliate_code', $code)->exists());

            DB::table('users')->where('id', $user->id)->update(['affiliate_code' => $code]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_id');
            $table->dropUnique(['affiliate_code']);
            $table->dropColumn(['affiliate_code', 'affiliate_rate', 'commission_balance', 'affiliate_clicks']);
        });
    }
};
