<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('original_price', 15, 2)->nullable();
            $table->decimal('bp', 12, 2)->nullable();
            $table->string('squad')->nullable();
            $table->unsignedSmallInteger('ovr')->nullable();
            $table->json('featured_players')->nullable();
            $table->string('status')->default('available');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('sold_at')->nullable();
            $table->text('credentials')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
            $table->index(['category_id', 'status']);
            $table->index('price');
            $table->index('bp');
            $table->index('ovr');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
