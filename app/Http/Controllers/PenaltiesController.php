<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Penalties;
use Illuminate\Http\Request;
use App\Models\Crew;
use App\Models\Rally;

class PenaltiesController extends Controller
{
    public function getPenaltiesByRally($seasonYear, $rallyTag)
    {
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $crews = Crew::with(['team'])
            ->where('rally_id', $rally->id)
            ->get();

        $penaltiesData = $crews->map(function ($crew) {
            $penalties = Penalties::where('crew_id', $crew->id)->get();

            if ($penalties->isEmpty()) {
                return null;
            }

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
                'penalties' => $penalties->map(function ($penalty) {
                    return [
                        'stage_id' => $penalty->stage_id,
                        'penalty_type' => $penalty->penalty_type,
                        'penalty_amount' => lrc_formatMillisecondsTwoDigits($penalty->penalty_amount),
                    ];
                }),
                'team' => [
                    'id' => $crew->team->id,
                    'team_name' => $crew->team->team_name,
                ],
            ];
        });

        $penaltiesData = $penaltiesData->filter(function ($item) {
            return $item !== null;
        });

        return response()->json($penaltiesData->values()); // Use values() to return indexed array
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
    public function show(Penalties $penalties)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Penalties $penalties)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Penalties $penalties)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penalties $penalties)
    {
        //
    }
}
