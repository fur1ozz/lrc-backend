<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rallies', function (Blueprint $table) {
            DB::statement("ALTER TABLE rallies MODIFY road_surface ENUM('gravel', 'tarmac', 'snow') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rallies', function (Blueprint $table) {
            $table->string('road_surface')->change();
        });
    }
};
