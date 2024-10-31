<?php

namespace App\Http\Controllers;

use App\Models\Rally;
use App\Models\Season;
use Illuminate\Http\Request;
use Carbon\Carbon;

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
    public function getNextEvent()
    {
        $today = Carbon::today();

        $nextRally = Rally::where('date_to', '>=', $today)
            ->orderBy('date_from')
            ->first();

        if (!$nextRally) {
            $nextRally = Rally::orderBy('date_to', 'desc')->first();
            $allEventsFinished = true;
        } else {
            $allEventsFinished = false;
        }

        if (!$nextRally) {
            return response()->json(['message' => 'No rally events found'], 404);
        }

        $season = Season::find($nextRally->season_id);

        return response()->json([
            'id' => $nextRally->id,
            'name' => $nextRally->rally_name,
            'tag' => $nextRally->rally_tag,
            'date_from' => Carbon::parse($nextRally->date_from)->format('d.m'),
            'date_to' => Carbon::parse($nextRally->date_to)->format('d.m'),
            'location' => $nextRally->location,
            'year' => $season ? $season->year : null,
            'road_surface' => $nextRally->road_surface,
            'sequence' => $nextRally->rally_sequence,
            'all_events_finished' => $allEventsFinished,
        ]);
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
