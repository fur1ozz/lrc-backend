<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{ChampionshipPoint,
    ChampionshipClass,
    Rally,
    Crew,
    Stage,
    OverallResult,
    StageResults,
    CrewClassInvolvement};

class ChampionshipPointsSeeder extends Seeder
{
    public function run(): void
    {
        $seasonId = 1;

        // Truncate the table before seeding
        ChampionshipPoint::truncate();

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

        $classes = ChampionshipClass::where('season_id', $seasonId)->get();
        $classIds = $classes->pluck('class_id')->toArray();

        $rallies = Rally::where('season_id', $seasonId)->get();

        foreach ($rallies as $rally) {
            $crews = Crew::where('rally_id', $rally->id)->get();
            $lastStage = Stage::where('rally_id', $rally->id)->orderBy('stage_number', 'desc')->first();

            $rallyClassGroups = [];

            foreach ($crews as $crew) {
                $involvements = CrewClassInvolvement::where('crew_id', $crew->id)
                    ->whereIn('class_id', $classIds)
                    ->get();

                if ($involvements->isNotEmpty()) {
                    $overallResult = OverallResult::where('crew_id', $crew->id)
                        ->where('rally_id', $rally->id)
                        ->first();

                    $totalTime = $overallResult ? $overallResult->total_time : null;

                    $stageResult = StageResults::where('crew_id', $crew->id)
                        ->where('stage_id', $lastStage->id)
                        ->first();

                    $lastStageTime = $stageResult ? $stageResult->time_taken : null;

                    foreach ($involvements as $involvement) {
                        $classId = $involvement->class_id;

                        if (!isset($rallyClassGroups[$classId])) {
                            $rallyClassGroups[$classId] = [
                                'sorted_existing_total_times' => [],
                                'retired_crews' => [],
                            ];
                        }

                        $crewData = [
                            'crew_id' => $crew->id,
                            'driver_id' => $crew->driver_id,
                            'total_time' => $totalTime,
                            'last_stage_time' => $lastStageTime,
                            'power_stage' => null,
                        ];

                        if ($totalTime !== null) {
                            $rallyClassGroups[$classId]['sorted_existing_total_times'][] = $crewData;
                        } else {
                            $rallyClassGroups[$classId]['retired_crews'][] = $crewData;
                        }
                    }
                }
            }

            ksort($rallyClassGroups);

            foreach ($rallyClassGroups as $classId => &$classGroup) {
                usort($classGroup['sorted_existing_total_times'], fn($a, $b) => $a['total_time'] <=> $b['total_time']);

                foreach ($classGroup['sorted_existing_total_times'] as $index => &$crew) {
                    $crew['position'] = $index + 1;
                    $crew['points'] = $pointsSystem[$crew['position']] ?? 0;
                }

                foreach ($classGroup['retired_crews'] as &$crew) {
                    $crew['position'] = null;
                    $crew['points'] = null;
                }

                $allCrewsForStage = array_merge(
                    $classGroup['sorted_existing_total_times'],
                    $classGroup['retired_crews']
                );

                usort($allCrewsForStage, fn($a, $b) => $a['last_stage_time'] <=> $b['last_stage_time']);

                $powerStagePoints = [5, 3, 1];
                foreach ($allCrewsForStage as $i => $psCrew) {
                    if ($i >= 3 || $psCrew['last_stage_time'] === null) break;

                    foreach (['sorted_existing_total_times', 'retired_crews'] as $group) {
                        foreach ($classGroup[$group] as &$crew) {
                            if ($crew['crew_id'] === $psCrew['crew_id']) {
                                $crew['power_stage'] = $powerStagePoints[$i];
                                break 2;
                            }
                        }
                    }
                }

                // Save to DB
                foreach (['sorted_existing_total_times', 'retired_crews'] as $group) {
                    foreach ($classGroup[$group] as $crew) {
                        ChampionshipPoint::updateOrCreate([
                            'season_id' => $seasonId,
                            'class_id' => $classId,
                            'crew_id' => $crew['crew_id'],
                            'driver_id' => $crew['driver_id'],
                            'points' => $crew['points'],
                            'power_stage' => $crew['power_stage'],
                            'position' => $crew['position'],
                        ]);
                    }
                }
            }
        }
    }
}
