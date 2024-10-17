<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\Participant;
use App\Models\Rally;
use App\Models\Retirement;
use Illuminate\Http\Request;

class RetirementController extends Controller
{
    public function getRetirementsByRally($seasonYear, $rallyTag)
    {
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $crews = Crew::where('rally_id', $rally->id)->get();

        $retirements = Retirement::whereIn('crew_id', $crews->pluck('id'))->get()->keyBy('crew_id');

        $retirementData = $crews->map(function ($crew) use ($retirements) {
            if (!isset($retirements[$crew->id])) {
                return null;
            }

            $retirement = $retirements[$crew->id];

            $driver = Participant::find($crew->driver_id);
            $coDriver = Participant::find($crew->co_driver_id);

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
                'retirement' => [
                    'retirement_reason' => $retirement->retirement_reason,
                    'stage_of_retirement' => $retirement->stage_of_retirement,
                    'finished_stages' => $retirement->stage_of_retirement - 1,
                ],
            ];
        });

        $filteredRetirementData = $retirementData->filter(function ($item) {
            return $item !== null;
        });

        return response()->json($filteredRetirementData->values());
    }

}
