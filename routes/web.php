<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\public\importPermit\PermitApplicationController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RoleAndPermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

// Logout route
Route::get('/logout', [AuthenticationController::class, 'logout'])->name('logout');

// Guest routes
Route::middleware(['multi.guest'])->group(function () {
    Route::get('/login', [AuthenticationController::class, 'login'])->name('login');
    // Route::get('/register', [AuthenticationController::class, 'register'])->name('register');
    Route::get('/register', [AuthenticationController::class, 'register_test'])->name('register');
    Route::post('/login', [AuthenticationController::class, 'loginAction'])->name('login.action');
    Route::post('/register', [AuthenticationController::class, 'registerPublic'])->name('register.public');


    Route::get('/forgot-password', [PasswordResetController::class, 'resetPage'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
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
Route::get('/email/verify/{id}/{hash}', [AuthenticationController::class, 'verify_link'])
    ->middleware('signed')
    ->name('verification.verify');

// Resend verification email
Route::post('/email/verification-notification', [AuthenticationController::class, 'resend_verify_link'])->middleware(['auth:public,internal', 'throttle:6,1'])->name('verification.send');

// Dashboard routes
Route::prefix('public')
    ->name('public.')
    ->middleware(['redirect.other.guard:public', 'auth:public', 'verified'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/import_permit_application', [PermitApplicationController::class, 'show'])->name('permitApplication');
        Route::get('/import_assign_application', action: [PermitApplicationController::class, 'showassign'])->name('permitAssignApplication');
        Route::get('/new_application', [PublicController::class, 'show'])->name('newApplication');
        Route::get('/newtest', [PublicController::class, 'showthis'])->name('newApplicatasdion');
        Route::post('/store_exporter', [PermitApplicationController::class, 'storeExporter'])->name('storeExp');
        Route::get('/get_importers/{idno}', [PermitApplicationController::class, 'getImporters'])->name('getImporters');
        Route::get('/get_exporters', [PermitApplicationController::class, 'getExporters'])->name('getExporters');
        Route::get('/get_entry_point', [PermitApplicationController::class, 'getEntryPoint'])->name('getEntryPoint');        
        Route::get('/get_consignment/{countryCode}', [PermitApplicationController::class, 'getConsignmentFromCountry'])->name('getItemFromCountry');
        Route::get('/consignment_uses/{id}', [PermitApplicationController::class, 'getConsignmentUses'])->name('consignmentUses');
        Route::post('/save-application', [PermitApplicationController::class, 'saveApplication'])->name('saveApplication');
        Route::post('/upload_attachment', [PermitApplicationController::class, 'uploadAttachment'])->name('uploadAttachment');
        Route::post('/temp_upload', [PermitApplicationController::class, 'tempUpload'])->name('tempUpload');

        // view application
        Route::get('/view_all_application', [PublicController::class, 'showallapplicationlist'])->name('showallapplicationlist');
        Route::get('/verify_application', [PublicController::class, 'verifyapplication'])->name('verifyapplication');
        Route::get('/view_application/{uuid}', [PublicController::class, 'viewapplication'])->name('viewApplication');
        

        Route::post('/upload-verification', [UserController::class, 'uploadVerificationAttachment'])
            ->name('user.uploadVerification');
    });

Route::prefix('internal')
    ->name('internal.')
    ->middleware(['redirect.other.guard:internal', 'auth:internal', 'verified'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        // ==================== user managemet =================
        Route::get('/user_public/list', [UserController::class, 'public_list'])->name('public.list');
        Route::get('/user_public/list/data', [UserController::class, 'public_list_data'])->name('public.list.data');
        Route::get('/user_public/user/data/{id}', [UserController::class, 'user_data']);
        Route::post('/user_public/save', [UserController::class, 'public_user_save']);
        Route::delete('/user_public/delete/{id}', [UserController::class, 'public_user_delete']);

        Route::get('/user_internal/list', [UserController::class, 'internal_list'])->name('internal.list');
        Route::get('/user_internal/list/data', [UserController::class, 'internal_list_data'])->name('internal.list.data');
        Route::get('/user_internal/user/data/{id}', [UserController::class, 'internal_user_data']);
        Route::post('/user_internal/save', [UserController::class, 'internal_user_save']);
        Route::delete('/user_internal/delete/{id}', [UserController::class, 'internal_user_delete']);

        Route::get('/activity_log', [ActivityLogController::class, 'log'])->name('internal.activity_log');
        Route::get('/activity_log/data', [ActivityLogController::class, 'data']);


        Route::get('/user_list/{type}', [UserController::class, 'user_list']);


        Route::get('/verification/{id}', [UserController::class, 'verification_attachment']);
        Route::post('/verification/{id}/save', [UserController::class, 'save_attachment']);


        Route::get('/roles', [RoleAndPermissionController::class, 'role'])->name('internal.role');
        Route::get('/roles/list/data', [RoleAndPermissionController::class, 'role_list_data']);
        Route::post('/roles/update', [RoleAndPermissionController::class, 'update_role']);
        
        Route::get('/permission/data', [ RoleAndPermissionController::class, 'get_permission']);
        Route::post('/permission/update', [ RoleAndPermissionController::class, 'update_permission']);
        // ==================== user managemet =================

        //MISC

        Route::get('/control_panel', [\App\Http\Controllers\internal\MiscController::class, 'showcontrolpanel']);
    });

Route::middleware(['auth.any'])
    ->group(function () {
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::get('/data', [UserController::class, 'userData']);
        Route::post('/data', [UserController::class, 'updateData']);
        Route::post('/password', [UserController::class, 'password']);
    });
