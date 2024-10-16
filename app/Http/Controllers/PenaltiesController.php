<?php

namespace App\Http\Controllers;

use App\Models\Penalties;
use Illuminate\Http\Request;
use App\Models\Crew;
use App\Models\Rally;

class PenaltiesController extends Controller
{
    public function getPenaltiesByRally($seasonYear, $rallyTag)
    {
        // Find the rally based on the provided tag and season year
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        // Get all crews associated with the rally
        $crews = Crew::where('rally_id', $rally->id)->get();

        // Prepare an array to hold the penalties
        $penaltiesData = $crews->map(function ($crew) {
            // Get penalties for the current crew
            $penalties = Penalties::where('crew_id', $crew->id)->get();

            // Map penalties to a more structured array
            return [
                'crew_id' => $crew->id,
                'crew_number' => $crew->crew_number, // Assuming you have a crew_number field
                'penalties' => $penalties->map(function ($penalty) {
                    return [
                        'stage_id' => $penalty->stage_id,
                        'penalty_type' => $penalty->penalty_type,
                        'penalty_amount' => $penalty->penalty_amount,
                    ];
                }),
            ];
        });

        return response()->json($penaltiesData);
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
