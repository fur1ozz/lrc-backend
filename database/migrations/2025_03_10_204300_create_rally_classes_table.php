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
        Schema::create('rally_classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rally_id');
            $table->unsignedBigInteger('class_id');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('rally_id')->references('id')->on('rallies')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('group_classes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rally_classes');
    }
};
