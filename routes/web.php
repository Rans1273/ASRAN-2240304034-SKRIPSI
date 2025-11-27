<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard'); // Gambar 1
Route::get('/active-visitors', [DashboardController::class, 'active'])->name('visitors.active'); // Gambar 2
Route::get('/logs', [DashboardController::class, 'logs'])->name('visitors.logs'); // Gambar 3

Route::get('/1', function () {
    return view('gate-monitor');
});

Route::resource('members', MemberController::class);