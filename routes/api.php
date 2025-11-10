<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RingSynchronizerController;

Route::post('/ring-synchronizer/validate', [RingSynchronizerController::class, 'validateRule']);
Route::get('/ring-synchronizer/part-by-tractor/{tractorType}', [RingSynchronizerController::class, 'getPartByTractorType']);
Route::post('/ring-synchronizer/save', [RingSynchronizerController::class, 'insert']);