<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\internal\MiscController;
use App\Http\Controllers\public\importPermit\PermitApplicationController;
use App\Http\Controllers\ApplicationController;

use App\Http\Controllers\internController;

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PermitGenerateController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RoleAndPermissionController;
use App\Http\Controllers\TempFileController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Broadcast;
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
        Route::get('/new_application', [ApplicationController::class, 'show'])->name('newApplication');
        Route::get('/newtest', [ApplicationController::class, 'showthis'])->name('newApplicatasdion');
        Route::post('/store_exporter', [PermitApplicationController::class, 'storeExporter'])->name('storeExp');
        Route::delete('/delete_exporter/{id}', [PermitApplicationController::class, 'deleteExporter'])->name('deleteExp');
        Route::get('/get_importers/{idno}', [PermitApplicationController::class, 'getImporters'])->name('getImporters');
        Route::get('/get_exporters', [PermitApplicationController::class, 'getExporters'])->name('getExporters');
        Route::get('/get_entry_point', [PermitApplicationController::class, 'getEntryPoint'])->name('getEntryPoint');
        Route::get('/get_consignment/{countryCode}', [PermitApplicationController::class, 'getConsignmentFromCountry'])->name('getItemFromCountry');
        Route::get('/consignment_uses/{id}', [PermitApplicationController::class, 'getConsignmentUses'])->name('consignmentUses');
        Route::post('/save-application', [PermitApplicationController::class, 'saveApplication'])->name('saveApplication');
        Route::post('/upload_attachment', [PermitApplicationController::class, 'uploadAttachment'])->name('uploadAttachment');
        Route::post('/temp_upload', [PermitApplicationController::class, 'tempUpload'])->name('tempUpload');

        // view application
        Route::get('/view_all_application', [ApplicationController::class, 'showallapplicationlist'])->name('showallapplicationlist');

        Route::get('/verify_application', [ApplicationController::class, 'verifyapplication'])->name('verifyapplication');


        // temporary file
        Route::post('/temp-upload', [TempFileController::class, 'upload']);


        Route::post('/upload-verification', [UserController::class, 'uploadVerificationAttachment'])
            ->name('user.uploadVerification');


        // cart & checkout
        Route::get('/cart', [PublicController::class, 'showcart'])->name('cart');
        Route::get('/checkout', [PublicController::class, 'showcheckout'])->name('checkout');
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

        Route::get('/permission/data', [RoleAndPermissionController::class, 'get_permission']);
        Route::post('/permission/update', [RoleAndPermissionController::class, 'update_permission']);
        // ==================== user managemet =================


        // ======================= application ========================
        Route::get('/view_all_application', [ApplicationController::class, 'showallapplicationlist'])->name('application.list');
        Route::delete('/application/delete/{id}', [ApplicationController::class, 'deleteApplication'])->name('application.delete');

        // Route::get('/application/exporter/get', [ApplicationController::class, 'get_exporter'])->name('application.exporter.get');

        


        //MISC

        Route::get('/control_panel', [MiscController::class, 'showcontrolpanel']);
        Route::get('/get_pbdata/{cate}', [MiscController::class, 'getpbdata']);
        Route::get('/getspecificpbdata/{id}', [MiscController::class, 'getspecificpbdata']);
        Route::post('/updatepbdata', [MiscController::class, 'updatepbdata']);
        Route::delete('/deletepbdata/{id}', [MiscController::class, 'deletepbdata']);
        Route::post('/addpbdata', [MiscController::class, 'addpbdata']);

        //PERMIT CONDITION
        Route::get('/permit_condition', [MiscController::class, 'showpermitcondition']);
        Route::get('/permit_condition/data', [MiscController::class, 'getpermitconditiondata']);
        Route::get('/permit_condition/getdata/{id}', [MiscController::class, 'getpermitconditionbyid']);
        Route::get('/permit_add_condition', [MiscController::class, 'permitaddcondition'])->name('permitaddcondition');
        Route::post('/save_condition', [MiscController::class, 'saveCondition'])->name('saveCondition');
        Route::get('/permit_edit_condition/{id}', [MiscController::class, 'editCondition']);

        Route::get('/permit_condition', [MiscController::class, 'showpermitcondition'])->name('permitcondition');
        // Route::get('/permit_add_condition', [MiscController::class, 'permitaddcondition']);
        Route::post('/save_condition', [MiscController::class, 'saveCondition'])->name('saveCondition');
        // Route::get('/permit_edit_condition/{id}', [MiscController::class, 'editCondition']);
        // Route::get('/control_panel', [MiscController::class, 'showcontrolpanel'])->name('controlpanel');
        Route::post('/district/entry-point/update', [MiscController::class, 'updateEntry']);

        // ======================= notifications ===========================
        Route::post('/permit/{id}', [MiscController::class, 'accept_permit']);
    });

