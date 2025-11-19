<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RingSynchronizerController;
use App\Http\Controllers\BearingKbcController;

Route::post('/ring-synchronizer/validate', [RingSynchronizerController::class, 'validateRule']);
Route::get('/ring-synchronizer/part-by-tractor/{tractorType}', [RingSynchronizerController::class, 'getPartByTractorType']);
Route::post('/ring-synchronizer/save', [RingSynchronizerController::class, 'insert']);
Route::get('/ring-synchronizer/index', [RingSynchronizerController::class, 'index']);

Route::post('/bearing-kbc/validate', [BearingKbcController::class, 'validateRule']);
Route::get('/bearing-kbc/part-by-tractor/{tractorType}', [BearingKbcController::class, 'getPartByTractorType']);
Route::post('/bearing-kbc/save', [BearingKbcController::class, 'insert']);
Route::get('/bearing-kb/index', [BearingKbcController::class, 'index']);