<?php

namespace App\Http\Controllers;

use App\Models\StageResults;
use Illuminate\Http\Request;
use App\Models\Rally;
use App\Models\Stage;
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
            'results' => $results->map(function ($result) {
                return [
                    'crew_id' => $result->crew_id,
                    'crew_start_time' => $result->crew_start_time,
                    'time_taken' => $result->time_taken,
                    'avg_speed' => $result->avg_speed,
                ];
            }),
        ];

        return response()->json($response);
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
