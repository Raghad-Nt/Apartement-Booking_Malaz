<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Apartment;

class DashboardController extends Controller
{
    public function getOwnerStats(Request $request)
    {
        $user = $request->user();

        // 1. إجمالي الدخل من الحجوزات المكتملة
        $totalRevenue = Booking::whereHas('apartment', function($q) use ($user) {
            $q->where('owner_id', $user->id);
        })->where('status', 'completed')->sum('total_price');

        // 2. عدد الحجوزات الجديدة (بانتظار الموافقة)
        $newBookings = Booking::whereHas('apartment', function($q) use ($user) {
            $q->where('owner_id', $user->id);
        })->where('status', 'pending')->count();

        // 3. إجمالي عدد شقق المالك
        $apartmentsCount = $user->apartments()->count();

        // 4. متوسط التقييمات
        $averageRating = Review::whereHas('apartment', function($q) use ($user) {
            $q->where('owner_id', $user->id);
        })->avg('rating') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue' => (float)$totalRevenue,
                'new_bookings' => (int)$newBookings,
                'apartments_count' => (int)$apartmentsCount,
                'average_rating' => round((float)$averageRating, 1)
            ]
        ]);
    }
}
