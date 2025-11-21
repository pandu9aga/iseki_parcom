<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RingSynchronizerController;
use App\Http\Controllers\BearingKbcController;
use App\Http\Controllers\BearingKoyoController;

Route::post('/ring-synchronizer/validate', [RingSynchronizerController::class, 'validateRule']);
Route::get('/ring-synchronizer/part-by-tractor/{tractorType}', [RingSynchronizerController::class, 'getPartByTractorType']);
Route::post('/ring-synchronizer/save', [RingSynchronizerController::class, 'insert']);
Route::get('/ring-synchronizer/index', [RingSynchronizerController::class, 'index']);

Route::post('/bearing-kbc/validate', [BearingKbcController::class, 'validateRule']);
Route::get('/bearing-kbc/part-by-tractor/{tractorType}', [BearingKbcController::class, 'getPartByTractorType']);
Route::post('/bearing-kbc/save', [BearingKbcController::class, 'insert']);
Route::get('/bearing-kbc/index', [BearingKbcController::class, 'index']);

Route::post('/bearing-koyo/validate', [BearingKoyoController::class, 'validateRule']);
Route::get('/bearing-koyo/part-by-tractor/{tractorType}', [BearingKoyoController::class, 'getPartByTractorType']);
Route::post('/bearing-koyo/save', [BearingKoyoController::class, 'insert']);
Route::get('/bearing-koyo/index', [BearingKoyoController::class, 'index']);