<?php

namespace App\Http\Controllers;

use App\Models\RallyClass;
use App\Models\Retirement;
use App\Models\StageResults;
use Illuminate\Http\Request;
use App\Models\Rally;
use App\Models\Stage;
use App\Models\Crew;
use App\Models\Participant;
use App\Models\CrewGroupInvolvement;
use App\Models\Group;
use App\Models\Penalties;
use Illuminate\Support\Facades\DB;

class StageResultsController extends Controller
{
    public function getStageResultsBySeasonYearRallyTagAndStageNumber($seasonYear, $rallyTag, $stageNumber, $classId = 'all')
    {
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $stage = Stage::where('rally_id', $rally->id)
            ->where('stage_number', $stageNumber)
            ->first();

        if (!$stage) {
            return response()->json(['message' => 'No such stage exists'], 404);
        }

        $availableStageNumbers = Stage::where('rally_id', $rally->id)
            ->orderBy('stage_number')
            ->pluck('stage_number')
            ->toArray();

        $stageCount = Stage::where('rally_id', $rally->id)->count();

        if ($classId !== 'all') {
            $classExistsInRally = RallyClass::where('rally_id', $rally->id)
                ->where('class_id', $classId)
                ->exists();

            if (!$classExistsInRally) {
                return response()->json(['message' => 'Class not found in this rally'], 404);
            }

            $crewIds = Crew::where('rally_id', $rally->id)->pluck('id');

            $filteredCrewIds = DB::table('crew_class_involvements')
                ->whereIn('crew_id', $crewIds)
                ->where('class_id', $classId)
                ->pluck('crew_id');

            $results = StageResults::where('stage_id', $stage->id)
                ->whereIn('crew_id', $filteredCrewIds)
                ->get();
        } else {
            $results = StageResults::where('stage_id', $stage->id)->get();
        }

        $sortedResults = $results->sortBy('time_taken')->values();

        $rallyClasses = RallyClass::where('rally_id', $rally->id)
            ->with(['class.group'])
            ->get()
            ->groupBy(fn ($rallyClass) => $rallyClass->class->group->id ?? 0)
            ->map(function ($groupedClasses) {
                $first = $groupedClasses->first();

                return [
                    'group_id' => $first->class->group->id ?? null,
                    'group_name' => $first->class->group->group_name ?? 'Unknown',
                    'classes' => $groupedClasses->map(fn ($rallyClass) => [
                        'id' => $rallyClass->class->id,
                        'name' => $rallyClass->class->class_name,
                    ])->unique('id')->values(),
                ];
            })
            ->values();

        $response = [
            'stage_id' => $stage->id,
            'stage_name' => $stage->stage_name,
            'stage_distance' => $stage->distance_km,
            'stage_start_time' => $stage->start_time,
            'stage_number' => $stage->stage_number,
            'stage_count' => $stageCount,
            'available_stage_numbers' => $availableStageNumbers,
            'rally_classes' => $rallyClasses,
            'results' => $sortedResults->map(function ($result, $index) use ($stage, $stageNumber, $rally, $sortedResults) {
                $crew = Crew::find($result->crew_id);

                if (!$crew) {
                    return null;
                }

                $driver = Participant::find($crew->driver_id);
                $coDriver = Participant::find($crew->co_driver_id);

                $groupIds = CrewGroupInvolvement::where('crew_id', $crew->id)->pluck('group_id');
                $groups = Group::whereIn('id', $groupIds)->get();

                $penalties = Penalties::where('stage_id', $stage->id)
                    ->where('crew_id', $crew->id)
                    ->get();

                $penaltyDetails = $penalties->map(function ($penalty) {
                    return [
                        'penalty_reason' => $penalty->penalty_type,
                        'penalty_time' => lrc_formatMillisecondsTwoDigits($penalty->penalty_amount),
                    ];
                });

                $retirement = Retirement::where('crew_id', $crew->id)
                    ->where('rally_id', $rally->id)
                    ->first();

                $hasRetiredBeforeOrAtThisStage = $retirement && $retirement->stage_of_retirement <= $stageNumber;

                $overallResult = !$hasRetiredBeforeOrAtThisStage
                    ? $this->calculateOverallTimeAndPenalties($rally->id, $stageNumber, $crew->id)
                    : null;

                $timeTakenMs = $result->time_taken;
                $firstTimeMs = $sortedResults->first()->time_taken ?? null;

                $difFromFirst = $index === 0 ? '-' : '+' . lrc_formatMillisecondsAdaptive($timeTakenMs - $firstTimeMs);

                return [
                    'crew_id' => $crew->id,
                    'crew_number' => $crew->crew_number,
                    'car' => $crew->car,
                    'drive_type' => $crew->drive_type,
                    'driver' => [
                        'id' => $driver->id,
                        'name' => $driver->name,
                        'surname' => $driver->surname,
                        'nationality' => $driver->nationality,
                    ],
                    'co_driver' => $coDriver ? [
                        'id' => $coDriver->id,
                        'name' => $coDriver->name,
                        'surname' => $coDriver->surname,
                        'nationality' => $coDriver->nationality,
                    ] : null,
                    'groups' => $groups->map(function ($group) {
                        return [
                            'id' => $group->id,
                            'name' => $group->group_name,
                        ];
                    }),
                    'time_taken' => lrc_formatMillisecondsTwoDigits($result->time_taken),
                    'time_dif_from_first' => $difFromFirst,
                    'penalties' => $penaltyDetails->isNotEmpty() ? $penaltyDetails : null,
                    'overall_time_until_stage' => $overallResult['total_time'] ?? null,
                    'overall_penalties_until_stage' => $overallResult['total_penalties'] ?? null,
                    'overall_time_with_penalties_until_stage' => $overallResult['total_time_with_penalties'] ?? null,
                    'overall_time_with_penalties_until_stage_ms' => $overallResult['total_time_with_penalties_ms'] ?? null,
                ];
            })->values(),
        ];

        return response()->json($response);
    }

