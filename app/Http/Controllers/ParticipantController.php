<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Rally;
use App\Models\Crew;
use App\Models\CoDriverInRally;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index()
    {
        $participants = Participant::all();
        return response()->json($participants);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'nationality' => 'required|string|size:2',
            'image' => 'nullable|string',
        ]);

        $participant = Participant::create($validated);
        return response()->json($participant, 201);
    }

    public function getCrewDetailsBySeasonAndRally($seasonYear, $rallyTag)
    {
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $crews = Crew::with(['team']) // Load team relationship
        ->where('rally_id', $rally->id)
            ->get();

        // Prepare an array to hold crew information with co-driver IDs
        $crewWithParticipants = $crews->map(function ($crew) use ($rally) {
            // Get the co-driver ID based on the driver_id and rally_id
            $coDrivers = CoDriverInRally::where('driver_id', $crew->driver_id)
                ->where('rally_id', $rally->id)
                ->pluck('co_driver_id');

            // Retrieve participant data for the driver
            $driver = Participant::find($crew->driver_id);

            // Retrieve participant data for each co-driver ID
            $coDriverDetails = Participant::whereIn('id', $coDrivers)->get();

            // Attach driver and co-driver details to the crew data
            return [
                'crew' => $crew,
                'driver' => $driver, // Participant data for the driver
                'co_drivers' => $coDriverDetails // Collection of participant data for co-drivers
            ];
        });

        return response()->json($crewWithParticipants);
    }
}
