<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\Participant;
use App\Models\Rally;
use App\Models\Split;
use App\Models\SplitTime;
use App\Models\Stage;
use App\Models\StageResults;
use Illuminate\Http\Request;

class SplitTimeController extends Controller
{
    public function getCrewSplitTimesByStageId($seasonYear, $rallyTag, $stageNumber)
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

        $splits = Split::where('stage_id', $stage->id)
            ->select('id', 'split_number', 'split_distance')
            ->orderBy('split_number', 'asc')
            ->get();

        if ($splits->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No splits found for this stage. id-' . $stage->id,
            ], 404);
        }

        $splitIds = $splits->pluck('id');
        $splitTimes = SplitTime::whereIn('split_id', $splitIds)->get();

        if ($splitTimes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No split times found for this stage.',
            ], 404);
        }

        $stageResults = StageResults::where('stage_id', $stage->id)->get();

        $response = [];

        foreach ($splitTimes as $splitTime) {
            if (!isset($response[$splitTime->crew_id])) {
                $stageResult = $stageResults->firstWhere('crew_id', $splitTime->crew_id);

                $crew = Crew::with('team')->find($splitTime->crew_id);

                if (!$crew) {
                    return null;
                }

                $driver = Participant::find($crew->driver_id);
                $coDriver = Participant::find($crew->co_driver_id);

                $response[$splitTime->crew_id] = [
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
                    'stage_time' => $stageResult ? $stageResult->time_taken : null,
                    'splits' => []
                ];
            }

            $response[$splitTime->crew_id]['splits'][] = [
                'split_number' => $splitTime->split->split_number ?? null,
                'split_distance' => $splitTime->split->split_distance ?? null,
                'split_time' => $splitTime->split_time,
            ];
        }

        $responseData = [
            'splits' => $splits,
            'crew_times' => array_values($response),
        ];

        usort($responseData['crew_times'], function ($a, $b) {
            $aTime = $this->convertTimeToSeconds($a['stage_time']);
            $bTime = $this->convertTimeToSeconds($b['stage_time']);
            return $aTime - $bTime;
        });

        return response()->json([
            'success' => true,
            'splits' => $responseData['splits'],
            'crew_times' => $responseData['crew_times'],
        ]);
    }

    /**
     * Helper function to convert time string (mm:ss.milliseconds) to total seconds.
     *
     * @param string $time
     * @return float
     */
    private function convertTimeToSeconds($time)
    {
        if (!$time) {
            return PHP_INT_MAX; // If no stage time exists, push it to the end
        }

        // Split the time string into minutes and seconds.milliseconds
        $timeParts = explode(':', $time);
        $minutes = 0;
        $seconds = 0;
        $milliseconds = 0;

        if (count($timeParts) == 2) {
            // The format is mm:ss.milliseconds
            $minuteSecondParts = explode('.', $timeParts[1]);

            $minutes = (int)$timeParts[0];
            $seconds = (int)$minuteSecondParts[0];
            $milliseconds = (int)(isset($minuteSecondParts[1]) ? $minuteSecondParts[1] : 0);

            // Return total time in seconds, including the milliseconds as a fraction
            return $minutes * 60 + $seconds + ($milliseconds / 1000);
        }

        return 0; // Default return if the time format is incorrect
    }


    public function index()
    {
        $splitTimes = SplitTime::with(['crew', 'split'])->get();
        return response()->json($splitTimes);
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
