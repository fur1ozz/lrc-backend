<?php

namespace Database\Seeders;

use App\Models\Rally;
use App\Models\Stage;
use App\Models\Crew;
use App\Models\StageResults;
use Illuminate\Database\Seeder;

class StageResultsSeeder extends Seeder
{
    public function run()
    {
        $rallies = Rally::where('season_id', 1)->get();

        foreach ($rallies as $rally) {
            $stages = Stage::where('rally_id', $rally->id)->get();

            $crews = Crew::where('rally_id', $rally->id)->get();

            foreach ($crews as $crew) {
                foreach ($stages as $stage) {
                    $distance = $stage->distance_km;

                    // random speed in km/h, rounded to 2 decimal places
                    $speed = round(rand(6000, 15000) / 100, 2);

                    // time in hours
                    $time_taken_hours = $distance / $speed;

                    // Convert hours to milliseconds
                    $time_taken_milliseconds = round($time_taken_hours * 3600000);

                    StageResults::create([
                        'crew_id' => $crew->id,
                        'stage_id' => $stage->id,
                        'time_taken' => $time_taken_milliseconds,
                        'avg_speed' => $speed,
                    ]);
                }
            }
        }
    }
}
