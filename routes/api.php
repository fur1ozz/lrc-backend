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
use \App\Http\Controllers\RetirementController;
use \App\Http\Controllers\OverallResultController;

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
Route::get('overall-results/{seasonYear}/{rallyName}/', [OverallResultController::class, 'getOverallResultsByRallyAndSeason']);
Route::get('stage-results/{seasonYear}/{rallyName}/{stageNumber}', [StageResultsController::class, 'getStageResultsByRallyAndSeason']);
Route::get('rally-penalties/{seasonYear}/{rallyName}/', [PenaltiesController::class, 'getPenaltiesByRally']);
Route::get('rally-retirements/{seasonYear}/{rallyName}/', [RetirementController::class, 'getRetirementsByRally']);
Route::get('rally-winner-results/{seasonYear}/{rallyName}/', [StageResultsController::class, 'getStageWinnerResultsByRallyAndSeason']);


Route::get('/import-crew-data/{rallyId}', [CrewDataController::class, 'importCrewData']);
Route::get('/calculate/{rallyId}', [OverallResultController::class, 'calculateOverallResults']);




