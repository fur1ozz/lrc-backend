<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Rally;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function getNewsBySeasonYearAndRallyTag($seasonYear, $rallyTag)
    {
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

    public function getNewsById($seasonYear, $rallyTag, $newsId)
    {

        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })
            ->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $news = News::where('id', $newsId)->where('rally_id', $rally->id)->first();

        if (!$news) {
            return response()->json(['message' => 'News not found for this rally'], 404);
        }

        return response()->json($news);
    }
}
