<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use App\Models\Rally;
use App\Models\Season;
use Illuminate\Http\Request;

class GalleryImageController extends Controller
{
    public function getGalleryImagesBySeasonYearAndRallyTag($seasonYear, $rallyTag)
    {
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })
            ->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $galleryImages = $rally->galleryImages()->get();

        $response = [];
        foreach ($galleryImages as $image) {
            $response[] = [
                'image_id' => $image->id,
                'image_url' => asset('storage/' . $image->img_src),
                'created_by' => $image->created_by,
            ];
        }

        return response()->json($response);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(GalleryImage $galleryImage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GalleryImage $galleryImage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GalleryImage $galleryImage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GalleryImage $galleryImage)
    {
        //
    }
}
