<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Rally;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function getDocumentsByRallyTagAndSeason($seasonYear, $rallyTag)
    {
        $rally = Rally::where('rally_tag', $rallyTag)
            ->whereHas('season', function ($query) use ($seasonYear) {
                $query->where('year', $seasonYear);
            })
            ->first();

        if (!$rally) {
            return response()->json(['message' => 'Rally not found for this season'], 404);
        }

        $folders = Folder::where('rally_id', $rally->id)
            ->with('documents')
            ->get();

        $response = [];
        foreach ($folders as $folder) {
            $response[] = [
                'folder_id' => $folder->id,
                'number' => $folder->number,
                'title' => $folder->title,
                'documents' => $folder->documents,
            ];
        }

        return response()->json($response);
    }
}

