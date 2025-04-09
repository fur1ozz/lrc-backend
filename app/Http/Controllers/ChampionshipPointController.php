<?php

namespace App\Http\Controllers;

use App\Models\ChampionshipClass;
use App\Models\ChampionshipPoint;
use App\Models\Crew;
use App\Models\CrewClassInvolvement;
use App\Models\GroupClass;
use App\Models\OverallResult;
use App\Models\Participant;
use App\Models\Rally;
use App\Models\Season;

class ChampionshipPointController extends Controller
{
    public function getChampionshipPointsBySeasonYearAndClassName($seasonYear, $className)
    {
        $season = Season::where('year', $seasonYear)->first();

        if (!$season) {
            return response()->json(['message' => 'Season not found'], 404);
        }
        $seasonId = $season->id;

        $groupClass = GroupClass::where('class_name', $className)->first();

        if (!$groupClass) {
            return response()->json(['message' => 'Class not found'], 404);
        }
        $classId = $groupClass->id;

        $championshipClass = ChampionshipClass::where('season_id', $seasonId)
            ->where('class_id', $classId)
            ->first();

        if (!$championshipClass) {
            return response()->json(['message' => 'Class not found for this season'], 404);
        }

        $championshipPoints = ChampionshipPoint::where('season_id', $seasonId)
            ->where('class_id', $classId)
            ->get();

        $allChampionshipClasses = ChampionshipClass::where('season_id', $seasonId)
            ->with(['class.group'])
            ->get()
            ->groupBy(fn ($champClass) => $champClass->class->group->group_name ?? 'Unknown')
            ->map(function ($groupedClasses) {
                return $groupedClasses->map(fn ($champClass) => $champClass->class->class_name)->unique()->values();
            })
            ->toArray();

        $seasonRallies = Rally::where('season_id', $seasonId)
            ->orderBy('date_from')
            ->get();

        $rallies = $seasonRallies->map(function ($rally) {
            return [
                'id' => $rally->id,
                'name' => $rally->rally_name,
                'date_from' => $rally->date_from,
                'date_to' => $rally->date_to,
            ];
        })->toArray();

        $response = [
            'season' => $seasonYear,
            'championship_classes' => $allChampionshipClasses,
            'rallies' => $rallies,
            'championship' => []
        ];

        $classResults = [];

        foreach ($championshipPoints as $point) {
            $driverId = $point->driver_id;

            // Check if the driver already exists in the results array for this class
            if (!isset($classResults[$classId])) {
                $classResults[$classId] = [
                    'class' => $className,
                    'crews' => []
                ];
            }

            // Check if the driver is already listed in the results for this class
            if (!isset($classResults[$classId]['crews'][$driverId])) {
                $driver = Participant::find($driverId);
                if (!$driver) {
                    continue;
                }

                $classResults[$classId]['crews'][$driverId] = [
                    'driver' => $driver->name . ' ' . $driver->surname,
                    'results' => [],
                    'total_points' => 0
                ];
            }

            $crew = Crew::find($point->crew_id);
            if ($crew) {
                $rally = Rally::find($crew->rally_id);
                if ($rally) {
                    $coDriver = $crew->coDriver->name . ' ' . $crew->coDriver->surname;

                    $classResults[$classId]['crews'][$driverId]['results'][] = [
                        'rally_id' => $rally->id,
                        'rally' => $rally->rally_name,
                        'co_driver' => $coDriver,
                        'points' => $point->points,
                        'place' => $point->position,
                        'power_stage' => $point->power_stage,
                        'total_points' => $point->points + $point->power_stage,
                    ];

                    $totalPointsForRally = $point->points + $point->power_stage;
                    $classResults[$classId]['crews'][$driverId]['total_points'] += $totalPointsForRally;
                }
            }
        }


        usort($classResults[$classId]['crews'], function($a, $b) {
            return $b['total_points'] - $a['total_points'];
        });

        foreach ($classResults as $classResult) {
            $response['championship'][] = $classResult;
        }

        return response()->json($response);
    }

    public function getCrewClassPointsBySeason($seasonId)
    {
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

        $data = [];

        $rallies = Rally::where('season_id', $seasonId)->get();

        foreach ($rallies as $rally) {
            $crews = Crew::where('rally_id', $rally->id)->get();

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

                    foreach ($involvements as $involvement) {
                        $classId = $involvement->class_id;
                        if (!isset($rallyClassGroups[$classId])) {
                            $rallyClassGroups[$classId] = [
                                'class_id' => $classId,
                                'class_name' => ChampionshipClass::find($classId)->class->class_name,
                                'crews' => []
                            ];
                        }

                        $rallyClassGroups[$classId]['crews'][] = [
                            'crew_id' => $crew->id,
                            'crew_number' => $crew->crew_number,
                            'driver_id' => $crew->driver_id,
                            'total_time' => $totalTime,
                        ];
                    }
                }
            }

            ksort($rallyClassGroups);

            foreach ($rallyClassGroups as $classId => &$classGroup) {
                usort($classGroup['crews'], function ($a, $b) {
                    return $a['total_time'] <=> $b['total_time'];
                });
            }

            $data[] = [
                'rally_id' => $rally->id,
                'rally_name' => $rally->rally_name,
                'class_groups' => array_values($rallyClassGroups)
            ];
        }

        return response()->json($data);
    }

}
