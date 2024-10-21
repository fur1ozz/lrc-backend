<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\OverallResult;
use App\Models\Participant;
use App\Models\Penalties;
use App\Models\Rally;
use App\Models\Stage;
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
            $timeA = $this->convertTimeToSeconds($a->total_time);
            $timeB = $this->convertTimeToSeconds($b->total_time);
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
                    $penaltySum += $this->convertTimeToSeconds($penalty->penalty_amount);
                }

                $formattedTotalPenalties = $penaltySum > 0 ? $this->convertSecondsToTime($penaltySum) : '';

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
                    'total_time' => $result->total_time,
                    'total_penalty_time' => $formattedTotalPenalties,
                ];
            })->values(),
        ];

        return response()->json($response);
    }


    public function calculateOverallResults($rallyId)
    {
        $stages = Stage::where('rally_id', $rallyId)->orderBy('stage_number')->get();

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
                    $timeTakenInSeconds = $this->convertTimeToSeconds($stageResult->time_taken);
                    $totalTime += $timeTakenInSeconds;

                    $penalties = Penalties::where('crew_id', $crew->id)
                        ->where('stage_id', $stage->id)
                        ->get();

                    $penaltyTime = 0;
                    foreach ($penalties as $penalty) {
                        $penaltyTime += $this->convertTimeToSeconds($penalty->penalty_amount);
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
                    'total_time' => $this->convertSecondsToTime($totalTimeWithPenalties),
                ]
            );
        }

        return response()->json(['message' => 'Overall results calculated and saved successfully.']);
    }



    private function convertTimeToSeconds($time)
    {
        $parts = explode(':', $time);
        $seconds = 0;

        // Assuming the format is MM:SS.mm or SS.mm
        if (count($parts) == 2) {
            // MM:SS.mm
            $seconds += $parts[0] * 60; // Convert minutes to seconds

            // Check if milliseconds are present
            if (strpos($parts[1], '.') !== false) {
                $subParts = explode('.', $parts[1]);
                $seconds += (float)$subParts[0]; // Add seconds
                $milliseconds = isset($subParts[1]) ? (float)$subParts[1] : 0; // Add milliseconds if present
                $seconds += $milliseconds / 1000; // Convert milliseconds to seconds
            } else {
                $seconds += (float)$parts[1]; // Add seconds
            }
        } elseif (count($parts) == 1) {
            // Just SS.mm
            if (strpos($parts[0], '.') !== false) {
                $subParts = explode('.', $parts[0]);
                $seconds += (float)$subParts[0]; // Add seconds
                $milliseconds = isset($subParts[1]) ? (float)$subParts[1] : 0; // Add milliseconds if present
                $seconds += $milliseconds / 1000; // Convert milliseconds to seconds
            } else {
                $seconds += (float)$parts[0]; // Just seconds
            }
        }

        return $seconds;
    }

    /**
     * Convert seconds to "MM:SS.mm" format.
     */
    private function convertSecondsToTime($seconds)
    {
        $milliseconds = ($seconds - floor($seconds)) * 1000; // Get milliseconds
        $seconds = floor($seconds);
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        // Return formatted time
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d.%02d', $hours, $remainingMinutes, $remainingSeconds, $milliseconds);
        } else {
            return sprintf('%02d:%02d.%02d', $remainingMinutes, $remainingSeconds, $milliseconds);
        }
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
