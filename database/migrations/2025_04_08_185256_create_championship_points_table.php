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
        Schema::create('championship_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('group_classes')->onDelete('cascade');
            $table->foreignId('crew_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('participants')->onDelete('cascade');
            $table->integer('points')->nullable();
            $table->integer('power_stage')->nullable();
            $table->integer('position')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('championship_points');
    }
};
