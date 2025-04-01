<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Rally;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function getNewsBySeasonYearAndRallyTag($seasonYear, $rallyTag)
    {
        // Check if the rally exists for the given season year and rally tag
        $rally = Rally::whereHas('season', function ($query) use ($seasonYear) {
            $query->where('year', $seasonYear);
        })->where('rally_tag', $rallyTag)->first();

        // If the rally does not exist, return a 404 error
        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        // Fetch news for the existing rally
        $news = News::where('rally_id', $rally->id)->get();

        return response()->json($news);
    }

    public function getNewsById($newsId)
    {
        $news = News::find($newsId);

        if (!$news) {
            return response()->json(['message' => 'News not found'], 404);
        }

        return response()->json($news);
    }
}