    private function calculateOverallTimeAndPenalties($rallyId, $stageNumber, $crewId)
    {
        $stages = Stage::where('rally_id', $rallyId)
            ->where('stage_number', '<=', $stageNumber)
            ->get();

        $totalTime = 0;
        $totalPenalties = 0;

        foreach ($stages as $stage) {
            $stageResult = StageResults::where('crew_id', $crewId)
                ->where('stage_id', $stage->id)
                ->first();

            if ($stageResult) {
                $timeTaken = $stageResult->time_taken;
                $totalTime += $timeTaken;

                $penalties = Penalties::where('crew_id', $crewId)
                    ->where('stage_id', $stage->id)
                    ->get();

                $penaltyTime = 0;
                foreach ($penalties as $penalty) {
                    $penaltyTime += $penalty->penalty_amount;
                }

                $totalPenalties += $penaltyTime;
            }
        }

        $totalTimeWithPenalties = $totalTime + $totalPenalties;

        return [
            'total_time' => lrc_formatMillisecondsTwoDigits($totalTime),
            'total_penalties' => lrc_formatMillisecondsTwoDigits($totalPenalties),
            'total_time_with_penalties' => lrc_formatMillisecondsTwoDigits($totalTimeWithPenalties),
            'total_time_with_penalties_ms' => $totalTimeWithPenalties,
        ];
    }
    public function getStageWinnerResultsBySeasonYearAndRallyTag($seasonYear, $rallyTag)
    {
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $stages = Stage::where('rally_id', $rally->id)->orderBy('stage_number')->get();

        $top3 = [];

        $stageResults = $stages->map(function ($stage) use (&$top3) {
            $topResults = StageResults::where('stage_id', $stage->id)
                ->orderBy('time_taken')
                ->with(['crew', 'crew.driver', 'crew.coDriver', 'crew.team'])
                ->limit(3)
                ->get();

            if ($topResults->isEmpty()) {
                return null;
            }

            $winnerResult = $topResults->first();
            $stageWinner = [
                'place' => 1,
                'crew_number' => $winnerResult->crew->crew_number,
                'driver' => $winnerResult->crew->driver->name . ' ' . $winnerResult->crew->driver->surname,
                'driver_nationality' => $winnerResult->crew->driver->nationality,
                'co_driver' => $winnerResult->crew->coDriver->name . ' ' . $winnerResult->crew->coDriver->surname,
                'co_driver_nationality' => $winnerResult->crew->coDriver->nationality,
                'team' => $winnerResult->crew->team->team_name,
                'vehicle' => $winnerResult->crew->car,
                'drive_type' => $winnerResult->crew->drive_type,
                'completion_time' => lrc_formatMillisecondsTwoDigits($winnerResult->time_taken),
                'average_speed_kmh' => $winnerResult->avg_speed,
            ];

            foreach ($topResults as $index => $result) {
                $crewId = $result->crew->id;

                if (!isset($top3[$crewId])) {
                    $top3[$crewId] = [
                        'crew_id' => $crewId,
                        'crew_number' => $result->crew->crew_number,
                        'driver' => $result->crew->driver->name . ' ' . $result->crew->driver->surname,
                        'driver_nationality' => $result->crew->driver->nationality,
                        'co_driver' => $result->crew->coDriver->name . ' ' . $result->crew->coDriver->surname,
                        'co_driver_nationality' => $result->crew->coDriver->nationality,
                        'team' => $result->crew->team->team_name,
                        'vehicle' => $result->crew->car,
                        'drive_type' => $result->crew->drive_type,
                        'total_stage_wins' => 0,
                        'total_second_places' => 0,
                        'total_third_places' => 0,
                    ];
                }

                if ($index == 0) {
                    $top3[$crewId]['total_stage_wins']++;
                } elseif ($index == 1) {
                    $top3[$crewId]['total_second_places']++;
                } elseif ($index == 2) {
                    $top3[$crewId]['total_third_places']++;
                }
            }

            return [
                'stage_number' => $stage->stage_number,
                'stage_name' => $stage->stage_name,
                'stage_distance' => $stage->distance_km,
                'stage_winner' => $stageWinner
            ];
        })->filter()->values();

        $top3Result = collect($top3)->sortByDesc(function ($item) {
            return $item['total_stage_wins'] * 3 + $item['total_second_places'] * 2 + $item['total_third_places'];
        })->values();

        $response = [
            'winner_results' => [
                'stages' => $stageResults,
                'top_3_result' => $top3Result,
            ]
        ];

        return response()->json($response);
    }
}
