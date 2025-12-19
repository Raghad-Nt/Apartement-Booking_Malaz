<?php

namespace App\Http\Controllers\API;


use App\Http\Resources\UserResource;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends BaseController
{
    
       
     
    public function pendingUsers(Request $request)
{
    $users = User::where('status', 'pending')->paginate(20);

    return $this->sendPaginatedResponse($users, 'pending users retrieved');
}

    
    
     
    public function approveUser(Request $request, User $user)
    {
        
       if ($user->status === 'active') {
            
            return $this->sendError('user already approved', []);
        }

        try {
           
            $user->update(['status' => 'active']);

            
            return $this->sendResponse(new UserResource($user), 'user approved');

        } catch (Exception $e) {
           
            return $this->sendError('user approval failed', ['error' => $e->getMessage()]);
        }
    }

    

    
      
     
    public function rejectUser(Request $request, User $user)
{
    
    if ($user->status === 'rejected' || $user->status === 'active') {
       
        return $this->sendError('invalid action', []);
    }

    try {
        
        if ($user->id_image) {
            Storage::disk('public')->delete($user->id_image);
        }
        
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        
        $user->delete();

        
        return $this->sendResponse([], 'user rejected');

    } catch (Exception $e) {
        
        return $this->sendError('user rejection failed', ['error' => $e->getMessage()]);
    }
}
    
    public function statistics(Request $request)
    {
        try {
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

           
            return $this->sendResponse($stats, 'statistics retrieved');

        } catch (Exception $e) {
           
            return $this->sendError('statistics retrieval failed', ['error' => $e->getMessage()]);
        }
    }
}