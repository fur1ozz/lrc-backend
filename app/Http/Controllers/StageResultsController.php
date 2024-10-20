<?php

namespace App\Http\Controllers;

use App\Models\StageResults;
use Illuminate\Http\Request;
use App\Models\Rally;
use App\Models\Stage;
use App\Models\Crew;
use App\Models\Participant;
use App\Models\CrewGroupInvolvement;
use App\Models\Group;
use App\Models\Penalties;

class StageResultsController extends Controller
{
    public function getStageResultsByRallyAndSeason($seasonYear, $rallyTag, $stageNumber)
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

        $results = StageResults::where('stage_id', $stage->id)->get();

        $response = [
            'stage_id' => $stage->id,
            'stage_name' => $stage->stage_name,
            'stage_number' => $stage->stage_number,
            'results' => $results->map(function ($result) use ($stage, $stageNumber, $rally) {
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
                        'penalty_time' => $penalty->penalty_amount,
                    ];
                });

                $previousStageResults = $this->getPreviousStagesForCrew($rally->id, $stageNumber, $crew->id);

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
                    'stage_result' => [
                        'crew_start_time' => $result->crew_start_time,
                        'time_taken' => $result->time_taken,
                        'avg_speed' => $result->avg_speed,
                    ],
                    'penalties' => $penaltyDetails->isNotEmpty() ? $penaltyDetails : null,
                    'previous_stage_times' => $previousStageResults // Include the previous stage times
                ];
            }),
        ];

        return response()->json($response);
    }

    private function getPreviousStagesForCrew($rallyId, $stageNumber, $crewId)
    {
        // Get all stages before the current stage number
        $previousStages = Stage::where('rally_id', $rallyId)
            ->where('stage_number', '<', $stageNumber)
            ->get();

        $previousResults = [];

        foreach ($previousStages as $stage) {
            $stageResult = StageResults::where('crew_id', $crewId)
                ->where('stage_id', $stage->id)
                ->first();

            if ($stageResult) {
                $previousResults[] = [
                    'stage_id' => $stage->id,
                    'stage_number' => $stage->stage_number,
                    'time_taken' => $stageResult->time_taken,
                ];
            }
        }

        return $previousResults;
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
    public function show(StageResults $stageResults)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StageResults $stageResults)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StageResults $stageResults)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StageResults $stageResults)
    {
        //
    }
}
