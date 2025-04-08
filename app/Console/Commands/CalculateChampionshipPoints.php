<?php

namespace App\Console\Commands;

use App\Models\ChampionshipPoint;
use App\Models\Crew;
use App\Models\OverallResult;
use App\Models\Participant;
use App\Models\Rally;
use App\Models\Retirement;
use App\Models\Stage;
use App\Models\StageResults;
use Illuminate\Console\Command;

class CalculateChampionshipPoints extends Command
{
    protected $signature = 'calculate:championship-points {rally_id}';
    protected $description = 'Calculate and assign championship points for a given rally based on the results';

    public function handle()
    {
        $rallyId = $this->argument('rally_id');

        $rally = Rally::findOrFail($rallyId);
        $seasonId = $rally->season_id;

        $crews = Crew::where('rally_id', $rallyId)->get();

        // First, handle the retired crews and set their points, power stage, and position to null
        $retiredCrews = Retirement::where('rally_id', $rallyId)->get();

        foreach ($retiredCrews as $retirement) {
            ChampionshipPoint::updateOrCreate(
                [
                    'season_id' => $seasonId,
                    'crew_id' => $retirement->crew_id,
                ],
                [
                    'points' => null,
                    'power_stage' => null,
                    'position' => null,
                    'driver_id' => $retirement->crew->driver_id,
                ]
            );
        }

        // Now, let's process the non-retired crews and assign points based on total_time
        $remainingCrews = OverallResult::where('rally_id', $rallyId)
            ->whereNotIn('crew_id', $retiredCrews->pluck('crew_id'))
            ->orderBy('total_time')
            ->get();

        $pointsSystem = [
            1 => 30,
            2 => 24,
            3 => 21,
            4 => 19,
            5 => 17,
            6 => 15,
            7 => 13,
            8 => 11,
            9 => 9,
            10 => 7,
            11 => 5,
            12 => 4,
            13 => 3,
            14 => 2,
            15 => 1
        ];

        $position = 1;
        foreach ($remainingCrews as $result) {
            $points = $pointsSystem[$position] ?? 0;

            ChampionshipPoint::updateOrCreate(
                [
                    'season_id' => $seasonId,
                    'crew_id' => $result->crew_id,
                ],
                [
                    'points' => $points,
                    'position' => $position,
                    'power_stage' => null, // Placeholder for now
                    'driver_id' => $result->crew->driver_id,
                ]
            );

            $position++;
        }

        // Find the last stage based on stage_number
        $lastStage = Stage::where('rally_id', $rallyId)
            ->orderByDesc('stage_number')
            ->first();

        if ($lastStage) {
            // Get the stage results and sort them by time_taken
            $stageResults = StageResults::where('stage_id', $lastStage->id)
                ->orderBy('time_taken')
                ->get();

            // Assign power stage points (top 5)
            $powerStagePoints = [5, 3, 1];
            $i = 0;
            foreach ($stageResults as $stageResult) {
                if ($i < 3) {
                    $points = $powerStagePoints[$i];

                    ChampionshipPoint::updateOrCreate(
                        [
                            'season_id' => $seasonId,
                            'crew_id' => $stageResult->crew_id,
                        ],
                        [
                            'power_stage' => $points,
                        ]
                    );
                }
                $i++;
            }
        }

        $this->info('Championship points have been calculated and assigned.');
    }
}
