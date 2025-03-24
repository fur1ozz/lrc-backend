<?php

namespace Database\Seeders;

use App\Models\Split;
use App\Models\SplitTime;
use App\Models\Stage;
use App\Models\StageResults;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SplitTimesSeeder extends Seeder
{
    public function run()
    {
        $stageResults = StageResults::all();

        foreach ($stageResults as $stageResult) {
            $stage = Stage::where('id', $stageResult->stage_id)->first();
            $splits = Split::where('stage_id', $stage->id)->get();

            $totalTime = $stageResult->time_taken;
            $totalDistance = $stage->distance_km;

            foreach ($splits as $split) {
                $splitDistance = $split->split_distance;

                $splitTime = round(($splitDistance / $totalDistance) * $totalTime);

                SplitTime::create([
                    'crew_id' => $stageResult->crew_id,
                    'split_id' => $split->id,
                    'split_time' => $splitTime,
                ]);
            }
        }
    }
}
