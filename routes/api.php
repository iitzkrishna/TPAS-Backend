<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AuthSPController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TouristSOSController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Tourist Routes (Main User Routes)
// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('verify-email', [AuthController::class, 'verifyEmail']);
Route::post('create-password', [AuthController::class, 'createPassword']);

// Protected Tourist Routes
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('user', [AuthController::class, 'user']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::put('change-password', [AuthController::class, 'changePassword']);

    // Tourist specific routes
    // Route::post('location', [TouristSOSController::class, 'updateLocation']);
    // Route::get('location', [TouristSOSController::class, 'getCurrentLocation']);
    // Route::get('location/history', [TouristSOSController::class, 'getLocationHistory']);
});

// Service Provider Routes
Route::prefix('sp')->group(function () {
    // Public routes
    Route::post('register', [AuthSPController::class, 'register']);
    Route::post('login', [AuthSPController::class, 'login']);
    Route::post('forgot-password', [AuthSPController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthSPController::class, 'resetPassword']);
    Route::post('verify-email', [AuthSPController::class, 'verifyEmail']);
    Route::post('create-password', [AuthSPController::class, 'createPassword']);

    // Protected routes
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthSPController::class, 'logout']);
        Route::post('refresh', [AuthSPController::class, 'refresh']);
        Route::get('user', [AuthSPController::class, 'user']);
        Route::put('profile', [AuthSPController::class, 'updateProfile']);
        Route::put('business', [AuthSPController::class, 'updateBusinessInfo']);
        Route::get('business', [AuthSPController::class, 'getBusinessInfo']);
        Route::put('change-password', [AuthSPController::class, 'changePassword']);
    });
});

// Location Routes (Public)
Route::prefix('locations')->group(function () {
    // Route::get('provinces', [LocationController::class, 'getProvinces']);
    // Route::get('provinces/{id}', [LocationController::class, 'getProvinceWithDistricts']);
    Route::get('districts', [LocationController::class, 'getDistricts']);
    Route::get('districts/{id}', [LocationController::class, 'getDistrictWithProvince']);
}); 