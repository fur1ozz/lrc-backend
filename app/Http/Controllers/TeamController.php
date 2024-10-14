<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::all();
        return response()->json($teams);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_name' => 'required|string|max:255',
            'manager_name' => 'required|string|max:255',
            'manager_contact' => 'required|string|max:255',
        ]);

        $team = Team::create($validated);
        return response()->json($team, 201);
    }
}
