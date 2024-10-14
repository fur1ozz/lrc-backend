<?php
namespace App\Http\Controllers;

use App\Models\Stage;
use App\Models\Rally;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function getStagesByRallyId($rallyId)
    {
        $rally = Rally::find($rallyId);

        if (!$rally) {
            return response()->json(['message' => 'Rally not found'], 404);
        }

        $stages = Stage::where('rally_id', $rallyId)->get();
        return response()->json($stages);
    }
    public function getStagesByRallyTagAndSeason($seasonYear, $rallyTag)
    {
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $stages = Stage::where('rally_id', $rally->id)->get();

        return response()->json($stages);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'rally_id' => 'required|exists:rallies,id',
            'stage_name' => 'required|string|max:255',
            'stage_number' => 'required|integer',
            'distance_km' => 'required|numeric',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
        ]);

        $stage = Stage::create($validatedData);
        return response()->json($stage, 201);
    }
}

