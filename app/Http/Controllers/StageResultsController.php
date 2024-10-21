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
        $stageCount = Stage::where('rally_id', $rally->id)->count();

        $sortedResults = $results->sort(function ($a, $b) {
            $timeA = $this->convertTimeToSeconds($a->time_taken);
            $timeB = $this->convertTimeToSeconds($b->time_taken);
            return $timeA <=> $timeB;
        });

        $response = [
            'stage_id' => $stage->id,
            'stage_name' => $stage->stage_name,
            'stage_number' => $stage->stage_number,
            'stage_count' => $stageCount,
            'results' => $sortedResults->map(function ($result) use ($stage, $stageNumber, $rally) {
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

                $overallResult = $this->calculateOverallTimeAndPenalties($rally->id, $stageNumber, $crew->id);

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
                    'time_taken' => $result->time_taken,
                    'penalties' => $penaltyDetails->isNotEmpty() ? $penaltyDetails : null,
                    'overall_time_until_stage' => $overallResult['total_time'],
                    'overall_penalties_until_stage' => $overallResult['total_penalties'],
                    'overall_time_with_penalties_until_stage' => $overallResult['total_time_with_penalties'],
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
                $timeTakenInSeconds = $this->convertTimeToSeconds($stageResult->time_taken);
                $totalTime += $timeTakenInSeconds;

                $penalties = Penalties::where('crew_id', $crewId)
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

        return [
            'total_time' => $this->convertSecondsToTime($totalTime),
            'total_penalties' => $this->convertSecondsToTime($totalPenalties),
            'total_time_with_penalties' => $this->convertSecondsToTime($totalTimeWithPenalties),
        ];
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
}
