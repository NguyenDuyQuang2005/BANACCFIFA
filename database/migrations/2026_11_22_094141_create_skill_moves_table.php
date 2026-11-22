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
        Schema::create('skill_moves', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->unsignedTinyInteger('level');
            $table->string('description')->nullable();
            $table->string('keyboard_keys', 120)->nullable();
            $table->string('controller_keys', 120)->nullable();
            $table->string('media_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['is_visible', 'level', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_moves');
    }
};
