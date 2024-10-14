<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStagesTable extends Migration
{
    public function up()
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rally_id')->constrained()->onDelete('cascade');
            $table->string('stage_name');
            $table->integer('stage_number');
            $table->float('distance_km');
            $table->date('start_date');
            $table->time('start_time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stages');
    }
}
