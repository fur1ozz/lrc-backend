<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crew_id');
            $table->unsignedBigInteger('rally_id');
            $table->string('retirement_reason');
            $table->unsignedInteger('stage_of_retirement')->nullable();
            $table->timestamps();

            $table->foreign('crew_id')->references('id')->on('crews')->onDelete('cascade');
            $table->foreign('rally_id')->references('id')->on('rallies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retirements');
    }
};
