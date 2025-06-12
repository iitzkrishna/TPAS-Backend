<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthJWTMiddleware;
use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\Auth\TouristsController;
use App\Http\Controllers\Auth\PartnersController;
use App\Http\Controllers\Partners\ServiceController;
use App\Http\Controllers\Public\PublicServiceController;


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

// Public routes
Route::prefix('public')->group(function () {
    // Service type specific endpoints
    Route::get('/stays', [PublicServiceController::class, 'stays']);
    Route::get('/stays/{service}', [PublicServiceController::class, 'show']);
    
    Route::get('/rental', [PublicServiceController::class, 'rental']);
    Route::get('/rental/{service}', [PublicServiceController::class, 'show']);
    
    Route::get('/attractions', [PublicServiceController::class, 'attractions']);
    Route::get('/attractions/{service}', [PublicServiceController::class, 'show']);

    // Service reviews endpoint
    Route::get('/{service}/reviews', [PublicServiceController::class, 'getReviews']);
});

// Partner routes with JWT authentication
Route::prefix('partner')->middleware(['jwt'])->group(function () {
    // Service management routes
    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::post('/', [ServiceController::class, 'store']);
        Route::get('/types', [ServiceController::class, 'getServiceTypes']);
        Route::get('/{service}', [ServiceController::class, 'show']);
        Route::put('/{service}', [ServiceController::class, 'update']);
        Route::delete('/{service}', [ServiceController::class, 'destroy']);
        Route::delete('/{service}/images/{image}', [ServiceController::class, 'deleteServiceImage']);
        Route::get('/{service}/reviews', [ServiceController::class, 'getServiceReviews']);
    });
});
