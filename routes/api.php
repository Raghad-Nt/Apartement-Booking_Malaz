<?php
  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ApartmentController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\AdminController;



Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [RegisterController::class, 'login']);

// Public routes
Route::get('/apartments', [ApartmentController::class, 'index']);
Route::get('/apartments/{apartment}', [ApartmentController::class, 'show']);
Route::get('/apartments/{apartment}/reviews', [ReviewController::class, 'apartmentReviews']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [RegisterController::class, 'profile']);
    Route::put('/user', [RegisterController::class, 'updateProfile']);
    
    // Apartment routes for owners (renters)
    Route::post('/apartments', [ApartmentController::class, 'store']);
    Route::put('/apartments/{apartment}', [ApartmentController::class, 'update']);
    Route::delete('/apartments/{apartment}', [ApartmentController::class, 'destroy']);
    
    // Favorite routes
    Route::post('/apartments/{apartment}/favorite', [ApartmentController::class, 'toggleFavorite']);
    Route::get('/favorites', [ApartmentController::class, 'favorites']);
    
    // Booking routes
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']); // For owner/admin to confirm/reject
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']); // For user to cancel
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    
    // Review routes
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{review}', [ReviewController::class, 'show']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
    
    // Message routes
    Route::post('/messages/send', [MessageController::class, 'send']);
    Route::get('/messages/inbox', [MessageController::class, 'inbox']);
    Route::get('/messages/conversation/{user}', [MessageController::class, 'conversation']);
    
    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin/users/pending', [AdminController::class, 'pendingUsers']);
        Route::post('/admin/users/{user}/approve', [AdminController::class, 'approveUser']);
        Route::post('/admin/users/{user}/reject', [AdminController::class, 'rejectUser']);
        Route::get('/admin/statistics', [AdminController::class, 'statistics']);
    });
});
