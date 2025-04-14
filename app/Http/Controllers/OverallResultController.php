<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\OverallResult;
use App\Models\Participant;
use App\Models\Penalties;
use App\Models\Rally;
use App\Models\RallyClass;
use App\Models\Stage;
use App\Models\StageResults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OverallResultController extends Controller
{

    public function getOverallResultsBySeasonYearAndRallyTag($seasonYear, $rallyTag, $classId = 'all')
    {
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $stageCount = Stage::where('rally_id', $rally->id)->count();

        if ($classId !== 'all') {
            $classExistsInRally = DB::table('rally_classes')
                ->where('rally_id', $rally->id)
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

            $overallResults = OverallResult::where('rally_id', $rally->id)
                ->whereIn('crew_id', $filteredCrewIds)
                ->get();
        } else {
            $overallResults = OverallResult::where('rally_id', $rally->id)->get();
        }

        $sortedResults = $overallResults->sortBy('total_time')->values();

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
            'rally_id' => $rally->id,
            'rally_name' => $rally->rally_name,
            'season_year' => $seasonYear,
            'stage_count' => $stageCount,
            'rally_classes' => $rallyClasses,
            'overall_results' => $sortedResults->map(function ($result, $index) use ($sortedResults) {
                $crew = Crew::with('team')->find($result->crew_id);

                if (!$crew) {
                    return null;
                }

                $driver = Participant::find($crew->driver_id);
                $coDriver = Participant::find($crew->co_driver_id);

                $totalPenalties = Penalties::where('crew_id', $crew->id)->sum('penalty_amount');
                $formattedTotalPenalties = $totalPenalties > 0 ? lrc_formatMillisecondsTwoDigits($totalPenalties) : '';

                $totalTimeMs = $result->total_time;
                $firstTimeMs = $sortedResults->first()->total_time ?? null;
                $previousTimeMs = $index > 0 ? $sortedResults[$index - 1]->total_time : null;

                $difFromFirst = $index === 0 ? '-' : '+' . lrc_formatMillisecondsAdaptive($totalTimeMs - $firstTimeMs);
                $difFromPrevious = $index === 0 ? '-' : '+' . lrc_formatMillisecondsAdaptive($totalTimeMs - $previousTimeMs);

                return [
                    'crew_id' => $crew->id,
                    'crew_number' => $crew->crew_number,
                    'car' => $crew->car,
                    'drive_type' => $crew->drive_type,
                    'drive_class' => $crew->drive_class,
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
                    'team' => $crew->team ? [
                        'id' => $crew->team->id,
                        'name' => $crew->team->team_name,
                    ] : null,
                    'total_time' => lrc_formatMillisecondsTwoDigits($result->total_time),
                    'total_penalty_time' => $formattedTotalPenalties,
                    'dif_from_first' => $difFromFirst,
                    'dif_from_previous' => $difFromPrevious,
                ];
            })->filter()->values(),
        ];

        return response()->json($response);
    }


    public function calculateOverallResults($rallyId)
    {
        $rally = Rally::where('id', $rallyId)->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found'], 404);
        }

        $stages = Stage::where('rally_id', $rallyId)->orderBy('stage_number')->get();

        $hasStageResults = StageResults::whereIn('stage_id', $stages->pluck('id'))->exists();

        if (!$hasStageResults) {
            return response()->json(['message' => 'No stage results available to calculate overall results'], 404);
        }

        $crews = Crew::whereHas('stageResults', function ($query) use ($rallyId) {
            $query->whereHas('stage', function ($stageQuery) use ($rallyId) {
                $stageQuery->where('rally_id', $rallyId);
            });
        })->get();

        foreach ($crews as $crew) {
            $totalTime = 0;
            $totalPenalties = 0;

            foreach ($stages as $stage) {
                $stageResult = $crew->stageResults()->where('stage_id', $stage->id)->first();

                if ($stageResult) {
                    $timeTaken = $stageResult->time_taken;
                    $totalTime += $timeTaken;

                    $penalties = Penalties::where('crew_id', $crew->id)
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

            OverallResult::updateOrCreate(
                [
                    'crew_id' => $crew->id,
                    'rally_id' => $rallyId,
                ],
                [
                    'total_time' => $totalTimeWithPenalties,
                ]
            );
        }

        return response()->json(['message' => 'Overall results calculated and saved successfully.']);
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(OverallResult $overallResult)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OverallResult $overallResult)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OverallResult $overallResult)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OverallResult $overallResult)
    {
        //
    }
}
