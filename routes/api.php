<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthJWTMiddleware;
use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\Auth\TouristsController;
use App\Http\Controllers\Auth\PartnersController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TouristSOSController;
use App\Http\Controllers\Partners\ServiceController;
use App\Http\Controllers\Public\ServiceController as PublicServiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Admin Routes
Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('login', [AdminController::class, 'login']);

    // Protected Admin Routes
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AdminController::class, 'logout']);
        Route::post('refresh', [AdminController::class, 'refresh']);
        Route::get('user', [AdminController::class, 'user']);
    });
});

// Tourist Routes
Route::prefix('auth/tourist')->group(function () {
    // Public routes
    Route::post('register', [TouristsController::class, 'register']);
    Route::post('login', [TouristsController::class, 'login']);
    Route::post('forgot-password', [TouristsController::class, 'forgotPassword']);
    Route::post('reset-password', [TouristsController::class, 'resetPassword']);
    Route::post('verify-email', [TouristsController::class, 'verifyEmail']);
    Route::post('create-password', [TouristsController::class, 'createPassword']);

    // Protected Tourist Routes
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [TouristsController::class, 'logout']);
        Route::post('refresh', [TouristsController::class, 'refresh']);
        Route::get('user', [TouristsController::class, 'user']);
        Route::put('profile', [TouristsController::class, 'updateProfile']);
        Route::put('change-password', [TouristsController::class, 'changePassword']);

        // Tourist specific routes
        // Route::post('location', [TouristSOSController::class, 'updateLocation']);
        // Route::get('location', [TouristSOSController::class, 'getCurrentLocation']);
        // Route::get('location/history', [TouristSOSController::class, 'getLocationHistory']);
    });
});

// Service Provider Routes
Route::prefix('auth/partner')->group(function () {
    // Public routes
    Route::post('register', [PartnersController::class, 'register']);
    Route::post('login', [PartnersController::class, 'login']);
    Route::post('forgot-password', [PartnersController::class, 'forgotPassword']);
    Route::post('reset-password', [PartnersController::class, 'resetPassword']);
    Route::post('verify-email', [PartnersController::class, 'verifyEmail']);
    Route::post('create-password', [PartnersController::class, 'createPassword']);

    // Protected routes
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [PartnersController::class, 'logout']);
        Route::post('refresh', [PartnersController::class, 'refresh']);
        Route::get('user', [PartnersController::class, 'user']);
        Route::put('profile', [PartnersController::class, 'updateProfile']);
        Route::put('business', [PartnersController::class, 'updateBusinessInfo']);
        Route::get('business', [PartnersController::class, 'getBusinessInfo']);
        Route::put('change-password', [PartnersController::class, 'changePassword']);
    });
});

// Partner Service Routes
Route::prefix('partner/services')->middleware(['jwt.auth', 'service.provider'])->group(function () {
    Route::get('/', [ServiceController::class, 'index']);
    Route::post('/', [ServiceController::class, 'store']);
    Route::get('/{service}', [ServiceController::class, 'show']);
    Route::put('/{service}', [ServiceController::class, 'update']);
    Route::delete('/{service}', [ServiceController::class, 'destroy']);
    Route::delete('/{service}/images/{image}', [ServiceController::class, 'deleteServiceImage']);
});

// Location Routes (Public)
Route::prefix('locations')->group(function () {
    // Route::get('provinces', [LocationController::class, 'getProvinces']);
    // Route::get('provinces/{id}', [LocationController::class, 'getProvinceWithDistricts']);
    Route::get('districts', [LocationController::class, 'getDistricts']);
    Route::get('districts/{id}', [LocationController::class, 'getDistrictWithProvince']);
});

// Public routes
Route::prefix('public')->group(function () {
    Route::get('/services', [PublicServiceController::class, 'index']);
    Route::get('/services/{service}', [PublicServiceController::class, 'show']);
}); 