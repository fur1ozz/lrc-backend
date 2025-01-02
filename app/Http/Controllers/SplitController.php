<?php

namespace App\Http\Controllers;

use App\Models\Split;
use Illuminate\Http\Request;

class SplitController extends Controller
{
    public function getSplitsByStageId($stageId)
    {

        $splits = Split::where('stage_id', $stageId)
            ->select('split_number', 'split_distance')
            ->orderBy('split_number', 'asc')
            ->get();

        if ($splits->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No splits found for this stage.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $splits,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $splits = Split::all();
        return response()->json($splits);
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
    public function show(Split $split)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Split $split)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Split $split)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Split $split)
    {
        //
    }
}
