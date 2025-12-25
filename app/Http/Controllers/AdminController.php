<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\BaseController as APIController;
use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Exception;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Show the admin dashboard.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_apartments' => Apartment::count(),
            'available_apartments' => Apartment::where('status', 'available')->count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'total_revenue' => Booking::where('status', 'confirmed')->sum('total_price')
        ];

        return $this->sendResponse($stats, 'admin dashboard statistics retrieved');
    }

    /**
     * Display a listing of pending users.
     *
     * @return JsonResponse
     */
    public function pendingUsers(): JsonResponse
    {
        $users = User::where('status', 'pending')->paginate(20);
        return $this->sendPaginatedResponse($users, 'pending users retrieved');
    }

    /**
     * Display a listing of all users.
     *
     * @return JsonResponse
     */
    public function users(): JsonResponse
    {
        $users = User::paginate(20);
        return $this->sendPaginatedResponse($users, 'users retrieved');
    }

    /**
     * Approve a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return JsonResponse
     */
    public function approveUser(Request $request, User $user): JsonResponse
    {
        // Check if user is already approved
        if ($user->status === 'active') {
            return $this->sendError('User already approved.');
        }

        try {
            // Update user status to "active"
            $user->update(['status' => 'active']);
            
            return $this->sendResponse([
                'message' => 'User approved successfully.',
                'user' => $user,
            ]);

        } catch (Exception $e) {
            return $this->sendError('User approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return JsonResponse
     */
    public function rejectUser(Request $request, User $user): JsonResponse
    {
        // Check if action is invalid (if user is already rejected or active)
        if ($user->status === 'rejected' || $user->status === 'active') {
            return $this->sendError('Invalid action.');
        }

        try {
            // Delete user images from storage
            if ($user->id_image) {
                Storage::disk('public')->delete($user->id_image);
            }
            
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Delete user
            $user->delete();

            return $this->sendResponse([], 'User rejected successfully.');

        } catch (Exception $e) {
            return $this->sendError('User rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Deposit money to a user's wallet
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return JsonResponse
     */
    public function deposit(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        try {
            // Ensure user is a tenant
            if (!$user->isTenant()) {
                return $this->sendError('Only tenants can have wallet deposits');
            }
            
            // Create wallet if it doesn't exist
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = new Wallet(['user_id' => $user->id, 'balance' => 0]);
                $user->wallet()->save($wallet);
            }

            // Update balance
            $wallet->balance = (float)(($wallet->balance ?? 0) + $request->amount);
            $wallet->save();

            return $this->sendResponse([
                'wallet' => $wallet,
                'message' => 'Deposit successful. New balance: $' . number_format($wallet->balance ?? 0, 2)
            ], 'Deposit successful.');

        } catch (Exception $e) {
            return $this->sendError('Deposit failed: ' . $e->getMessage());
        }
    }


}