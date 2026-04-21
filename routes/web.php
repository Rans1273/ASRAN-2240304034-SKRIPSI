<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PengunjungController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/active-visitors', [DashboardController::class, 'active'])->name('visitors.active');
Route::get('/logs', [DashboardController::class, 'logs'])->name('visitors.logs');
Route::resource('members', MemberController::class);

Route::get('/logs/export/excel', [DashboardController::class, 'exportExcel'])->name('visitors.export.excel');
Route::get('/logs/export/pdf', [DashboardController::class, 'exportPdf'])->name('visitors.export.pdf');

Route::get('/dashboard/realtime', [DashboardController::class, 'realtime'])->name('dashboard.realtime');

Route::get('/scan/{id}', [PengunjungController::class, 'scan']);