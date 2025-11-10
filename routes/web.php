<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Admins\DashboardController;
use App\Http\Controllers\Admins\RecordController;
use App\Http\Controllers\Admins\UserController;
use App\Http\Controllers\Admins\ModelAiController;
use App\Http\Controllers\Admins\TractorController;
use App\Http\Controllers\Admins\PartController;
use App\Http\Controllers\Admins\ComparisonController;
use App\Http\Controllers\Admins\ListComparisonController;

Route::get('/', [MainController::class, 'index'])->name('/');
Route::get('/dashboard', [MainController::class, 'index'])->name('dashboard');
Route::get('/dashboard/submit', [MainController::class, 'submit'])->name('dashboard.submit');
Route::get('/dashboard/export', [MainController::class, 'export'])->name('dashboard.export');
Route::get('/dashboard/reset', [MainController::class, 'reset'])->name('dashboard.reset');
Route::get('/record/{Id_Comparison}', [MainController::class, 'record'])->name('record');
Route::post('/record', [MainController::class, 'insert'])->name('record.insert');
Route::post('/record/validate-rule', [MainController::class, 'validateRule'])->name('record.validate_rule');

Route::get('/login', [MainController::class, 'signin'])->name('login');
Route::post('/login/auth', [MainController::class, 'login'])->name('login.auth');
Route::get('/logout', [MainController::class, 'logout'])->name('logout');

Route::middleware(AdminMiddleware::class)->group(function () {
    Route::get('/dashboard_admin', [DashboardController::class, 'index'])->name('dashboard.admin');
    Route::get('/dashboard_admin/submit', [DashboardController::class, 'submit'])->name('dashboard.admin.submit');
    Route::get('/dashboard_admin/export', [DashboardController::class, 'export'])->name('dashboard.admin.export');
    Route::get('/dashboard_admin/reset', [DashboardController::class, 'reset'])->name('dashboard.admin.reset');
    Route::post('/dashboard/admin/approve', [DashboardController::class, 'approve'])->name('dashboard.admin.approve');
    Route::get('/record_admin/{Id_Comparison}', [RecordController::class, 'record'])->name('record.admin');
    Route::post('/record_admin', [RecordController::class, 'insert'])->name('record.admin.insert');

    Route::get('/user', [UserController::class, 'index'])->name('user');
    Route::post('/user/create', [UserController::class, 'create'])->name('user.create');
    Route::put('/user/update/{Id_User}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/delete/{Id_User}', [UserController::class, 'destroy'])->name('user.destroy');

    Route::get('/model', [ModelAiController::class, 'index'])->name('model');
    Route::post('/model/create', [ModelAiController::class, 'store'])->name('model.store');
    Route::put('/model/update/{Id_Model}', [ModelAiController::class, 'update'])->name('model.update');
    Route::delete('/model/delete/{Id_Model}', [ModelAiController::class, 'destroy'])->name('model.destroy');

    Route::get('/tractor', [TractorController::class, 'index'])->name('tractor');
    Route::post('/tractor', [TractorController::class, 'store'])->name('tractor.store');
    Route::put('/tractor/{Id_Tractor}', [TractorController::class, 'update'])->name('tractor.update');
    Route::delete('/tractor/{Id_Tractor}', [TractorController::class, 'destroy'])->name('tractor.destroy');

    Route::get('/part', [PartController::class, 'index'])->name('part');
    Route::post('/part', [PartController::class, 'store'])->name('part.store');
    Route::put('/part/{Id_Part}', [PartController::class, 'update'])->name('part.update');
    Route::delete('/part/{Id_Part}', [PartController::class, 'destroy'])->name('part.destroy');

    Route::get('/comparison', [ComparisonController::class, 'index'])->name('comparison');
    Route::post('/comparison', [ComparisonController::class, 'store'])->name('comparison.store');
    Route::put('/comparison/{Id_Comparison}', [ComparisonController::class, 'update'])->name('comparison.update');
    Route::delete('/comparison/{Id_Comparison}', [ComparisonController::class, 'destroy'])->name('comparison.destroy');

    Route::get('/list-comparison', [ListComparisonController::class, 'index'])->name('list.comparison');
    Route::post('/list-comparison', [ListComparisonController::class, 'store'])->name('list.comparison.store');
    Route::put('/list-comparison/{Id_List_Comparison}', [ListComparisonController::class, 'update'])->name('list.comparison.update');
    Route::delete('/list-comparison/{Id_List_Comparison}', [ListComparisonController::class, 'destroy'])->name('list.comparison.destroy');
});