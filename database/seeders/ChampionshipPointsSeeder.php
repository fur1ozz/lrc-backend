<?php

namespace Database\Seeders;

use App\Models\ChampionshipPoint;
use App\Models\Crew;
use App\Models\OverallResult;
use App\Models\Retirement;
use App\Models\Stage;
use App\Models\StageResults;
use App\Models\Season;
use App\Models\ChampionshipClass;
use App\Models\CrewClassInvolvement;
use Illuminate\Database\Seeder;

class ChampionshipPointsSeeder extends Seeder
{
    public function run()
    {
        $seasonId = 1;

        $classes = ChampionshipClass::where('season_id', $seasonId)->get();

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

        // Loop through each class
        foreach ($classes as $championshipClass) {
            // Get all crews involved in the current class
            $crewsInClass = CrewClassInvolvement::where('class_id', $championshipClass->class_id)
                ->get();

            // Process each crew in the class
            foreach ($crewsInClass as $crewInClass) {
                $crewId = $crewInClass->crew_id;
                $crew = Crew::findOrFail($crewId);
                $seasonId = $championshipClass->season_id;

                // Ensure we have a driver_id before proceeding
                $driverId = $crew->driver_id;

                // First, handle the retired crews and set their points, power stage, and position to null
                $retiredCrews = Retirement::where('crew_id', $crewId)->get();
                if ($retiredCrews->count() > 0) {
                    ChampionshipPoint::updateOrCreate(
                        [
                            'season_id' => $seasonId,
                            'crew_id' => $crewId,
                            'class_id' => $championshipClass->class_id, // Add class_id here for uniqueness
                        ],
                        [
                            'points' => null,
                            'power_stage' => null,
                            'position' => null,
                            'driver_id' => $driverId, // Ensure driver_id is always included
                            'class_id' => $championshipClass->class_id,
                        ]
                    );
                    continue; // Skip further processing for retired crews
                }

                // Process non-retired crews and assign points based on total_time
                $overallResults = OverallResult::where('crew_id', $crewId)
                    ->orderBy('total_time')
                    ->get();

                // Determine the position and points for the crew based on the overall result
                $position = 1;
                foreach ($overallResults as $result) {
                    $points = $pointsSystem[$position] ?? 0;

                    ChampionshipPoint::updateOrCreate(
                        [
                            'season_id' => $seasonId,
                            'crew_id' => $crewId,
                            'class_id' => $championshipClass->class_id, // Add class_id to make each record unique
                        ],
                        [
                            'points' => $points,
                            'position' => $position,
                            'power_stage' => null, // Placeholder for now
                            'driver_id' => $driverId, // Ensure driver_id is always included
                            'class_id' => $championshipClass->class_id,
                        ]
                    );
                    $position++;
                }

                // Find the last stage based on stage_number for the rally
                $lastStage = Stage::where('rally_id', $crew->rally_id)
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
                                    'class_id' => $championshipClass->class_id, // Add class_id here for uniqueness
                                ],
                                [
                                    'power_stage' => $points,
                                    'driver_id' => $driverId, // Ensure driver_id is always included
                                    'class_id' => $championshipClass->class_id,
                                ]
                            );
                        }
                        $i++;
                    }
                }
            }
        }
    }
}
