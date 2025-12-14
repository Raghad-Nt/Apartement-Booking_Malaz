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
    
       //Get pending users for admin approval
     
    public function pendingUsers(Request $request)
{
    $users = User::where('status', 'pending')->paginate(20);

    return $this->sendPaginatedResponse($users, 'pending users retrieved');
}

    
     // Approve a user
     
    public function approveUser(Request $request, User $user)
    {
        // Check if user is already approved
       if ($user->status === 'active') {
            // استجابة خطأ 400 (Bad Request) لأن العميل يحاول إجراء غير صالح
            return $this->sendError('user already approved', [], 400);
        }

        try {
            // تحديث حالة المستخدم إلى "نشط"
            $user->update(['status' => 'active']);

            // استجابة نجاح 200 (OK) مع بيانات المستخدم المحدثة ورسالة نجاح
            return $this->sendResponse(new UserResource($user), 'user approved');

        } catch (Exception $e) {
            // استجابة خطأ 500 (Internal Server Error) في حالة فشل عملية التحديث
            return $this->sendError('user approval failed', ['error' => $e->getMessage()]);
        }
    }

    

    
      //Reject a user
     
    public function rejectUser(Request $request, User $user)
{
    // التحقق إذا كان الإجراء غير صالح (إذا كان المستخدم مرفوضًا أو نشطًا بالفعل)
    if ($user->status === 'rejected' || $user->status === 'active') {
        // استجابة خطأ 400 (Bad Request) لأن العميل يحاول إجراء غير صالح
        return $this->sendError('invalid action', [], 400);
    }

    try {
        // حذف صور المستخدم من التخزين
        if ($user->id_image) {
            Storage::disk('public')->delete($user->id_image);
        }
        
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // حذف المستخدم
        $user->delete();

        // استجابة نجاح 200 (OK) مع رسالة تأكيد
        return $this->sendResponse([], 'user rejected');

    } catch (Exception $e) {
        // استجابة خطأ 500 (Internal Server Error) في حالة فشل عملية الحذف
        return $this->sendError('user rejection failed', ['error' => $e->getMessage()]);
    }
}
    
      // Get statistics for admin dashboard
     
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

            // استجابة نجاح 200 (OK) مع بيانات الإحصائيات ورسالة نجاح
            return $this->sendResponse($stats, 'statistics retrieved');

        } catch (Exception $e) {
            // استجابة خطأ 500 (Internal Server Error) في حالة فشل جلب الإحصائيات
            return $this->sendError('statistics retrieval failed', ['error' => $e->getMessage()]);
        }
    }
}