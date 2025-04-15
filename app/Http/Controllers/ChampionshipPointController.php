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
use App\Models\Stage;
use App\Models\StageResults;

class ChampionshipPointController extends Controller
{
    public function getChampionshipPointsBySeasonYearAndClassName($seasonYear, $classId)
    {
        $season = Season::where('year', $seasonYear)->first();

        if (!$season) {
            return response()->json(['message' => 'Season not found'], 404);
        }
        $seasonId = $season->id;

        $groupClass = GroupClass::where('id', $classId)->first();

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
            ->groupBy(fn ($champClass) => $champClass->class->group->id ?? 0)
            ->map(function ($groupedClasses) {
                $first = $groupedClasses->first();

                return [
                    'group_id' => $first->class->group->id ?? null,
                    'group_name' => $first->class->group->group_name ?? 'Unknown',
                    'classes' => $groupedClasses->map(fn ($champClass) => [
                        'id' => $champClass->class->id,
                        'name' => $champClass->class->class_name,
                    ])->unique('id')->values(),
                ];
            })
            ->values();

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
        ];

        $classResults = [];

        foreach ($championshipPoints as $point) {
            $driverId = $point->driver_id;

            if (!isset($classResults[$classId])) {
                $classResults[$classId] = [
                    'class' => $groupClass->class_name,
                    'crews' => []
                ];
            }

            if (!isset($classResults[$classId]['crews'][$driverId])) {
                $driver = Participant::find($driverId);
                if (!$driver) {
                    continue;
                }

                $classResults[$classId]['crews'][$driverId] = [
                    'driver_id' => $driverId,
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

        $response['championship'] = reset($classResults);

        return response()->json($response);
    }
}
