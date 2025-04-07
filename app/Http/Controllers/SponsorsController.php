<?php

namespace App\Http\Controllers;

use App\Models\Rally;
use Illuminate\Http\Request;

class SponsorsController extends Controller
{
    public function getSponsorsBySeasonYearAndRallyTag($seasonYear, $rallyTag)
    {
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $sponsors = $rally->sponsors()->withPivot('type')->get();

        return response()->json($sponsors->map(function ($sponsor) {
            return [
                'sponsor_id' => $sponsor->id,
                'name' => $sponsor->name,
                'image_url' => $sponsor->image,
                'url' => $sponsor->url,
                'type' => $sponsor->pivot->type,
            ];
        }));
    }

}
