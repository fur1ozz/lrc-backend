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
use Illuminate\Support\Facades\Log;

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
            ->orderByRaw("CAST(REPLACE(time_taken, ':', '') AS UNSIGNED) ASC")
            ->first();

        $fastestCrew = $fastestStageResult ? Crew::find($fastestStageResult->crew_id) : null;
        $fastestCrewSplitTimes = $fastestCrew
            ? SplitTime::where('crew_id', $fastestCrew->id)
                ->whereIn('split_id', $splitIds)
                ->get()
            : collect();

        $response = [];

        foreach ($splitTimes as $splitTime) {
            if (!isset($response[$splitTime->crew_id])) {
                $crew = Crew::with('team')->find($splitTime->crew_id);
                $stageResult = StageResults::where('crew_id', $splitTime->crew_id)
                    ->where('stage_id', $stage->id)
                    ->first();

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
                    'splits' => [],
                ];
            }

            $fastestSplitTime = $fastestCrewSplitTimes->firstWhere('split_id', $splitTime->split_id);

            $splitDifference = $fastestSplitTime
                ? ($splitTime->crew_id === $fastestCrew->id ? null : $this->calculateSplitDifference($splitTime->split_time, $fastestSplitTime->split_time))
                : null;

            $response[$splitTime->crew_id]['splits'][] = [
                'split_number' => $splitTime->split->split_number ?? null,
                'split_distance' => $splitTime->split->split_distance ?? null,
                'split_time' => $splitTime->split_time,
                'split_dif' => $splitDifference,
            ];
        }

        foreach ($response as $crewId => $data) {
            $stageTime = $data['stage_time'];
            if ($stageTime) {
                $fastestStageTime = $fastestStageResult->time_taken;
                $response[$crewId]['stage_time_dif'] = $crewId == $fastestCrew->id
                    ? null
                    : $this->calculateStageTimeDifference($stageTime, $fastestStageTime);
            }
        }

        $responseData = [
            'splits' => $splits,
            'crew_times' => array_values($response),
        ];

        usort($responseData['crew_times'], function ($a, $b) {
            if ($a['stage_time'] === null) return 1;
            if ($b['stage_time'] === null) return -1;

            $aTime = $this->convertStageTimeToSeconds($a['stage_time']);
            $bTime = $this->convertStageTimeToSeconds($b['stage_time']);
            return $aTime - $bTime;
        });

        return response()->json([
            'splits' => $responseData['splits'],
            'crew_times' => $responseData['crew_times'],
            'stage_count' => $totalStages,
        ]);
    }

    /**
     * Helper function to calculate the difference between two times in mm:ss.xx format.
     *
     * @param string $crewTime
     * @param string $fastestTime
     * @return string
     */
    private function calculateSplitDifference($crewTime, $fastestTime)
    {
        // Convert both times to seconds for easier comparison
        $crewTimeSeconds = $this->convertSplitTimeToSeconds($crewTime);
        $fastestTimeSeconds = $this->convertSplitTimeToSeconds($fastestTime);

        // Calculate the difference in seconds
        $differenceInSeconds = $crewTimeSeconds - $fastestTimeSeconds;

        // If the difference is greater than or equal to 60 seconds, display in +m:ss.x format
        if (abs($differenceInSeconds) >= 60) {
            $minutes = floor(abs($differenceInSeconds) / 60);
            $seconds = abs($differenceInSeconds) % 60;
            $milliseconds = round(($differenceInSeconds - floor($differenceInSeconds)) * 10);  // Get the 1-digit milliseconds part

            // Format the difference as +m:ss.x or -m:ss.x
            $formattedDifference = sprintf("%+d:%02d.%d", $minutes, floor($seconds), $milliseconds);
        } else {
            // Otherwise, return the difference in +ss.x format (for differences less than 1 minute)
            $formattedDifference = number_format($differenceInSeconds, 1);

            // Ensure the difference has a "+" sign for positive numbers
            if ($differenceInSeconds > 0) {
                $formattedDifference = "+" . $formattedDifference;
            }
        }

        return $formattedDifference;
    }

    private function calculateStageTimeDifference($crewTime, $fastestTime)
    {
        // Convert both times to seconds for easier comparison
        $crewTimeSeconds = $this->convertStageTimeToSeconds($crewTime);
        $fastestTimeSeconds = $this->convertStageTimeToSeconds($fastestTime);

        // Calculate the difference in seconds
        $differenceInSeconds = $crewTimeSeconds - $fastestTimeSeconds;

        // If the difference is greater than or equal to 60 seconds, display in +m:ss.xx format
        if (abs($differenceInSeconds) >= 60) {
            $minutes = floor(abs($differenceInSeconds) / 60);
            $seconds = abs($differenceInSeconds) % 60;
            $milliseconds = round(($differenceInSeconds - floor($differenceInSeconds)) * 100);  // Get the 2-digit milliseconds part

            // Format the difference as +m:ss.xx or -m:ss.xx
            $formattedDifference = sprintf("%+d:%02d.%02d", $minutes, floor($seconds), $milliseconds);
        } else {
            // Otherwise, return the difference in +ss.xx format (for differences less than 1 minute)
            $formattedDifference = number_format($differenceInSeconds, 2);  // Format with 2 decimal places for milliseconds

            // Ensure the difference has a "+" sign for positive numbers
            if ($differenceInSeconds > 0) {
                $formattedDifference = "+" . $formattedDifference;
            }
        }

        return $formattedDifference;
    }


    /**
     * Helper function to convert time string (mm:ss.milliseconds) to total seconds.
     *
     * @param string $time
     * @return float
     */
    private function convertSplitTimeToSeconds($time)
    {
        if (!$time) {
            return PHP_INT_MAX; // If no time exists, push it to the end
        }

        // Split the time string into minutes and seconds.milliseconds
        $timeParts = explode(':', $time);
        $minutes = 0;
        $seconds = 0;
        $milliseconds = 0;

        if (count($timeParts) == 2) {
            // The format is mm:ss.x
            $minuteSecondParts = explode('.', $timeParts[1]);

            $minutes = (int)$timeParts[0];
            $seconds = (int)$minuteSecondParts[0];

            // Handle the milliseconds properly as a single digit
            $milliseconds = (isset($minuteSecondParts[1]) ? (int)$minuteSecondParts[1] : 0);

            // Return total time in seconds, including the milliseconds as a fraction
            return $minutes * 60 + $seconds + ($milliseconds / 10); // Divide by 10 to get 0-1 decimal
        }

        return 0; // Default return if the time format is incorrect
    }
    private function convertStageTimeToSeconds($time)
    {
        if (!$time) {
            return PHP_INT_MAX; // If no time exists, push it to the end
        }

        // Split the time string into minutes and seconds.milliseconds
        $timeParts = explode(':', $time);
        $minutes = 0;
        $seconds = 0;
        $milliseconds = 0;

        if (count($timeParts) == 2) {
            // The format is mm:ss.xx
            $minuteSecondParts = explode('.', $timeParts[1]);

            $minutes = (int)$timeParts[0];
            $seconds = (int)$minuteSecondParts[0];

            // Handle the milliseconds properly as two digits
            $milliseconds = (isset($minuteSecondParts[1]) ? (int)$minuteSecondParts[1] : 0);

            // Return total time in seconds, including the milliseconds as a fraction
            return $minutes * 60 + $seconds + ($milliseconds / 100); // Divide by 100 to get 0-1 decimal
        }

        return 0; // Default return if the time format is incorrect
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
