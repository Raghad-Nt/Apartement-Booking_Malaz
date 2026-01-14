<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Models\FcmToken;


class FcmTokenController extends BaseController
{
// Laravel: FcmTokenController.php

    public function store(Request $request)
    {
        // 1. الحصول على المستخدم المسجل حالياً من التوكن
        $user = $request->user();

        // 2. التحقق من البيانات
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        // 3. التحديث في جدول المستخدمين مباشرة (العمود الذي رأيتِه)
        $user->update([
            'fcm_token' => $request->fcm_token
        ]);

        return response()->json([
            'message' => 'FCM Token saved successfully in users table'
        ], 200);
    }
    public function getToken($userId)
    {
        // استرجاع التوكن من قاعدة البيانات
        $token = FcmToken::where('user_id', $userId)->latest()->first();

        if ($token) {
           return $this->sendResponse(['fcm_token' => $token->fcm_token], 'Token retrieved successfully',200);
        }

       return $this->sendError('Token not found', [],404);
    }
}
