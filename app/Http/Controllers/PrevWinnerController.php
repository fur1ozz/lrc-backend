<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrevWinner;

class PrevWinnerController extends Controller
{
    public function getLastWinner()
    {
        $lastWinner = PrevWinner::with(['rally', 'crew'])
            ->latest('id')
            ->first();

        if (!$lastWinner) {
            return response()->json(['message' => 'No previous winner found'], 404);
        }

        return response()->json($lastWinner);
    }
}
