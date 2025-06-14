<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthJWTMiddleware;
use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\Auth\TouristsController;
use App\Http\Controllers\Auth\PartnersController;
use App\Http\Controllers\Partners\ServiceController;
use App\Http\Controllers\Public\PublicServiceController;
use App\Http\Controllers\Tourist\ServiceController as TouristServiceController;
use App\Http\Controllers\Tourist\ServiceWishlistController;
use App\Http\Controllers\Public\DistrictController;
use App\Http\Controllers\Tourist\TripController;

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
    Route::get('service/{service}/reviews', [PublicServiceController::class, 'getReviews']);

    // Districts endpoint
    Route::get('/districts', [DistrictController::class, 'index']);
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

// Partner routes with JWT authentication
Route::prefix('tourist')->middleware(['jwt'])->group(function () {    
    // Wishlist routes
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [ServiceWishlistController::class, 'getWishlist']);
        Route::post('/', [ServiceWishlistController::class, 'addToWishlist']);
        Route::delete('/{service}', [ServiceWishlistController::class, 'removeFromWishlist']);
        Route::post('/{service}/review', [ServiceWishlistController::class, 'addRatingAndReview']);
    });

    // Service booking routes
    Route::prefix('bookings')->group(function () {
        Route::get('/active', [TouristServiceController::class, 'getActiveBookings']);
        Route::get('/past', [TouristServiceController::class, 'getPastBookings']);
        Route::get('/canceled', [TouristServiceController::class, 'getCanceledBookings']);
        Route::get('/{booking}', [TouristServiceController::class, 'getBookingDetails']);
    });

    // Tourist Trip Routes
    Route::prefix('trip')->group(function () {
        Route::post('/plan', [TripController::class, 'planTrip']);
        Route::get('/{id}', [TripController::class, 'getTrip']);
        Route::get('/completed', [TripController::class, 'getCompletedTrips']);
    });
});
