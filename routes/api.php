<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StateDistrictController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ApplicationPaymentController;

Route::post('/internal/login', [AuthenticationController::class, 'internalLoginApi']);
Route::post('/internal/logout', [AuthenticationController::class, 'internalLogoutApi'])->middleware('auth:internal-api');

Route::middleware('api')->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return [
            'uuid' => $user->uuid ?? null,
            'name' => $user->fullname ?? null,
            'email' => $user->email ?? null,
            'roles' => method_exists($user, 'getRoleNames')
                ? ($user->getRoleNames()->first() ?? null)
                : null,
        ];
    })->middleware('auth:sanctum');

    // State and District endpoints
    Route::get('/states', [StateDistrictController::class, 'getStates']);
    Route::get('/districts/{stateId}', [StateDistrictController::class, 'getDistricts']);
    Route::get('/all-districts', [StateDistrictController::class, 'getAllDistricts']);
    
    // Create and delete endpoints for admin
    Route::post('/districts', [StateDistrictController::class, 'storeDistrict']);
    Route::delete('/districts/{districtId}', [StateDistrictController::class, 'destroyDistrict']);
    Route::delete('/states/{stateId}', [StateDistrictController::class, 'destroyState']);
});

Route::get('/permit/validate', [ApplicationPaymentController::class, 'validatePermitApi'])->middleware('auth:internal-api');
Route::get('/permits/pending', [ApplicationPaymentController::class, 'pendingPermitsApi'])->middleware('auth:internal-api');
Route::post('/permits/search', [ApplicationPaymentController::class, 'searchPermitsApi'])->middleware('auth:internal-api');
Route::post('/qr-scan/complete-scan', [ApplicationPaymentController::class, 'completeQrScan'])->middleware('auth:internal-api');
Route::get('/qr-scan/history', [ApplicationPaymentController::class, 'getScanHistoryApi'])->middleware('auth:internal-api');
Route::get('/qr-scan/history/all', [ApplicationPaymentController::class, 'getAllScanHistoryApi'])->middleware('auth:internal-api');
Route::get('/order/details/{order_number}', [ApplicationPaymentController::class, 'orderDetailsApi'])->middleware('auth:internal-api');