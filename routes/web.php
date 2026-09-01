<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\internal\MiscController;
use App\Http\Controllers\internal\AnnouncementController;
use App\Http\Controllers\public\importPermit\PermitApplicationController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ConsignmentController;
use App\Http\Controllers\ConsignmentApplicationController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\ApplicationPaymentController;

use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PermitGenerateController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RoleAndPermissionController;
use App\Http\Controllers\TempFileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermitConsignmentController;
use App\Http\Controllers\InspectionPermitController;
use App\Http\Controllers\ConsignmentPermitController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\BoundaryOfficerController;
use App\Http\Controllers\ConsignmentMiscController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\IpConditionController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\VehicleController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::get('/documents/data', [DocumentController::class, 'documentsList']);

// Guest routes (Unauthenticated users)
Route::middleware(['multi.guest'])->group(function () {
    Route::get('/login', [AuthenticationController::class, 'login2'])->name('login');
    Route::post('/login', [AuthenticationController::class, 'loginAction'])->name('login.action');

    Route::get('/register', [AuthenticationController::class, 'register'])->name('register');
    Route::post('/register', [AuthenticationController::class, 'registerPublic'])->name('register.public');

    Route::get('/forgot-password', [PasswordResetController::class, 'resetPage'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');

    // list of documents

});

// Password Reset Routes (Token verification)
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// Protected Authentication Routes (Logged-in users only)
// Note: Changed to POST for security. If your frontend strictly requires GET, change it back to Route::get
Route::get('/logout', [AuthenticationController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
    if (auth('public')->check()) {
        return redirect()->route('public.dashboard');
    } elseif (auth('internal')->check()) {
        return redirect()->route('internal.dashboard');
    } else {
        return redirect()->route('login');
    }
});

// Root Route '/'
// This handles the logic of where to send someone based on their auth status
Route::get('/', function () {

    return app(LandingController::class)->landing();


    // return redirect()->route('login');
})->name('home');

Route::get('/gallery', [LandingController::class, 'gallery'])->name('public.gallery');

Route::get('/announcement', function () {
    $announcements = \App\Models\Announcement::with('releasedBy')
        ->where('is_active', true)
        ->where(function ($query) {
            $query->whereNull('valid_from')
                ->orWhere('valid_from', '<=', now()->toDateString());
        })
        ->where(function ($query) {
            $query->whereNull('valid_until')
                ->orWhere('valid_until', '>=', now()->toDateString());
        })
        ->orderBy('pin_announcement', 'desc')
        ->latest()
        ->get();

    return view('pages.announcement', [
        'title' => 'Announcement',
        'announcements' => $announcements
    ]);
})->name('announcements.index');

// verify email
Route::get('/verify-email', [AuthenticationController::class, 'verify_email'])
    ->middleware(['auth.any', 'unverified'])
    ->name('verify.email');

// Verification link callback
Route::get('/email/verify/{id}/{hash}', [AuthenticationController::class, 'verify_link'])
    ->middleware('signed')
    ->name('verification.verify');

// Resend verification email
Route::post('/email/verification-notification', [AuthenticationController::class, 'resend_verify_link'])
    ->middleware(['auth:public,internal', 'throttle:6,1'])
    ->name('verification.send');

// Dashboard routes
Route::prefix('public')
    ->name('public.')
    ->middleware(['redirect.other.guard:public', 'auth:public', 'verified'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/import_permit_application', [PermitApplicationController::class, 'show'])->name('permitApplication');
        Route::get('/import_assign_application', [PermitApplicationController::class, 'showassign'])->name('permitAssignApplication');
        Route::get('/new_application', [ApplicationController::class, 'show'])->name('newApplication');
        Route::get('/newtest', [ApplicationController::class, 'showthis'])->name('newApplicatasdion');
        Route::post('/store_exporter', [PermitApplicationController::class, 'storeExporter'])->name('storeExp');
        Route::delete('/delete_exporter/{id}', [PermitApplicationController::class, 'deleteExporter'])->name('deleteExp');
        Route::get('/get_importers/{idno}', [PermitApplicationController::class, 'getImporters'])->name('getImporters');
        Route::get('/get_exporters', [PermitApplicationController::class, 'getExporters'])->name('getExportersJson');
        Route::get('/get_importers', [PermitApplicationController::class, 'getConsignmentImporters'])->name('getImportersJson');
        Route::get('/get_entry_point', [PermitApplicationController::class, 'getEntryPoint'])->name('getEntryPoint');
        Route::get('/consignment_uses/{id}', [PermitApplicationController::class, 'getConsignmentUses'])->name('consignmentUses');
        Route::get('/all_consignment_uses/{id}', [PermitApplicationController::class, 'getAllConsignmentUses'])->name('allConsignmentUses');
        Route::post('/save-application', [PermitApplicationController::class, 'saveApplication'])->name('saveApplication');
        Route::post('/upload_attachment', [PermitApplicationController::class, 'uploadAttachment'])->name('uploadAttachment');
        Route::post('/temp_upload', [PermitApplicationController::class, 'tempUpload'])->name('tempUpload');
        Route::post('/save_application_inspection', [InspectionController::class, 'saveApplication'])->name('saveApplicationInspection');

        Route::post('/save_application_consignment', [ConsignmentApplicationController::class, 'saveApplication'])->name('saveApplicationConsignment');
        Route::post('/save_draft_consignment', [ConsignmentApplicationController::class, 'saveDraft'])->name('saveDraftConsignment');
        // view application
        Route::get('/view_import_permit', [ApplicationController::class, 'showallapplicationlist'])->name('showallapplicationlist');

        Route::get('/view_all_consignment', [ConsignmentController::class, 'showallconsignmentlist'])->name('showallconsignmentlist');

        Route::get('/verify_application', [ApplicationController::class, 'verifyapplication'])->name('verifyapplication');
        Route::get('/agent_list', [ApplicationController::class, 'agentList']);
        Route::get('/view_application/{uuid}', [ConsignmentApplicationController::class, 'viewapplication'])->name('viewApplication');

        // get importer
        Route::post('/store_consignment_importer', [ConsignmentApplicationController::class, 'storeConsignmentImporter'])->name('storeImporter');
        Route::get('/get_consignment_certificate/{countryCode}', [ConsignmentApplicationController::class, 'getConsignmentFromCountry']);
        Route::delete('/delete_importer/{id}', [ConsignmentApplicationController::class, 'deleteImporter']);
        // itemSelect
        Route::post('/save-permit/{id}', [PermitApplicationController::class, 'reapply']);

        Route::post('/save-consignment/{id}', [ConsignmentApplicationController::class, 'reapply']);

        // temporary file
        Route::post('/temp-upload', [TempFileController::class, 'upload']);

        Route::post('/upload-verification', [UserController::class, 'uploadVerificationAttachment'])->name('user.uploadVerification');

        // cart & checkout
        Route::get('/cart', [PublicController::class, 'showcart'])->name('cart');
        Route::get('/checkout', [PublicController::class, 'showcheckout'])->name('checkout');

        Route::get('/consignment_certificate_application_self', [ConsignmentApplicationController::class, 'getView'])->name('consignment.app');
        Route::get('/consignment_certificates_application_other', [ConsignmentApplicationController::class, 'getViewOther'])->name('consignmentOther.app');
        Route::post('/save-consignment', [ConsignmentApplicationController::class, 'saveApplication'])->name('savConsignment');
        Route::delete('/consignment_application/delete/{id}', [ConsignmentApplicationController::class, 'deleteApplication'])->name('consignment.delete');

        Route::get('/inspection_certificates_application', [InspectionController::class, 'getInspection']);
        Route::get('/inspection_certificates_list', [InspectionController::class, 'showAllInspectionList'])->name('showallinspectionlist');
        Route::get('/view_inspection_certificate/{id}', [InspectionController::class, 'viewInspection'])->name('viewInspectionApplication');
        Route::get('/inspection_application_data/{id}', [InspectionController::class, 'getApplicationData'])->name('inspection.app.data');
        Route::post('/inspection/{id}/status', [InspectionController::class, 'updateStatus'])->name('inspection.status');
        Route::get('/inspection_certificates_application_self/{id?}', [InspectionController::class, 'getInspectionSelf'])->name('inspectionApplicationSelf');
        Route::get('/inspection_certificates_application_others/{id?}', [InspectionController::class, 'getInspectionOthers'])->name('inspectionApplicationOthers');
        Route::post('/save-inspection/{id}', [InspectionController::class, 'reapply']);

        Route::get('/vehicle/data', [VehicleController::class, 'getVehicleList']);
        Route::post('/store_vehicle', [VehicleController::class, 'storeVehicle'])->name('storeVehicle');

        // ==================== Filter API Endpoints ====================
        Route::get('/api/filters/my-exporters', [FilterController::class, 'getMyExporters'])->name('api.filters.myExporters');
        Route::get('/api/filters/my-importers', [FilterController::class, 'getMyImporters'])->name('api.filters.myImporters');
        Route::get('/api/filters/my-consignment-exporters', [FilterController::class, 'getMyConsignmentExporters'])->name('api.filters.myConsignmentExporters');
        Route::get('/api/filters/my-consignment-importers', [FilterController::class, 'getMyConsignmentImporters'])->name('api.filters.myConsignmentImporters');

        Route::get('/get_item_details/{id}', [MiscController::class, 'getspecificitem']);
    });

Route::prefix('internal')
    ->name('internal.')
    ->middleware(['redirect.other.guard:internal', 'auth:internal', 'verified'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        // ==================== user managemet =================
        Route::get('/user_public/list', [UserController::class, 'public_list'])->name('public.list');
        Route::get('/user_public/list/data', [UserController::class, 'public_list_data'])->name('public.list.data');
        Route::get('/user_public/view/{id}', [UserController::class, 'public_user_view'])->name('public.view');

        // Verification Routes
        Route::get('/user_public/verification', [UserController::class, 'verification_list'])->name('public.verification.list');
        Route::get('/user_public/verification/data', [UserController::class, 'verification_list_data'])->name('public.verification.list.data');
        Route::get('/verification_count', [UserController::class, 'verification_count']);
        Route::get('/application_count', [ApplicationController::class, 'application_count']);

        Route::get('/user_public/user/data/{id}', [UserController::class, 'user_data']);
        Route::post('/user_public/save', [UserController::class, 'public_user_save']);
        Route::delete('/user_public/delete/{id}', [UserController::class, 'public_user_delete']);

        Route::get('/user_internal/list', [UserController::class, 'internal_list'])->name('internal.list');
        Route::get('/user_internal/list/data', [UserController::class, 'internal_list_data'])->name('internal.list.data');
        Route::get('/user_internal/view/{id}', [UserController::class, 'internal_user_view'])->name('internal.view');
        Route::get('/user_internal/user/data/{id}', [UserController::class, 'internal_user_data']);
        Route::post('/user_internal/save', [UserController::class, 'internal_user_save']);
        Route::delete('/user_internal/delete/{id}', [UserController::class, 'internal_user_delete']);

        Route::get('/activity-logs', [ActivityLogController::class, 'log'])->name('activity_logs');
        Route::get('/activity-logs/data', [ActivityLogController::class, 'data'])->name('activity_logs.data');
        Route::get('/activity-logs/export-excel', [ActivityLogController::class, 'exportExcel'])->name('activity_logs.export_excel');
        Route::get('/activity-logs/export-pdf', [ActivityLogController::class, 'exportPdf'])->name('activity_logs.export_pdf');
        Route::get('/user_list/{type}', [UserController::class, 'user_list']);

        Route::get('/verification/{id}', [UserController::class, 'verification_attachment']);
        Route::post('/verification/{id}/save', [UserController::class, 'save_attachment']);
        Route::post('/verification/attachment/{attachmentId}/accept', [UserController::class, 'acceptAttachment']);
        Route::post('/verification/attachment/{attachmentId}/reject', [UserController::class, 'rejectAttachment']);

        Route::get('/roles', [RoleAndPermissionController::class, 'role'])->name('internal.role');
        Route::get('/roles/list/data', [RoleAndPermissionController::class, 'role_list_data']);
        Route::post('/roles/update', [RoleAndPermissionController::class, 'update_role']);

        Route::get('/permission/data', [RoleAndPermissionController::class, 'get_permission']);
        Route::post('/permission/update', [RoleAndPermissionController::class, 'update_permission']);
        // ==================== user managemet =================

        // ======================= application ========================
        Route::get('/view_import_permit', [ApplicationController::class, 'showallapplicationlist'])->name('application.list');
        Route::delete('/application/delete/{id}', [ApplicationController::class, 'deleteApplication'])->name('application.delete');

        // ======================= exporter and importer ========================
        Route::get('/exporter_list', [ApplicationController::class, 'showInternalExporterList'])->name('exporter.list');
        Route::get('/exporter_list/data', [ApplicationController::class, 'getInternalExporterListData'])->name('exporter.list.data');
        Route::get('/importer_list', [ApplicationController::class, 'showInternalImporterList'])->name('importer.list');
        Route::get('/importer_list/data', [ApplicationController::class, 'getInternalImporterListData'])->name('importer.list.data');


        // ======================= announcements ========================
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.list');
        Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::get('/announcements/{id}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::get('/announcements/data', [AnnouncementController::class, 'data'])->name('announcements.data');
        Route::get('/announcements/{id}', [AnnouncementController::class, 'show'])->name('announcements.show');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::put('/announcements/{id}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
        Route::get('/announcements/{id}/attachments', [AnnouncementController::class, 'getAttachments'])->name('announcements.attachments.get');
        Route::post('/announcements/{id}/attachments', [AnnouncementController::class, 'uploadAttachment'])->name('announcements.attachments.upload');
        Route::delete('/announcements/attachments/{attachmentId}', [AnnouncementController::class, 'deleteAttachment'])->name('announcements.attachments.delete');
        Route::post('/announcements/{id}/toggle', [AnnouncementController::class, 'toggleActive'])->name('announcements.toggle');
        Route::post('/announcements/{id}/toggle-pin', [AnnouncementController::class, 'togglePin'])->name('announcements.toggle_pin');
        Route::post('/announcements/{id}/share-email', [AnnouncementController::class, 'shareViaEmail'])->name('announcements.share_email');

        // ============================== gallery ================================================
        Route::get('/galleries', [GalleryController::class, 'index'])->name('galleries.list');
        Route::get('/galleries/data', [GalleryController::class, 'data'])->name('galleries.data');
        Route::get('/galleries/{id}', [GalleryController::class, 'show'])->name('galleries.show');
        Route::post('/galleries', [GalleryController::class, 'store'])->name('galleries.store');
        Route::put('/galleries/{id}', [GalleryController::class, 'update'])->name('galleries.update');
        Route::delete('/galleries/{id}', [GalleryController::class, 'destroy'])->name('galleries.destroy');

        // ======================= inspection certificates ========================
        Route::get('/inspection_certificates_list', [InspectionController::class, 'showAllInspectionList'])->name('inspection.list');

        Route::get('/view_inspection_certificate/{id}', [InspectionController::class, 'viewApplication'])->name('viewInspectionApplication');
        // Route::delete('/inspection/delete/{id}', [InspectionController::class, 'deleteApplication'])->name('inspection.delete');

        // ======================= consignment certificates ========================
        Route::get('/consignment_certificates_list', [ConsignmentController::class, 'showInternalConsignmentList'])->name('consignment.list');
        Route::delete('/consignment/delete/{id}', [ConsignmentController::class, 'deleteApplication'])->name('internal.consignment.delete');

        Route::post('/consignment/{id}/status', [ConsignmentController::class, 'updateStatus'])->name('consignment.status');
        Route::delete('/inspection/delete/{id}', [InspectionController::class, 'deleteApplication'])->name('inspection.delete');
        Route::get('/inspection_application/{id}/data', [InspectionController::class, 'getApplicationDetails']);

        // ==================== Filter API Endpoints (Internal) ====================
        Route::get('/api/filters/public-users', [FilterController::class, 'getPublicUsers'])->name('api.filters.publicUsers');
        Route::get('/api/filters/user/{uuid}/exporters', [FilterController::class, 'getUserExporters'])->name('api.filters.userExporters');
        Route::get('/api/filters/user/{uuid}/importers', [FilterController::class, 'getUserImporters'])->name('api.filters.userImporters');
        // Consignment-specific filter endpoints
        Route::get('/api/filters/consignment/exporters', [FilterController::class, 'getAllConsignmentExporters'])->name('api.filters.consignmentExporters');
        Route::get('/api/filters/consignment/importers', [FilterController::class, 'getAllConsignmentImporters'])->name('api.filters.consignmentImporters');
        Route::get('/api/filters/user/{uuid}/consignment/exporters', [FilterController::class, 'getUserConsignmentExporters'])->name('api.filters.userConsignmentExporters');
        Route::get('/api/filters/user/{uuid}/consignment/importers', [FilterController::class, 'getUserConsignmentImporters'])->name('api.filters.userConsignmentImporters');

        // Route::get('/application/exporter/get', [ApplicationController::class, 'get_exporter'])->name('application.exporter.get');

        //MISC - Restricted to non-boundary officers
        Route::get('/control_panel', [MiscController::class, 'showcontrolpanel']);
        Route::get('/state-district-management', [MiscController::class, 'showStateDistrictManagement'])->name('state-district-management');
        Route::get('/branch-management', [MiscController::class, 'showBranchManagement'])->name('branch-management');
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

        Route::post('/ip_condition/quick-add', [IpConditionController::class, 'quickAdd']);
        Route::get('/ip_condition/search/{country}', [IpConditionController::class, 'search']);
        Route::post('/ip_condition/{id}/add-alias', [IpConditionController::class, 'addAlias']);
        Route::post('/permit/{id}/link-condition', [PermitConsignmentController::class, 'linkCondition']);

        Route::post('/save_condition', [MiscController::class, 'saveCondition'])->name('saveCondition');
        Route::get('/permit_edit_condition/{id}', [MiscController::class, 'editCondition']);
        Route::delete('/permit_condition/{id}', [MiscController::class, 'deleteCondition']);
        Route::post('/news', [MiscController::class, 'shareNews']);

        // IMPORTER & EXPORTER LISTS
        Route::get('/importer/list', [ApplicationController::class, 'showInternalImporterList'])->name('importer.list');
        Route::get('/importer_list/data', [ApplicationController::class, 'getInternalImporterListData']);
        Route::get('/exporter/list', [ApplicationController::class, 'showInternalExporterList'])->name('exporter.list');
        Route::get('/exporter_list/data', [ApplicationController::class, 'getInternalExporterListData']);

        // BRANCH MANAGEMENT
        Route::get('/branches', [MiscController::class, 'getBranches']);
        Route::post('/branch/add', [MiscController::class, 'addBranch']);
        Route::post('/branch/update', [MiscController::class, 'updateBranch']);
        Route::delete('/branch/delete/{id}', [MiscController::class, 'deleteBranch']);

        // CONSIGNMENT CATEGORY
        Route::get('/consignment_categories', [MiscController::class, 'getConsignmentCategory']);

        // CONSIGNMENT CONDITION
        Route::get('/consignment_condition', [ConsignmentMiscController::class, 'showConsignmentCondition']);
        Route::get('/consignment_condition/data', [ConsignmentMiscController::class, 'getConsignmentConditionData']);
        Route::get('/consignment_condition/data/{id}', [ConsignmentMiscController::class, 'getConsignmentConditionDataById']);
        Route::get('/consignment_condition/edit/{id}', [ConsignmentMiscController::class, 'editConsignmentConditionDataById']);
        Route::post('/consignment_condition/save', [ConsignmentMiscController::class, 'saveCondition']);
        Route::get('/consignment_condition/add', [ConsignmentMiscController::class, 'addConsignmentConditionData']);
        Route::get('/permit_condition/usages', [MiscController::class, 'getDistinctUsage']);
        Route::get('/consignment_condition/usages', [ConsignmentMiscController::class, 'getDistinctUsage']);
        Route::delete('/consignment_condition/delete/{id}', [ConsignmentMiscController::class, 'deleteCondition']);

        // SHARE NEWS
        Route::post('/news', [MiscController::class, 'shareNews']);

        Route::get('/permit_condition', [MiscController::class, 'showpermitcondition'])->name('permitcondition');
        // Route::get('/permit_add_condition', [MiscController::class, 'permitaddcondition']);
        Route::post('/save_condition', [MiscController::class, 'saveCondition'])->name('saveCondition');
        // Route::get('/permit_edit_condition/{id}', [MiscController::class, 'editCondition']);
        // Route::get('/control_panel', [MiscController::class, 'showcontrolpanel'])->name('controlpanel');
        Route::post('/district/entry-point/update', [MiscController::class, 'updateEntry']);

        // BOUNDARY OFFICER MANAGEMENT - Restricted
        Route::get("/boundary/list", [BoundaryOfficerController::class, 'view'])->name('boundary.list');
        Route::get("/boundary/list/data", [BoundaryOfficerController::class, 'data']);
        Route::get('/boundary/{id}', [BoundaryOfficerController::class, 'getBoundaryData']);
        Route::post('/boundary/{id}/save', [BoundaryOfficerController::class, 'saveInternal']);
        Route::get('/get_entry_point', [PermitApplicationController::class, 'getEntryPoint']);

        // Admin dashboard - Restricted
        Route::get('/admin/dashboard/daily-volume', [AdminDashboardController::class, 'dailyVolume']);
        Route::get('/admin/dashboard/user-registration', [AdminDashboardController::class, 'userRegistration']);

        // ======================= notifications ===========================
        Route::post('/permit/{id}', [PermitConsignmentController::class, 'accept_permit']);

        // Inspection item accept/reject endpoints
        Route::post('/inspection_item/{id}/accept', [InspectionController::class, 'acceptInspectionItem']);
        Route::post('/inspection_item/{id}/reject', [InspectionController::class, 'rejectInspectionItem']);
        Route::post('/inspection/{id}/status', [InspectionController::class, 'updateStatus'])->name('inspection.status');

        Route::post('/consignment/{id}', [ConsignmentApplicationController::class, 'accept_permit']);

        // Document Requirements Management
        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('/documents/add', [DocumentController::class, 'create'])->name('documents.add');
        Route::get('/documents/{id}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
        Route::get('/documents/data', [DocumentController::class, 'data'])->name('documents.data');
        Route::get('/documents/view/{id}', [DocumentController::class, 'showView'])->name('documents.view');
        Route::get('/documents/{id}/attachments/data', [DocumentController::class, 'attachmentsData'])->name('documents.attachments.data');
        Route::get('/documents/{id}', [DocumentController::class, 'show'])->name('documents.show');
        Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::put('/documents/{id}', [DocumentController::class, 'update'])->name('documents.update');
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::post('documents/upload-file', [DocumentController::class, 'uploadFile']);
    });

// Publicly accessible location routes
Route::get('/get_states', [UserController::class, 'getStates']);
Route::get('/get_districts/{state_id}', [UserController::class, 'getDistricts']);
Route::get('/get_postcodes/{district_id}', [UserController::class, 'getPostcodes']);

Route::middleware(['auth.any'])->group(function () {
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::get('/data', [UserController::class, 'userData']);
    Route::post('/data', [UserController::class, 'updateData']);
    Route::post('/password', [UserController::class, 'password']);
    Route::get('/get_country', [PublicController::class, 'getCountry']);

    Route::get('/api/auth-user', [UserController::class, 'userInfo']);

    Route::get('/country/{code}', [DashboardController::class, 'get_country']);
    Route::get('/entry_point/{id}', [DashboardController::class, 'get_entry_point']);

    //============================= application ======================
    Route::get('/application/list/data', [ApplicationController::class, 'getallapplicationlist'])->name('application.data');
    Route::get('/application/export-excel', [ApplicationController::class, 'exportExcel'])->name('application.export_excel');
    Route::get('/application/export-pdf', [ApplicationController::class, 'exportPdf'])->name('application.export_pdf');
    Route::get('/application/review/list/data', [ApplicationController::class, 'getAllReviewapplicationList'])->name('application.review.data');
    Route::get('/application/agent/list/data', [ApplicationController::class, 'getAllAgentApplicationList'])->name('application.review.data');
    Route::get('/inspection_certificates_list/data', [InspectionController::class, 'getAllInspectionList'])->name('inspection.list.data');

    Route::get('/application/{id}/data', [ApplicationController::class, 'getApplicationDetails']);
    // Route::get('/view_application/{uuid}', [ApplicationController::class, 'viewapplication'])->name('viewApplication');

    Route::get('/edit_application/{uuid}', [ApplicationController::class, 'editApplication'])->name('editApplication');

    // TEST NEW DESIGN
    Route::get('/apply_import/test', [ApplicationController::class, 'applyTest']);
    Route::get('/apply_import/bahasa/test', [ApplicationController::class, 'applyTestBahasa']);
    // Route::get('/view_import/test/{uuid}', [ApplicationController::class, 'viewapplicationTest']);
    Route::get('/view_application/{uuid}', [ApplicationController::class, 'viewapplicationTest'])->name('viewApplication');
    Route::get('/summary_import/test', [ApplicationController::class, 'summaryTest']);
    Route::get('/list_import/test', [ApplicationController::class, 'listTest']);
    Route::get('/verify_import/test', [ApplicationController::class, 'verifyTest']);
    Route::get('/approve_permit/test', [ApplicationController::class, 'approveTest']);
    Route::get('/payment_permit/test', [ApplicationController::class, 'paymentTest']);
    Route::get('/order_payment/test', [ApplicationController::class, 'orderTest']);
    Route::get('/receipt_payment/test', [ApplicationController::class, 'receiptTest']);
    Route::get('/control_panel/test', [ApplicationController::class, 'controlPanelTest']);

    Route::get('/view_consignment/{uuid}', [ConsignmentApplicationController::class, 'viewapplication'])->name('consignment.view');
    Route::get('/edit_consignment/{uuid}', [ConsignmentApplicationController::class, 'editApplication'])->name('consignment.edit');
    Route::get('/consignment_application/{id}/data', [ConsignmentController::class, 'getApplicationDetails']);
    Route::get('/consignment/attachment/{id}', [ConsignmentApplicationController::class, 'viewAttachment'])->name('consignment.attachment.view');
    Route::get('/consignment/list/data', [ConsignmentController::class, 'getallconsignmentlist'])->name('consignment.data');
    Route::get('/consignment/export-excel', [ConsignmentController::class, 'exportExcel'])->name('consignment.export_excel');
    Route::get('/consignment/export-pdf', [ConsignmentController::class, 'exportPdf'])->name('consignment.export_pdf');
    Route::post('/consignment/verify/{id}/', [ConsignmentController::class, 'verify_application_permit']);

    Route::get('/inspection/export-excel', [InspectionController::class, 'exportExcel'])->name('inspection.export_excel');
    Route::get('/inspection/export-pdf', [InspectionController::class, 'exportPdf'])->name('inspection.export_pdf');
    Route::get('/view_inspection_certificates/{id}', [InspectionController::class, 'viewInspection'])->name('inspection.view_details');
    Route::get('/inspection_application/{id}/data', [InspectionController::class, 'getApplicationDetails']);

    Route::get('/application/permit/{id}/data', [ApplicationController::class, 'get_application_permit']);
    Route::post('/application/verify/{id}/', [ApplicationController::class, 'verify_application_permit']);
    Route::get('/application/{id}/email-action/{action}', [ApplicationController::class, 'handleEmailAction'])->name('application.email.action');

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

        $markedCount = 0;
        foreach ($notifications as $notification) {
            /** @var \Illuminate\Notifications\DatabaseNotification $notification */
            if (!$notification->read_at) {
                $notification->markAsRead();
                $markedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "{$markedCount} notification(s) marked as read.",
            'marked_count' => $markedCount
        ]);
    })->name('notifications.mark-read');

    // ✅ Mark ALL notifications as read (from notification page)
    Route::post('/notifications/mark-read-all', function () {
        $type = authUser()['type'];
        $user = authUser()['user'];

        // Get ALL unread notifications for this user
        $unreadNotifications = DatabaseNotification::where('notifiable_type', $type)
            ->where('notifiable_id', $user->uuid)
            ->whereNull('read_at')
            ->get();

        $markedCount = 0;
        foreach ($unreadNotifications as $notification) {
            /** @var \Illuminate\Notifications\DatabaseNotification $notification */
            $notification->markAsRead();
            $markedCount++;
        }

        return response()->json([
            'status' => 'success',
            'message' => "All {$markedCount} unread notification(s) marked as read.",
            'marked_count' => $markedCount
        ]);
    })->name('notifications.mark-read-all');

    // ✅ Mark single notification as read
    Route::post('/notifications/mark-read-single', function (Request $request) {
        $notification = DatabaseNotification::find($request->notification_id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.'
            ], 404);
        }

        $type = authUser()['type'];
        $user = authUser()['user'];

        // Ensure the notification belongs to the current user
        if ($notification->notifiable_type !== $type || $notification->notifiable_id !== $user->uuid) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to mark this notification as read.'
            ], 403);
        }

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.'
        ]);
    })->name('notifications.mark-read-single');

    Route::get('/notifications', [DashboardController::class, 'notifications_page'])->name('notifications.page');
    Route::delete('/inspection/delete/{id}', [InspectionController::class, 'deleteApplication'])->name('inspection.delete');
    Route::get('/application/exporter', [ApplicationController::class, 'show_exporter'])->name('application.exporter');
    Route::get('/application/exporter/{id}', [ApplicationController::class, 'get_exporter']);
    Route::get('/application/importer', [ApplicationController::class, 'show_importer'])->name('application.importer');
    Route::get('/application/importer/{id}', [ApplicationController::class, 'get_importer']);

    Route::get('/permit/generate/{permit_number}', [PermitGenerateController::class, 'generatePermitWord']);
    Route::get('/permit/generate/pdf/{permit_number}', [PermitGenerateController::class, 'generatePermitPdf']);
    Route::get('/permit/generate/consignment/{permit_number}', [PermitGenerateController::class, 'generateConsignmentPermitWord']);



    // list down all the inspection
    Route::get('/inspection/generate/{id}', [PermitGenerateController::class, 'generateInspection']);
    Route::get('/consignment/generate/{id}', [PermitGenerateController::class, 'generateConsignmentApplication']);

    Route::get('/payment/{id}/{permitId}/{total}/{type}', [PaymentController::class, 'checkout'])
        ->name('payment.checkout')
        ->middleware('signed');

    Route::post('/payment/signed-url', [PaymentController::class, 'signedUrl'])->name('payment.signed.url');

    Route::post('/payment', [PaymentController::class, 'payment']);

    Route::post('/payment/cancel', [PaymentController::class, 'cancelPayment']);


    // VIEW PAYMENT - Restricted
    Route::get('/order/list', [ApplicationPaymentController::class, 'getView']);
    Route::get('/order/list/data', [ApplicationPaymentController::class, 'getAllOrderList']);
    Route::get('/order/qr-scan-logs', [ApplicationPaymentController::class, 'getQrScanLogs']);
    Route::get('/order/encrypted-qr-payload', [ApplicationPaymentController::class, 'getEncryptedPermitPayload']);
    Route::get('/order/{order_number}', [ApplicationPaymentController::class, 'orderDetails']);

    // PERMIT
    Route::get('/permit/list/import', [PermitConsignmentController::class, 'getView']);
    Route::get('/permit/list/import/data', [PermitConsignmentController::class, 'getAllpermitList']);
    Route::get('/permit/import/{permit_number}', [PermitConsignmentController::class, 'permitDetails']);

    // INSPECTION CERTIFICATE PERMIT
    Route::get('/permit/list/inspection', [InspectionPermitController::class, 'getView']);
    Route::get('/permit/list/inspection/data', [InspectionPermitController::class, 'getAllpermitList']);

    Route::get('/permit/list/consignment', [ConsignmentPermitController::class, 'getView']);
    Route::get('/permit/list/consignment/data', [ConsignmentPermitController::class, 'getAllPermitList']);
    Route::get('/permit/inspection/{permit_number}', [InspectionPermitController::class, 'permitDetails']);

    // Route::post('/')

    // bounce url
    // Route::get('/paymentUpdate/{kod_transaksi}', [PaymentController::class, 'bounce']);
    Route::get('/paymentUpdate/{rn}', [PaymentController::class, 'paymentUpdate'])->name('payment.update');

    // count 
    Route::post('/permit/print', [PermitGenerateController::class, 'permitCount']);



    // dashboard
    Route::get('/application/count', [DashboardController::class, 'applicationCount']);

    Route::get('/measurement', [MiscController::class, 'measurementUnit']);


    Route::get('/vehicles/details', [VehicleController::class, 'getVehiclesByIds'])->name('vehicles.details');
    Route::get('/vehicles/list', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/data', [VehicleController::class, 'data'])->name('vehicles.data');
    Route::get('/vehicles/{id}', [VehicleController::class, 'show'])->name('vehicles.show');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::put('/vehicles/{id}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::delete('/vehicles/{id}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');


    // consignment item
    Route::get('/get_pbdata/{cate}', [MiscController::class, 'getpbdata']);
    Route::get('/consignment_condition/category/{category}/{countryCode}/data', [ConsignmentMiscController::class, 'getConsignmentConditionDataByCategory']);


    // get importers
    Route::get('/get_consignment_importers', [ConsignmentApplicationController::class, 'getConsignmentImporters'])->name('getConsignmentImporters');
    Route::get('/vehicle/data', [VehicleController::class, 'getVehicleList']);



    // export
    Route::get('/permit_condition/export', [MiscController::class, 'exportExcel'])
        ->name('permit_condition.export');


    // download to pdf
    Route::get('/consignment/application/{id}/print', [ConsignmentController::class, 'printApplication'])
        ->name('consignment.application.print');

    Route::get('/import/application/{id}/print', [ApplicationController::class, 'printImportPermit'])->name('import-permit.print');
    Route::get('/inspection/application/{id}/print', [InspectionController::class, 'printInspection'])->name('inspection.print');

    // uses
    Route::get('/consignment_uses', [PublicController::class, 'getConsignmentUses'])->name('consignment.uses');
    Route::get('/get_consignment/{countryCode}', [PermitApplicationController::class, 'getConsignmentFromCountry'])->name('getItemFromCountry');




    Route::get('documents/temp-preview/{filename}', [DocumentController::class, 'tempPreview'])->name('documents.temp-preview');
});

// broadcast --dont kacau---
// Broadcast::routes();
// Broadcast::routes(['middleware' => ['auth:internal']]);
// Broadcast::routes(['middleware' => ['auth:internal']]);
Route::get('/email', function () {
    return view('email.notify_email', [
        'title' => 'Test Email'
    ]);
});
