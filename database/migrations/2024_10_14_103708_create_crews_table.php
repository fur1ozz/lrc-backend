<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrewsTable extends Migration
{
    public function up()
    {
        Schema::create('crews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('co_driver_id');
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('rally_id');
            $table->integer('crew_number_int');
            $table->boolean('is_historic')->default(false);
            $table->string('car');
            $table->enum('drive_type', ['AWD', 'FWD', 'RWD']);
            $table->string('drive_class');  // e.g., RC2, R5, WRC
            $table->timestamps();

            $table->foreign('driver_id')->references('id')->on('participants')->onDelete('cascade');
            $table->foreign('co_driver_id')->references('id')->on('participants')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('rally_id')->references('id')->on('rallies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('crews');
    }
}

