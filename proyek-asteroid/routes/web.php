<?php

use App\Http\Controllers\AsteroidController;
use App\Http\Controllers\AsteroidObservationController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/nasa-asteroid');

// Halaman Utama Radar NEO
Route::get('/nasa-asteroid', [AsteroidController::class, 'index'])->name('asteroid.index');

// Rute Khusus Pengunjung Belum Login (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Rute Khusus Operator yang Sudah Login (Auth)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/observations/export/pdf', [AsteroidObservationController::class, 'exportPdf'])->name('observations.export.pdf');
    Route::get('/observations/export/excel', [AsteroidObservationController::class, 'exportExcel'])->name('observations.export.excel');
    Route::resource('observations', AsteroidObservationController::class)->except(['show']);
});

