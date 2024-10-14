<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prev_winners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rally_id');
            $table->unsignedBigInteger('crew_id');
            $table->text('feedback');
            $table->string('winning_img')->nullable();
            $table->timestamps();

            $table->foreign('rally_id')->references('id')->on('rallies')->onDelete('cascade');
            $table->foreign('crew_id')->references('id')->on('crews')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('prev_winners');
    }
};
