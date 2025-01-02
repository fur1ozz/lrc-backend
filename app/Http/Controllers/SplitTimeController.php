<?php

namespace App\Http\Controllers;

use App\Models\SplitTime;
use Illuminate\Http\Request;

class SplitTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $splitTimes = SplitTime::with(['crew', 'split'])->get();
        return response()->json($splitTimes);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(SplitTime $splitTime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SplitTime $splitTime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SplitTime $splitTime)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SplitTime $splitTime)
    {
        //
    }
}
