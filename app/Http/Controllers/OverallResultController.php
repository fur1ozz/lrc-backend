<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\OverallResult;
use App\Models\Participant;
use App\Models\Penalties;
use App\Models\Rally;
use App\Models\Stage;
use App\Models\StageResults;
use Illuminate\Http\Request;

class OverallResultController extends Controller
{

    public function getOverallResultsByRallyAndSeason($seasonYear, $rallyTag)
    {
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $stageCount = Stage::where('rally_id', $rally->id)->count();
        $overallResults = OverallResult::where('rally_id', $rally->id)->get();

        $sortedResults = $overallResults->sort(function ($a, $b) {
            $timeA = $a->total_time;
            $timeB = $b->total_time;
            return $timeA <=> $timeB;
        });

        $response = [
            'rally_id' => $rally->id,
            'rally_name' => $rally->rally_name,
            'season_year' => $seasonYear,
            'stage_count' => $stageCount,
            'overall_results' => $sortedResults->map(function ($result) {
                $crew = Crew::with('team')->find($result->crew_id);

                if (!$crew) {
                    return null;
                }

                $driver = Participant::find($crew->driver_id);
                $coDriver = Participant::find($crew->co_driver_id);

                $totalPenalties = Penalties::where('crew_id', $crew->id)->get();

                $penaltySum = 0;
                foreach ($totalPenalties as $penalty) {
                    $penaltySum += $penalty->penalty_amount;
                }

                $formattedTotalPenalties = $penaltySum > 0 ? lrc_formatMillisecondsTwoDigits($penaltySum) : '';

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
                ];
            })->values(),
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
