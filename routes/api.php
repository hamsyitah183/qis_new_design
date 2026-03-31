<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StateDistrictController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ApplicationPaymentController;

Route::middleware('api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
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

Route::post('/login', [AuthenticationController::class, 'loginActionApi'])->name('login.action');
Route::get('/permit/validate', [ApplicationPaymentController::class, 'validatePermitApi']);
Route::get('/order/details/{order_number}', [ApplicationPaymentController::class, 'orderDetailsApi']);
