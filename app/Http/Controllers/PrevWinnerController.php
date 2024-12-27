<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrevWinner;

class PrevWinnerController extends Controller
{
    public function getLastWinner()
    {
        $lastWinner = PrevWinner::with(['rally', 'crew.driver', 'crew.coDriver'])
            ->latest('id')
            ->first();

        if (!$lastWinner) {
            return response()->json(['message' => 'No previous winner found'], 404);
        }

        return response()->json([
            'crew_id' => $lastWinner->crew->id,
            'rally_name' => $lastWinner->rally->rally_name,
            'driver' => $lastWinner->crew->driver->name . ' ' . $lastWinner->crew->driver->surname,
            'co_driver' => $lastWinner->crew->coDriver->name . ' ' . $lastWinner->crew->coDriver->surname,
            'car' => $lastWinner->crew->car,
            'feedback' => $lastWinner->feedback,
            'image' => $lastWinner->winning_img,
        ]);
    }
}
