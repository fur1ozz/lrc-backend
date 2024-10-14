<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipantsTable extends Migration
{
    public function up()
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->text('desc')->nullable();
            $table->string('nationality', 2);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('participants');
    }
}

