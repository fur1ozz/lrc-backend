<?php

namespace App\Http\Controllers;

use App\Models\CrewGroupInvolvement;
use App\Models\Group;
use App\Models\Participant;
use App\Models\Rally;
use App\Models\Crew;
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

    public function getCrewDetailsBySeasonYearAndRallyTag($seasonYear, $rallyTag)
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
            ->orderByRaw('is_historic ASC, crew_number_int ASC')
            ->get();

        $crewWithParticipants = $crews->map(function ($crew) use ($rally) {

            $driver = Participant::find($crew->driver_id);
            $coDriver = Participant::find($crew->co_driver_id);

            $groupIds = CrewGroupInvolvement::where('crew_id', $crew->id)->pluck('group_id');
            $groups = Group::whereIn('id', $groupIds)->get();

            return [
                'crew' => [
                    'id' => $crew->id,
                    'crew_number' => $crew->crew_number,
                    'crew_number_int' => $crew->crew_number_int,
                    'car' => $crew->car,
                    'drive_type' => $crew->drive_type,
                    'drive_class' => $crew->drive_class,
                    'groups' => $groups->map(function ($group) {
                        return [
                            'id' => $group->id,
                            'group_name' => $group->group_name,
                        ];
                    }),
                ],
                'team' => [
                    'id' => $crew->team->id,
                    'team_name' => $crew->team->team_name,
                ],
                'driver' => [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'surname' => $driver->surname,
                    'desc' => $driver->desc,
                    'nationality' => $driver->nationality,
                    'image' => $driver->image,
                ],
                'co_driver' => $coDriver ? [
                    'id' => $coDriver->id,
                    'name' => $coDriver->name,
                    'surname' => $coDriver->surname,
                    'desc' => $coDriver->desc,
                    'nationality' => $coDriver->nationality,
                    'image' => $coDriver->image,
                ] : null
            ];
        });

        return response()->json($crewWithParticipants);
    }
}
