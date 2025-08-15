<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

Route::get('/', [MainController::class, 'index'])->name('/');
Route::get('/dashboard', [MainController::class, 'index'])->name('dashboard');
Route::get('/dashboard/submit', [MainController::class, 'submit'])->name('dashboard.submit');
Route::get('/dashboard/export', [MainController::class, 'export'])->name('dashboard.export');
Route::get('/dashboard/reset', [MainController::class, 'reset'])->name('dashboard.reset');
Route::get('/record/{Id_Comparison}', [MainController::class, 'record'])->name('record');
Route::post('/record', [MainController::class, 'insert'])->name('record.insert');