<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RallyController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PrevWinnerController;
use \App\Http\Controllers\CrewDataController;
use \App\Http\Controllers\StageResultsController;
use \App\Http\Controllers\PenaltiesController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//home page
Route::get('rallies', [RallyController::class, 'index']);
Route::get('previousWinner', [PrevWinnerController::class, 'getLastWinner']);

Route::get('stagesById/{rallyId}', [StageController::class, 'getStagesByRallyId']);
Route::get('stages/{seasonYear}/{rallyName}', [StageController::class, 'getStagesByRallyTagAndSeason']);

//about rally
Route::get('news/{seasonYear}/{rallyTag}', [NewsController::class, 'getNewsBySeasonAndRally']);
Route::get('participants/{seasonYear}/{rallyTag}', [ParticipantController::class, 'getCrewDetailsBySeasonAndRally']);
Route::get('documents/{seasonYear}/{rallyTag}', [FolderController::class, 'getDocumentsByRallyTagAndSeason']);

//rally results
Route::get('stage-results/{seasonYear}/{rallyName}/{stageNumber}', [StageResultsController::class, 'getStageResultsByRallyAndSeason']);
Route::get('rally-penalties/{seasonYear}/{rallyName}/', [PenaltiesController::class, 'getPenaltiesByRally']);


Route::get('/import-crew-data/{rallyId}', [CrewDataController::class, 'importCrewData']);




