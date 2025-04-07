<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRalliesTable extends Migration
{
    public function up()
    {
        Schema::create('rallies', function (Blueprint $table) {
            $table->id();
            $table->string('rally_name');
            $table->date('date_from');
            $table->date('date_to');
            $table->string('location');
            $table->enum('road_surface', ['gravel', 'tarmac', 'snow'])->default('gravel');
            $table->string('rally_tag');
            $table->unsignedBigInteger('season_id');
            $table->string('rally_img')->nullable();
            $table->string('rally_banner')->nullable();
            $table->timestamps();

            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('rallies');
    }
}
