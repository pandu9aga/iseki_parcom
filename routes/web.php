<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Admins\DashboardController;
use App\Http\Controllers\Admins\RecordController;
use App\Http\Controllers\Admins\UserController;

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
});