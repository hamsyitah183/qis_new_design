<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::middleware(['multi.guest'])->group(function () {
    Route::get('/login', [AuthenticationController::class, 'login'])->name('login');
    Route::get('/register', [AuthenticationController::class, 'register'])->name('register');

    Route::post('/login', [AuthenticationController::class, 'loginAction'])->name('login.action');
    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
});

Route::prefix('public')
    ->name('public.')
    ->middleware(['redirect.other.guard:public', 'auth:public'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    });

Route::prefix('internal')
    ->name('internal.')
    ->middleware(['redirect.other.guard:internal', 'auth:internal'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    });