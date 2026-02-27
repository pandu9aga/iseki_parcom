<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RingSynchronizerController;
use App\Http\Controllers\BearingKbcController;
use App\Http\Controllers\BearingKoyoController;
use App\Http\Controllers\JointUniversalController;
use App\Http\Controllers\TestRingSynchronizerController;
use App\Http\Controllers\TestBearingKbcController;
use App\Http\Controllers\TestBearingKoyoController;

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

Route::post('/joint-universal/validate', [JointUniversalController::class, 'validateRule']);
Route::post('/joint-universal/save', [JointUniversalController::class, 'insert']);
Route::get('/joint-universal/index', [JointUniversalController::class, 'index']);

Route::post('/testing/ring-synchronizer/validate', [TestRingSynchronizerController::class, 'validateRule']);
Route::get('/testing/ring-synchronizer/part-by-tractor/{tractorType}', [TestRingSynchronizerController::class, 'getPartByTractorType']);
Route::post('/testing/ring-synchronizer/save', [TestRingSynchronizerController::class, 'insert']);
Route::get('/testing/ring-synchronizer/index', [TestRingSynchronizerController::class, 'index']);

Route::post('/testing/bearing-kbc/validate', [TestBearingKbcController::class, 'validateRule']);
Route::get('/testing/bearing-kbc/part-by-tractor/{tractorType}', [TestBearingKbcController::class, 'getPartByTractorType']);
Route::post('/testing/bearing-kbc/save', [TestBearingKbcController::class, 'insert']);
Route::get('/testing/bearing-kbc/index', [TestBearingKbcController::class, 'index']);

Route::post('/testing/bearing-koyo/validate', [TestBearingKoyoController::class, 'validateRule']);
Route::get('/testing/bearing-koyo/part-by-tractor/{tractorType}', [TestBearingKoyoController::class, 'getPartByTractorType']);
Route::post('/testing/bearing-koyo/save', [TestBearingKoyoController::class, 'insert']);
Route::get('/testing/bearing-koyo/index', [TestBearingKoyoController::class, 'index']);