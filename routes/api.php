<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HotelApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\FeedbackApiController;

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Hotels & Rooms
Route::get('/hotels', [HotelApiController::class, 'index']);
Route::get('/hotels/{id}', [HotelApiController::class, 'show']);
Route::get('/rooms/recommended', [\App\Http\Controllers\Api\RoomApiController::class, 'recommended']);
Route::get('/rooms/{id}', [\App\Http\Controllers\Api\RoomApiController::class, 'show']);
Route::post('/rooms/{id}/check-availability', [\App\Http\Controllers\Api\RoomApiController::class, 'checkAvailability']);

// Search Suggestions
Route::get('/search/suggestions', [\App\Http\Controllers\Api\SearchApiController::class, 'suggestions']);

// Feedback (Public view)
Route::get('/feedback', [FeedbackApiController::class, 'index']);

// Promotions & Contact
Route::get('/coupons', [\App\Http\Controllers\Api\CouponApiController::class, 'index']);
Route::post('/contact', [\App\Http\Controllers\Api\ContactApiController::class, 'store']);

// Payment Callbacks
Route::get('/payments/vnpay/callback', [PaymentApiController::class, 'vnpayCallback']);
Route::post('/payments/sepay/webhook', [\App\Http\Controllers\Api\SePayWebhookController::class, 'handle']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    
    // Cart
    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/cart/add', [CartApiController::class, 'store']);
    Route::delete('/cart/remove/{id}', [CartApiController::class, 'destroy']);
    Route::post('/cart/verify', [CartApiController::class, 'verify']);
    
    // Booking
    Route::get('/bookings', [BookingApiController::class, 'index']);
    Route::post('/bookings/hold', [BookingApiController::class, 'hold'])->middleware('throttle:hold-room');
    Route::post('/bookings', [BookingApiController::class, 'store']);
    Route::post('/bookings/preview', [BookingApiController::class, 'preview']);
    Route::post('/bookings/apply-coupon', [BookingApiController::class, 'applyCoupon']);
    Route::delete('/bookings/{id}', [BookingApiController::class, 'destroy']);
    
    // Feedback (Store)
    Route::post('/feedback', [FeedbackApiController::class, 'store']);

    // Refund Requests
    Route::post('/refund-requests', [\App\Http\Controllers\Api\RefundRequestApiController::class, 'store']);
    
    // Payment Init
    Route::post('/payments/init', [PaymentApiController::class, 'initPayment']);
    Route::get('/payments/status', [PaymentApiController::class, 'paymentStatus']);
    
    // User Profile
    Route::put('/user', [AuthController::class, 'updateProfile']);
});
