<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::get('/admin/users/pending', [AdminController::class, 'pendingUsers'])->name('admin.users.pending');
    Route::post('/admin/users/{user}/approve', [AdminController::class, 'approveUser'])->name('admin.users.approve');
    Route::post('/admin/users/{user}/reject', [AdminController::class, 'rejectUser'])->name('admin.users.reject');
    
    // Wallet Management Routes
    Route::get('/admin/wallet/deposit/{user}', [AdminController::class, 'showDepositForm'])->name('admin.wallet.deposit.form');
    Route::post('/admin/wallet/deposit/{user}', [AdminController::class, 'deposit'])->name('admin.wallet.deposit');
});
