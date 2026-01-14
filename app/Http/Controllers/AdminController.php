<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Notifications\AdminWalletDeposit;

class AdminController extends Controller
{
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

    public function users()
    {
        $users = User::paginate(1000);
        return view('admin.users.index', compact('users'))->with('title', 'All Users');
    }

    public function pendingUsers()
    {
        $users = User::where('status', 'pending')->paginate(20);
        return view('admin.users.index', compact('users'))->with('title', 'Pending Approvals');
    }

    public function approveUser(User $user)
    {
        if ($user->status === 'active') {
            return back()->with('error', 'User already approved.');
        }

        $user->update(['status' => 'active']);
        return back()->with('success', 'User approved successfully.');
    }

    public function rejectUser(User $user)
    {
        try {
            if ($user->id_image) Storage::disk('public')->delete($user->id_image);
            if ($user->profile_image) Storage::disk('public')->delete($user->profile_image);

            $user->delete();
            return back()->with('success', 'User rejected and removed.');
        } catch (Exception $e) {
            return back()->with('error', 'Rejection failed.');
        }
    }

    public function deposit(Request $request, User $user)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        try {
            $wallet = $user->wallet ?: new Wallet(['user_id' => $user->id, 'balance' => 0]);
            $wallet->balance += $request->amount;
            $user->wallet()->save($wallet);

            // Send notification to the tenant about the deposit
            $user->notify(new AdminWalletDeposit($request->amount, $user->id));

            return back()->with('success', "Deposited $" . number_format($request->amount, 2) . " to {$user->name}");
        } catch (Exception $e) {
            return back()->with('error', 'Deposit failed.');
        }
    }

    // Show the login form
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/admin/dashboard');
        }
        return view('auth.login');
    }

    // Handle the login logic
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