Route::middleware(['auth.any'])
    ->group(function () {
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::get('/data', [UserController::class, 'userData']);
        Route::post('/data', [UserController::class, 'updateData']);
        Route::post('/password', [UserController::class, 'password']);
        Route::get('/get_country', [PublicController::class, 'getCountry']);

        Route::get('/api/auth-user', [UserController::class, 'userInfo']);



        Route::get('/country/{code}', [DashboardController::class, 'get_country']);
        Route::get('/entry_point/{id}', [DashboardController::class, 'get_entry_point']);

        //============================= application ======================
        Route::get('/application/list/data', [ApplicationController::class, 'getallapplicationlist'])
            ->name('application.data');
        Route::get('/application/review/list/data', [ApplicationController::class, 'getAllReviewapplicationList'])
            ->name('application.review.data');

        Route::get('/application/{id}/data', [ApplicationController::class, 'getApplicationDetails']);
        Route::get('/view_application/{uuid}', [ApplicationController::class, 'viewapplication'])
            ->name('viewApplication');
        Route::get('/edit_application/{uuid}', [ApplicationController::class, 'editApplication'])
            ->name('editApplication');

        Route::get('/application/permit/{id}/data', [ApplicationController::class, 'get_application_permit']);
        Route::post('/application/verify/{id}/', [ApplicationController::class, 'verify_application_permit']);

        Route::get('/notifications/data', [DashboardController::class, 'get_notifications'])->name('notifications');
        Route::get('/notifications/data/get', [DashboardController::class, 'notifications_data']);
        Route::post('/notifications/mark-read', function () {
            $type = authUser()['type'];
            $user = authUser()['user'];

            // Get the latest 10 notifications (same as what you display in header)
            $notifications = DatabaseNotification::where('notifiable_type', $type)
                ->where('notifiable_id', $user->uuid)
                ->latest()
                ->take(10)
                ->get();

            // Mark only these notifications as read
            foreach ($notifications as $notification) {
                if (!$notification->read_at) {
                    $notification->markAsRead();
                }
            }

            return response()->json(['status' => 'success']);
        })->name('notifications.mark-read');
        Route::get('/notifications', [DashboardController::class, 'notifications_page'])->name('notifications.page');
        Route::get('/application/exporter', [ApplicationController::class, 'show_exporter'])->name('application.exporter');


        Route::get('/permit/generate/{id}', [PermitGenerateController::class, 'generatePermitWord']);

        
    });

// broadcast --dont kacau---
// Broadcast::routes();
// Broadcast::routes(['middleware' => ['auth:internal']]);

Route::get('/intern', [internController::class, 'getView']);
Route::post('/intern', [internController::class, 'sendData']);

//error page testing
Route::get('/402', function () {abort(402); });
Route::get('/403', function () {abort(403); });
Route::get('/404', function () {abort(404); });
Route::get('/419', function () {abort(419); });
Route::get('/429', function () {abort(429); });
Route::get('/500', function () {abort(500); });
Route::get('/503', function () {abort(503); });
