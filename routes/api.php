<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HotelApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\PaymentApiController;

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Hotels & Rooms
Route::get('/hotels', [HotelApiController::class, 'index']);
Route::get('/hotels/{id}', [HotelApiController::class, 'show']);
Route::get('/search-locations', [HotelApiController::class, 'searchLocations']);
Route::get('/rooms/recommended', [\App\Http\Controllers\Api\RoomApiController::class, 'recommended']);

// Promotions & Contact
Route::get('/coupons', [\App\Http\Controllers\Api\CouponApiController::class, 'index']);
Route::post('/contact', [\App\Http\Controllers\Api\ContactApiController::class, 'store']);

// Payment Callbacks
Route::get('/payments/vnpay/callback', [PaymentApiController::class, 'vnpayCallback']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    
    // Cart
    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/cart/add', [CartApiController::class, 'store']);
    Route::delete('/cart/remove/{id}', [CartApiController::class, 'destroy']);
    
    // Booking
    Route::get('/bookings', [BookingApiController::class, 'index']);
    Route::post('/bookings', [BookingApiController::class, 'store']);
    
    // Payment Init
    Route::post('/payments/init', [PaymentApiController::class, 'initPayment']);
    
    // User Profile
    Route::put('/user', [AuthController::class, 'updateProfile']);
});
