<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
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

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Display a listing of pending users.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function pendingUsers()
    {
        $users = User::where('status', 'pending')->paginate(20);
        return view('admin.users.pending', compact('users'));
    }

    /**
     * Display a listing of all users.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function users()
    {
        $users = User::paginate(20);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Approve a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveUser(Request $request, User $user)
    {
        // Check if user is already approved
        if ($user->status === 'active') {
            return redirect()->back()->with('error', 'User already approved.');
        }

        try {
            // Update user status to "active"
            $user->update(['status' => 'active']);
            
            return redirect()->back()->with('success', 'User approved successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'User approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectUser(Request $request, User $user)
    {
        // Check if action is invalid (if user is already rejected or active)
        if ($user->status === 'rejected' || $user->status === 'active') {
            return redirect()->back()->with('error', 'Invalid action.');
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

            return redirect()->back()->with('success', 'User rejected successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'User rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Show the deposit form for a user
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showDepositForm(User $user)
    {
        return view('admin.wallets.deposit', compact('user'));
    }

    /**
     * Deposit money to a user's wallet
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deposit(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        try {
            // Ensure user is a tenant
            if (!$user->isTenant()) {
                return redirect()->back()->with('error', 'Only tenants can have wallet deposits');
            }
            
            // Create wallet if it doesn't exist
            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = new Wallet(['user_id' => $user->id, 'balance' => 0]);
                $user->wallet()->save($wallet);
            }

            // Update balance
            $wallet->balance += $request->amount;
            $wallet->save();

            return redirect()->back()->with('success', 'Deposit successful. New balance: $' . number_format($wallet->balance, 2));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Deposit failed: ' . $e->getMessage());
        }
    }
}