<?php

use App\Http\Controllers\GalleryImageController;
use App\Http\Controllers\SplitTimeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RallyController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PrevWinnerController;
use \App\Http\Controllers\StageResultsController;
use \App\Http\Controllers\PenaltiesController;
use \App\Http\Controllers\RetirementController;
use \App\Http\Controllers\OverallResultController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//home page
Route::get('rallies', [RallyController::class, 'index']);
Route::get('next-event', [RallyController::class, 'getNextEvent']);
Route::get('previousWinner', [PrevWinnerController::class, 'getLastWinner']);

Route::get('stagesById/{rallyId}', [StageController::class, 'getStagesByRallyId']);
Route::get('stages/{seasonYear}/{rallyName}', [StageController::class, 'getStagesBySeasonYearAndRallyTag']);

//about rally
Route::get('news/{seasonYear}/{rallyTag}', [NewsController::class, 'getNewsBySeasonYearAndRallyTag']);
Route::get('news-article/{id}', [NewsController::class, 'getNewsById']);
Route::get('participants/{seasonYear}/{rallyTag}', [ParticipantController::class, 'getCrewDetailsBySeasonYearAndRallyTag']);
Route::get('documents/{seasonYear}/{rallyTag}', [FolderController::class, 'getDocumentsBySeasonYearAndRallyTag']);
Route::get('photos/{seasonYear}/{rallyTag}', [GalleryImageController::class, 'getGalleryImagesBySeasonYearAndRallyTag']);

//rally results
Route::get('overall-results/{seasonYear}/{rallyName}/', [OverallResultController::class, 'getOverallResultsBySeasonYearAndRallyTag']);
Route::get('stage-results/{seasonYear}/{rallyName}/{stageNumber}', [StageResultsController::class, 'getStageResultsBySeasonYearRallyTagAndStageNumber']);
Route::get('stage-splits/{seasonYear}/{rallyName}/{stageNumber}', [SplitTimeController::class, 'getCrewSplitTimesBySeasonYearRallyTagAndStageNumber']);
Route::get('rally-penalties/{seasonYear}/{rallyName}/', [PenaltiesController::class, 'getPenaltiesBySeasonYearAndRallyTag']);
Route::get('rally-retirements/{seasonYear}/{rallyName}/', [RetirementController::class, 'getRetirementsBySeasonYearAndRallyTag']);
Route::get('rally-winner-results/{seasonYear}/{rallyName}/', [StageResultsController::class, 'getStageWinnerResultsBySeasonYearAndRallyTag']);

Route::get('/calculate/{rallyId}', [OverallResultController::class, 'calculateOverallResults']);




