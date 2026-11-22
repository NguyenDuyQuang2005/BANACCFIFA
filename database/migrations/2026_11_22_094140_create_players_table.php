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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_season_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 120);
            $table->string('position', 5);
            $table->unsignedSmallInteger('ovr');
            $table->unsignedSmallInteger('pace')->nullable();
            $table->unsignedSmallInteger('stamina')->nullable();
            $table->unsignedTinyInteger('stars')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedTinyInteger('reset_odd_minute')->nullable();
            $table->unsignedTinyInteger('reset_even_minute')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['is_visible', 'ovr']);
            $table->index('name');
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
