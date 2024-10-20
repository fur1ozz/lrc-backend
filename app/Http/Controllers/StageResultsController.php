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

                // Get overall results until the current stage (including penalties)
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
                    'stage_result' => [
                        'crew_start_time' => $result->crew_start_time,
                        'time_taken' => $result->time_taken,
                        'avg_speed' => $result->avg_speed,
                    ],
                    'penalties' => $penaltyDetails->isNotEmpty() ? $penaltyDetails : null,
                    'overall_time_until_stage' => $overallResult['total_time'],
                    'overall_penalties_until_stage' => $overallResult['total_penalties'],
                    'overall_time_with_penalties_until_stage' => $overallResult['total_time_with_penalties'],
//                    'previous_stage_times' => $overallResult['previous_stage_times']
                ];
            }),
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
        $previousStageTimes = [];

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

                $previousStageTimes[] = [
                    'stage_id' => $stage->id,
                    'stage_number' => $stage->stage_number,
                    'time_taken' => $this->convertSecondsToTime($timeTakenInSeconds),
                    'penalties' => $penalties->isNotEmpty() ? $penalties->map(function ($penalty) {
                        return [
                            'penalty_reason' => $penalty->penalty_type,
                            'penalty_time' => $penalty->penalty_amount,
                        ];
                    }) : null
                ];
            }
        }

        // Calculate total time including penalties
        $totalTimeWithPenalties = $totalTime + $totalPenalties;

        return [
            'total_time' => $this->convertSecondsToTime($totalTime),
            'total_penalties' => $this->convertSecondsToTime($totalPenalties),
            'total_time_with_penalties' => $this->convertSecondsToTime($totalTimeWithPenalties),
//            'previous_stage_times' => $previousStageTimes
        ];
    }

    /**
     * Convert time in "HH:MM:SS" or "MM:SS" format to seconds.
     */
    private function convertTimeToSeconds($time)
    {
        $parts = explode(':', $time);
        $seconds = 0;

        if (count($parts) == 3) {
            // HH:MM:SS
            $seconds += $parts[0] * 3600; // Hours to seconds
            $seconds += $parts[1] * 60;   // Minutes to seconds
            $seconds += $parts[2];        // Seconds
        } elseif (count($parts) == 2) {
            // MM:SS
            $seconds += $parts[0] * 60;   // Minutes to seconds
            $seconds += $parts[1];        // Seconds
        }

        return $seconds;
    }

    /**
     * Convert seconds to "HH:MM:SS" or "MM:SS" format.
     */
    private function convertSecondsToTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
        } else {
            return sprintf('%02d:%02d', $minutes, $remainingSeconds);
        }
    }
}
