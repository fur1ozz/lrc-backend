<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoDriverInRally;

class CoDriverInRallyController extends Controller
{
    public function index()
    {
        $coDrivers = CoDriverInRally::with(['driver', 'coDriver', 'rally'])->get();
        return response()->json($coDrivers);
    }
}
