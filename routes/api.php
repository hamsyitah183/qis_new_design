<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StateDistrictController;

Route::middleware('api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::get('/states', [StateDistrictController::class, 'getStates']);
    Route::get('/districts/{stateId}', [StateDistrictController::class, 'getDistricts']);
    Route::get('/all-districts', [StateDistrictController::class, 'getAllDistricts']);
});