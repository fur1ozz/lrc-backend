<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RallyController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\FolderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('rallies', [RallyController::class, 'index']);

Route::get('stagesById/{rallyId}', [StageController::class, 'getStagesByRallyId']);
Route::get('stages/{seasonYear}/{rallyName}', [StageController::class, 'getStagesByRallyTagAndSeason']);

Route::get('documents/{seasonYear}/{rallyTag}', [FolderController::class, 'getDocumentsByRallyTagAndSeason']);


Route::get('news/{seasonYear}/{rallyTag}', [NewsController::class, 'getNewsBySeasonAndRally']);


