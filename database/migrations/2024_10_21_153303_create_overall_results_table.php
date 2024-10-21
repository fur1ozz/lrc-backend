<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('overall_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crew_id');
            $table->unsignedBigInteger('rally_id');
            $table->string('total_time', 12)->nullable();
            $table->timestamps();

            $table->foreign('crew_id')->references('id')->on('crews')->onDelete('cascade');
            $table->foreign('rally_id')->references('id')->on('rallies')->onDelete('cascade');

        });
    }
    public function down()
    {
        Schema::dropIfExists('overall_results');
    }
};
