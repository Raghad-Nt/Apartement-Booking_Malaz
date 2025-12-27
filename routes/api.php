<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ApartmentController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\AdminController;

// Authentication Routes
Route::post('/register', [RegisterController::class, 'register']); // Register new user
Route::post('/login', [RegisterController::class, 'login']); // Login existing user

// Protected Routes (Require Authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User Profile Routes
    Route::get('/user', [RegisterController::class, 'profile']); // Get authenticated user profile
    Route::put('/user', [RegisterController::class, 'updateProfile']); // Update authenticated user profile
    Route::post('/logout', [RegisterController::class, 'logout']); // Logout authenticated user

 
    
    // Wallet Routes (Accessible to all authenticated users)
    Route::get('/wallet/balance/{user}', [WalletController::class, 'balance']); // Get wallet balance
    
    // Public Apartment Routes (Accessible to all authenticated users)
    Route::get('/apartments', [ApartmentController::class, 'index']); // List all apartments
    Route::get('/apartments/{apartment}', [ApartmentController::class, 'show']); // Show specific apartment
    Route::get('/apartments/{apartment}/reviews', [ReviewController::class, 'apartmentReviews']); // Get reviews for specific apartment

    // Apartment Management Routes (Owner/Renter only)
    Route::post('/apartments', [ApartmentController::class, 'store']); // Create new apartment
    Route::put('/apartments/{apartment}', [ApartmentController::class, 'update']); // Update existing apartment
    Route::delete('/apartments/{apartment}', [ApartmentController::class, 'destroy']); // Delete apartment
    
    // Favorite Apartment Routes
    Route::post('/apartments/{apartment}/favorite', [ApartmentController::class, 'addToFavorites']); // Add apartment to favorites
    Route::delete('/apartments/{apartment}/favorite', [ApartmentController::class, 'removeFromFavorites']); // Remove apartment from favorites
    Route::get('/favorites', [ApartmentController::class, 'favorites']); // List user's favorite apartments
    
    // Booking Routes
    Route::post('/bookings', [BookingController::class, 'store']); // Create new booking
    Route::get('/bookings', [BookingController::class, 'index']); // List all bookings (with filters)
    Route::get('/bookings/{booking}', [BookingController::class, 'show']); // Show specific booking
    Route::put('/bookings/{booking}', [BookingController::class, 'update']); // Update booking status (owner/admin) - includes modification approval
    Route::put('/bookings/{booking}/details', [BookingController::class, 'updateDetails']); // Update booking details (user)
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']); // Cancel booking (user)
    Route::get('/my-bookings', [BookingController::class, 'myBookings']); // List current user's bookings
        
    // Review Routes
    Route::post('/reviews', [ReviewController::class, 'store']); // Create/update review
    Route::get('/reviews', [ReviewController::class, 'index']); // List all reviews (with filters)
    Route::get('/reviews/{review}', [ReviewController::class, 'show']); // Show specific review
    Route::put('/reviews/{review}', [ReviewController::class, 'update']); // Update review (owner)
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']); // Delete review (owner/admin)
    
    // Messaging Routes
    Route::post('/messages/send', [MessageController::class, 'send']); // Send message to user
    Route::get('/messages/inbox', [MessageController::class, 'inbox']); // Get user's message inbox
    Route::get('/messages/conversation/{user}', [MessageController::class, 'conversation']); // Get conversation with specific user
    Route::get('/messages/apartment/{apartment}', [MessageController::class, 'apartmentMessages']); // Get messages related to specific apartment
    
    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index']); // Get user's notifications
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']); // Mark notification as read
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']); // Mark all notifications as read
});