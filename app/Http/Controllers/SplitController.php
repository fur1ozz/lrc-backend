<?php

namespace App\Http\Controllers;

use App\Models\Split;
use Illuminate\Http\Request;

class SplitController extends Controller
{
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
