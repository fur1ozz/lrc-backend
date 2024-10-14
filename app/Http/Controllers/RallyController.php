<?php

namespace App\Http\Controllers;

use App\Models\Rally;
use App\Models\Season;
use Illuminate\Http\Request;

class RallyController extends Controller
{
    public function index()
    {
        $rallies = Rally::with('season')->get()->map(function ($rally) {
            return [
                'id' => $rally->id,
                'rally_name' => $rally->rally_name,
                'date_from' => $rally->date_from,
                'date_to' => $rally->date_to,
                'location' => $rally->location,
                'road_surface' => $rally->road_surface,
                'rally_tag' => $rally->rally_tag,
                'rally_sequence' => $rally->rally_sequence,
                'season' => $rally->season->year,
            ];
        });

        return response()->json($rallies);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rally_name' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'location' => 'required|string|max:255',
            'road_surface' => 'required|string|max:255',
            'rally_tag' => 'nullable|string|max:255',
            'season_id' => 'required|exists:seasons,id',
            'rally_sequence' => 'required|integer',
        ]);

        $rally = Rally::create($validated);
        return response()->json($rally, 201);
    }
    public function update(Request $request, Rally $rally)
    {
        $validated = $request->validate([
            'rally_name' => 'required|string|max:255',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'location' => 'required|string|max:255',
            'road_surface' => 'required|string|max:255',
            'rally_tag' => 'nullable|string|max:255',
            'season_id' => 'required|exists:seasons,id',
            'rally_sequence' => 'required|integer',
        ]);

        $rally->update($validated);
        return response()->json($rally);
    }
}
