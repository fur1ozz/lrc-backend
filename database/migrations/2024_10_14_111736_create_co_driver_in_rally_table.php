<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('co_driver_in_rallies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('co_driver_id');
            $table->unsignedBigInteger('rally_id');
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('participants')->onDelete('cascade');
            $table->foreign('co_driver_id')->references('id')->on('participants')->onDelete('cascade');
            $table->foreign('rally_id')->references('id')->on('rallies')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('co_driver_in_rally');
    }
};
