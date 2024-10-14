<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('crew_group_involvements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crew_id');
            $table->unsignedBigInteger('group_id');
            $table->timestamps();

            $table->foreign('crew_id')->references('id')->on('crews')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('crew_group_involvements');
    }
};
