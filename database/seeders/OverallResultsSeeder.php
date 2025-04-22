<?php

namespace Database\Seeders;

use App\Models\Crew;
use App\Models\OverallResult;
use App\Models\Penalties;
use App\Models\Rally;
use App\Models\Retirement;
use App\Models\StageResults;
use Illuminate\Database\Seeder;

class OverallResultsSeeder extends Seeder
{
    public function run(): void
    {
        $rallies = Rally::where('season_id', 1)->get();

        foreach ($rallies as $rally) {
            $crews = Crew::where('rally_id', $rally->id)->get();

            foreach ($crews as $crew) {
                $retirement = Retirement::where('crew_id', $crew->id)->where('rally_id', $rally->id)->first();

                if ($retirement) {
                    continue;
                }

                $stageResults = StageResults::where('crew_id', $crew->id)->get();

                $totalTime = 0;

                foreach ($stageResults as $stageResult) {
                    $totalTime += $stageResult->time_taken;
                }

                $penalties = Penalties::where('crew_id', $crew->id)->get();

                foreach ($penalties as $penalty) {
                    $totalTime += $penalty->penalty_amount;
                }

                OverallResult::create([
                    'crew_id' => $crew->id,
                    'rally_id' => $rally->id,
                    'total_time' => $totalTime,
                ]);
            }
        }
    }
}
