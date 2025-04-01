<?php

namespace Database\Seeders;

use App\Models\Crew;
use App\Models\Rally;
use App\Models\Stage;
use App\Models\StartTime;
use Illuminate\Database\Seeder;

class StartTimeSeeder extends Seeder
{
    public function run(): void
    {
        $rallies = Rally::where('season_id', 1)->get();

        foreach ($rallies as $rally) {
            $stages = Stage::where('rally_id', $rally->id)->get();
            $crews = Crew::where('rally_id', $rally->id)->get();

            foreach ($stages as $stage) {
                $startTime = \Carbon\Carbon::parse($stage->start_time);

                foreach ($crews as $crew) {
                    StartTime::create([
                        'crew_id' => $crew->id,
                        'stage_id' => $stage->id,
                        'start_time' => $startTime->format('H:i:s'),
                    ]);

                    // Add 1 minute to the start time for the next crew
                    $startTime->addMinute();
                }
            }
        }
    }
}
