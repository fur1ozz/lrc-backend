<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use Illuminate\Http\Request;

class CrewController extends Controller
{
    // Get all crews
    public function index()
    {
        $crews = Crew::with(['driver', 'team', 'rally'])->get();
        return response()->json($crews);
    }

    // Store a new crew
    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => 'required|exists:participants,id',
            'team_id' => 'required|exists:teams,id',
            'rally_id' => 'required|exists:rallies,id',
            'crew_number' => 'required|string|max:255',
            'car' => 'required|string|max:255',
            'drive_type' => 'required|string|max:255',
            'drive_class' => 'required|string|max:255',
        ]);

        $crew = Crew::create($validated);
        return response()->json($crew, 201);
    }

    // Get a specific crew by ID
    public function show($id)
    {
        $crew = Crew::with(['driver', 'team', 'rally'])->find($id);

        if (!$crew) {
            return response()->json(['message' => 'Crew not found'], 404);
        }

        return response()->json($crew);
    }

    // Update an existing crew
    public function update(Request $request, $id)
    {
        $crew = Crew::find($id);

        if (!$crew) {
            return response()->json(['message' => 'Crew not found'], 404);
        }

        $validated = $request->validate([
            'driver_id' => 'sometimes|required|exists:participants,id',
            'team_id' => 'sometimes|required|exists:teams,id',
            'rally_id' => 'sometimes|required|exists:rallies,id',
            'crew_number' => 'sometimes|required|string|max:255',
            'car' => 'sometimes|required|string|max:255',
            'drive_type' => 'sometimes|required|string|max:255',
            'drive_class' => 'sometimes|required|string|max:255',
        ]);

        $crew->update($validated);
        return response()->json($crew);
    }

    // Delete a crew
    public function destroy($id)
    {
        $crew = Crew::find($id);

        if (!$crew) {
            return response()->json(['message' => 'Crew not found'], 404);
        }

        $crew->delete();
        return response()->json(['message' => 'Crew deleted']);
    }
}
