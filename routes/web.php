<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

// Logout route
Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');

// Guest routes
Route::middleware(['multi.guest'])->group(function () {
    Route::get('/login', [AuthenticationController::class, 'login'])->name('login');
    Route::get('/register', [AuthenticationController::class, 'register'])->name('register');

    Route::post('/login', [AuthenticationController::class, 'loginAction'])->name('login.action');

    Route::post('/register', [AuthenticationController::class, 'registerPublic'])->name('register.public');
});



// Root route '/'
Route::get('/', function () {
    if (auth('public')->check()) {
        return redirect()->route('public.dashboard');
    } elseif (auth('internal')->check()) {
        return redirect()->route('internal.dashboard');
    } else {
        return redirect()->route('login');
    }
});

// verify email
Route::get('/verify-email', [AuthenticationController::class, 'verify_email'])
    ->middleware(['auth.any', 'unverified'])
    ->name('verify.email');
// Verification link callback
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // marks the user as verified
    return redirect('/'); // redirect to dashboard or home
})->middleware(['auth:public,internal', 'signed'])->name('verification.verify');

// Resend verification email
Route::post('/email/verification-notification', function (Request $request) {
    $user = auth('public')->user() ?? auth('internal')->user();
    $user->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth:public,internal', 'throttle:6,1'])->name('verification.send');

// Dashboard routes
Route::prefix('public')
    ->name('public.')
    ->middleware(['redirect.other.guard:public', 'auth:public', 'verified'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    });

Route::prefix('internal')
    ->name('internal.')
    ->middleware(['redirect.other.guard:internal', 'auth:internal', 'verified'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        // user managemet
        Route::get('/user_public/list', [UserController::class, 'public_list'])->name('public.list');
        Route::get('/user_public/list/data', [UserController::class, 'public_list_data'])->name('public.list.data');
        Route::get('/user_public/user/data/{id}', [UserController::class, 'user_data']);

        Route::get('/user_internal/list', [UserController::class, 'internal_list'])->name('internal.list');
        Route::get('/user_internal/list/data', [UserController::class, 'internal_list_data'])->name('internal.list.data');
        Route::get('/user_internal/user/data/{id}', [UserController::class, 'internal_user_data']);
    });
