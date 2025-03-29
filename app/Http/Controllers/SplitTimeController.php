<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\Rally;
use App\Models\Split;
use App\Models\SplitTime;
use App\Models\Stage;
use App\Models\StageResults;
use Illuminate\Http\Request;

class SplitTimeController extends Controller
{
    public function getCrewSplitTimesBySeasonYearRallyTagAndStageNumber($seasonYear, $rallyTag, $stageNumber)
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
            return response()->json([
                'type' => "stage",
                'message' => 'No such stage exists',
                'splits' => [],
                'crew_times' => [],
            ]);
        }

        $totalStages = Stage::where('rally_id', $rally->id)->count();

        $splits = Split::where('stage_id', $stage->id)
            ->select('id', 'split_number', 'split_distance')
            ->orderBy('split_number', 'asc')
            ->get();

        $splitIds = $splits->pluck('id');
        $splitTimes = SplitTime::whereIn('split_id', $splitIds)->get();

        if ($splits->isEmpty() || $splitTimes->isEmpty()) {
            return response()->json([
                'splits' => $splits->isEmpty() ? [] : $splits,
                'crew_times' => [],
                'stage_count' => $totalStages,
            ]);
        }

        // Get the fastest crew based on stage time
        $fastestStageResult = StageResults::where('stage_id', $stage->id)
            ->orderBy('time_taken', 'asc')
            ->first();

        $fastestCrew = $fastestStageResult ? Crew::find($fastestStageResult->crew_id) : null;
        $fastestCrewSplitTimes = $fastestCrew
            ? SplitTime::where('crew_id', $fastestCrew->id)
                ->whereIn('split_id', $splitIds)
                ->get()
            : collect();

        $response = [];

        $crews = Crew::whereIn('id', $splitTimes->pluck('crew_id')->unique())
            ->with(['team', 'driver', 'coDriver'])
            ->get()
            ->keyBy('id');

        $stageResults = StageResults::whereIn('crew_id', $splitTimes->pluck('crew_id')->unique())
            ->where('stage_id', $stage->id)
            ->get()
            ->keyBy('crew_id');

        foreach ($splitTimes as $splitTime) {
            $crew = $crews[$splitTime->crew_id] ?? null;
            if (!$crew) continue;

            if (!isset($response[$splitTime->crew_id])) {
                $stageResult = $stageResults[$splitTime->crew_id] ?? null;

                $response[$splitTime->crew_id] = [
                    'crew_id' => $crew->id,
                    'crew_number' => $crew->crew_number,
                    'car' => $crew->car,
                    'drive_type' => $crew->drive_type,
                    'drive_class' => $crew->drive_class,
                    'driver' => [
                        'id' => $crew->driver->id,
                        'name' => $crew->driver->name,
                        'surname' => $crew->driver->surname,
                        'nationality' => $crew->driver->nationality,
                    ],
                    'co_driver' => $crew->coDriver ? [
                        'id' => $crew->coDriver->id,
                        'name' => $crew->coDriver->name,
                        'surname' => $crew->coDriver->surname,
                        'nationality' => $crew->coDriver->nationality,
                    ] : null,
                    'team' => $crew->team ? [
                        'id' => $crew->team->id,
                        'name' => $crew->team->team_name,
                    ] : null,
                    'stage_time_millis' => $stageResult ? $stageResult->time_taken : 0,
                    'stage_time' => $stageResult ? lrc_formatMillisecondsTwoDigits($stageResult->time_taken) : null,
                    'splits' => [],
                ];
            }

            $fastestSplitTime = $fastestCrewSplitTimes->firstWhere('split_id', $splitTime->split_id);
            $splitDifferenceMs = $fastestSplitTime ? ($splitTime->split_time - $fastestSplitTime->split_time) : null;
            $splitDifferenceFormatted = $splitDifferenceMs !== null
                ? ($splitTime->crew_id === $fastestCrew->id ? null : lrc_formatMillisecondsAdaptive(abs($splitDifferenceMs), 1))
                : null;

            $response[$splitTime->crew_id]['splits'][] = [
                'split_number' => $splitTime->split->split_number ?? null,
                'split_distance' => $splitTime->split->split_distance ?? null,
                'split_time' => lrc_formatMillisecondsShowMinutesAdaptive($splitTime->split_time, 1),
                'split_dif' => $splitDifferenceMs !== null ? (($splitDifferenceMs > 0) ? "+{$splitDifferenceFormatted}" : "-{$splitDifferenceFormatted}") : null,
                'split_dif_ms' => $splitDifferenceMs,
            ];
        }

        foreach ($response as $crewId => $data) {
            $stageTime = $data['stage_time_millis'];
            if ($stageTime) {
                $fastestStageTime = $fastestStageResult->time_taken;
                $stageTimeDiffMs = $stageTime - $fastestStageTime;
                $stageTimeDiffFormatted = lrc_formatMillisecondsAdaptive(abs($stageTimeDiffMs));

                $response[$crewId]['stage_time_dif'] = $crewId == $fastestCrew->id ? null : (($stageTimeDiffMs > 0) ? "+{$stageTimeDiffFormatted}" : "-{$stageTimeDiffFormatted}");
                $response[$crewId]['stage_time_dif_ms'] = $crewId == $fastestCrew->id ? null : $stageTimeDiffMs;
            }
        }

        usort($response, fn($a, $b) => ($a['stage_time_millis'] ?? PHP_INT_MAX) - ($b['stage_time_millis'] ?? PHP_INT_MAX));

        return response()->json([
            'splits' => $splits,
            'crew_times' => array_values($response),
            'stage_count' => $totalStages,
        ]);
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
    public function show(SplitTime $splitTime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SplitTime $splitTime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SplitTime $splitTime)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SplitTime $splitTime)
    {
        //
    }
}
