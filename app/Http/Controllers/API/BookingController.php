<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Apartment;
use App\Models\Wallet;
use App\Notifications\ApartmentActivity;
use App\Notifications\BookingStatusChanged;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingController extends BaseController
{

      //Display a listing of the resource.

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'apartment']);

        // Filter by user ID
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by apartment ID
        if ($request->has('apartment_id')) {
            $query->where('apartment_id', $request->apartment_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(10);

        return $this->sendPaginatedResponse($bookings, 'Bookings retrieved', 200);
      }


      //Store a newly created resource in storage.

    public function store(StoreBookingRequest $request)
    {
        // Only tenants can book apartments
        if (!$request->user()->isTenant()) {
            return $this->sendError('Only tenants can book apartments', [],403);
        }

        try {
            $user = $request->user();
            $apartment = Apartment::findOrFail($request->apartment_id);

            // Calculate total price based on days
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $days = $startDate->diffInDays($endDate);
            $totalPrice = (float)($days * $apartment->price);

            // Check if tenant has a wallet
            $tenantWallet = $user->wallet;
            if (!$tenantWallet) {
                return $this->sendError('You do not have money in your wallet. ', [],400);
            }

            // Check if tenant has sufficient balance
            $currentBalance = (float)($tenantWallet->balance ?? 0);
            if ($currentBalance < $totalPrice) {
                return $this->sendError("Insufficient balance in your wallet. Your balance: $" . number_format($currentBalance, 2) . ", Required: $" . number_format($totalPrice, 2), [],400);
            }

            // Create booking with pending status
            $booking = $user->bookings()->create([
                'apartment_id' => $request->apartment_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'pending',
                'total_price' => $totalPrice
            ]);

            // Send notification to tenant about the booking
            $user->notify(new ApartmentActivity('booking', $request->apartment_id, $user->id));

            // Load relationships
            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'Booking created. Pending owner approval.',200);
        } catch (Exception $e) {
            // Handle creation errors
            return $this->sendError('Booking creation failed', ['error' => $e->getMessage()],500);
        }
    }




    public function show(Booking $booking)
    {
        // Load relationships
        $booking->load(['user', 'apartment']);
        return $this->sendResponse(new BookingResource($booking), 'Booking retrieved',200);
          }


      //Update the specified resource in storage.

    public function update(Request $request, Booking $booking)
    {
        $user = $request->user();

        // Check admin or owner permissions
        if (!$user->isAdmin() && $user->id !== $booking->apartment->owner_id) {
            return $this->sendError('Unauthorized',[], 403);
        }

        // Check if booking has already been cancelled by tenant - if so, owner cannot change status
        if ($booking->status === 'cancelled') {
            return $this->sendError('Cannot update a booking that has been cancelled by the tenant', [],400);
        }

        $request->validate([
            'status' => 'required|in:confirmed,rejected,modification_approved,modification_rejected'
        ]);

        try {
            // Handle booking confirmation for new bookings
            if ($request->status === 'confirmed' && $booking->status === 'pending') {
                DB::beginTransaction();

                try {
                    // Get tenant (booking user) wallet
                    $tenant = $booking->user;
                    $tenantWallet = $tenant->wallet;

                    if (!$tenantWallet) {
                        DB::rollBack();
                        return $this->sendError('You do not have money in your wallet', [], 400);
                    }

                    // Check if tenant has sufficient balance
                    if ((float)($tenantWallet->balance ?? 0) < (float)($booking->total_price ?? 0)) {
                        DB::rollBack();
                        return $this->sendError('Insufficient balance in tenant wallet. Balance: $' . number_format($tenantWallet->balance ?? 0, 2) . ', Required: $' . number_format($booking->total_price ?? 0, 2),[], 400);
                    }

                    // Get owner (apartment owner) wallet or create one
                    $renter = $booking->apartment->owner;
                    $renterWallet = $renter->wallet;

                    if (!$renterWallet) {
                        $renterWallet = new Wallet(['user_id' => $renter->id, 'balance' => 0]);
                        $renter->wallet()->save($renterWallet);
                    }

                    // Deduct from tenant wallet
                    $tenantWallet->balance = (float)(($tenantWallet->balance ?? 0) - $booking->total_price);
                    $tenantWallet->save();

                    // Add to renter wallet
                    $renterWallet->balance = (float)(($renterWallet->balance ?? 0) + $booking->total_price);
                    $renterWallet->save();

                    // Update booking status
                    $booking->update(['status' => $request->status]);

                    // Send notification to the booking user
                    $booking->user->notify(new BookingStatusChanged($booking, $request->status));

                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    return $this->sendError('Payment processing failed', ['error' => $e->getMessage()],500);
                }
            }
            // Handle modification approval
            elseif ($request->status === 'modification_approved' && $booking->status === 'pending_modification') {
                // Update status to confirmed since modification is approved
                $booking->update(['status' => 'confirmed']);

                // Send notification to the booking user
                $booking->user->notify(new BookingStatusChanged($booking, 'modification_approved'));
            }
            // Handle modification rejection
            elseif ($request->status === 'modification_rejected' && $booking->status === 'pending_modification') {
                // Revert to original booking details, keep status as confirmed
                $booking->update(['status' => 'confirmed']);

                // Send notification to the booking user
                $booking->user->notify(new BookingStatusChanged($booking, 'modification_rejected'));
            }
            else {
                // For other status updates (rejected), just update status
                $booking->update(['status' => $request->status]);

                // Send notification to the booking user
                $booking->user->notify(new BookingStatusChanged($booking, $request->status));
            }

            // Load relationships
            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'Booking status updated',200);
        } catch (Exception $e) {
            return $this->sendError('Booking update failed', ['error' => $e->getMessage()],500);
        }
    }


      //Cancel a booking by user

    public function cancel(Request $request, Booking $booking)
    {
        // Check if user owns the booking
        if ($request->user()->id !== $booking->user_id) {
            return $this->sendError('Unauthorized', [],401);
        }

        // Check if booking is in pending or confirmed status (allow cancellation of both)
        if ($booking->status !== 'pending' && $booking->status !== 'confirmed') {
            return $this->sendError('Invalid action', [],400);
        }

        try {
            // Get booking info before update for notification and potential refund
            $apartment = $booking->apartment;
            $tenant = $booking->user;
            $bookingStatus = $booking->status;
            $bookingTotalPrice = $booking->total_price;

            // Update the booking status to cancelled
            $booking->update(['status' => 'cancelled']);

            // If the booking was already confirmed, refund the money to tenant's wallet
            if ($bookingStatus === 'confirmed') {
                // Get tenant wallet
                $tenantWallet = $tenant->wallet;
                if ($tenantWallet) {
                    // Add the booking amount back to tenant's wallet
                    $tenantWallet->balance = (float)($tenantWallet->balance + $bookingTotalPrice);
                    $tenantWallet->save();
                }

                // Get renter wallet and deduct the amount
                $renterWallet = $apartment->owner->wallet;
                if ($renterWallet) {
                    // Deduct the booking amount from renter's wallet
                    $renterWallet->balance = (float)($renterWallet->balance - $bookingTotalPrice);
                    $renterWallet->save();
                }
            }

            // Send notification to tenant about the cancellation
            $tenant->notify(new ApartmentActivity('cancellation', $apartment->id, $tenant->id));

            // Load relationships
            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'Booking cancelled',200);
        } catch (Exception $e) {
            return $this->sendError('Booking cancellation failed', ['error' => $e->getMessage()],500);
        }
    }

    /**
     * Update the specified booking details in storage.
     *
     * @param  \App\Http\Requests\UpdateBookingRequest  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\JsonResponse
     */
    // في BookingController.php

    public function updateDetails(UpdateBookingRequest $request, Booking $booking)
    {
        try {
            // 1. حساب السعر الجديد
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $days = $startDate->diffInDays($endDate);
            if ($days <= 0) return $this->sendError('تاريخ النهاية يجب أن يكون بعد تاريخ البداية', [], 400);

            $newTotalPrice = (float)($days * $booking->apartment->price);

            // 2. التحقق من المحفظة (Wallet Check)
            $tenantWallet = $booking->user->wallet;
            if (!$tenantWallet || $tenantWallet->balance < $newTotalPrice) {
                return $this->sendError("رصيدك غير كافٍ. السعر الجديد: $newTotalPrice", [], 400);
            }

            // 3. منطق تحديث الحالة
            if ($booking->status === 'confirmed') {
                // لا نحدث التواريخ فوراً، بل ننتظر موافقة المالك
                $booking->update([
                    'status' => 'pending_modification',
                    // ملاحظة: يمكنك إضافة حقول temp_start_date في قاعدة البيانات إذا أردت حفظها قبل الموافقة
                ]);
                $message = 'تم إرسال طلب التعديل للمالك للموافقة.';
            } else {
                // إذا كان pending نحدث البيانات مباشرة
                $booking->update([
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'total_price' => $newTotalPrice
                ]);
                $message = 'تم تحديث الحجز بنجاح.';
            }

            return $this->sendResponse(new BookingResource($booking), 'Booking updated', 200);

        } catch (Exception $e) {
            return $this->sendError('فشل التحديث', ['error' => $e->getMessage()], 500);
        }
    }
      //Get current user's bookings

    public function myBookings(Request $request)
    {
        $bookings = $request->user()->bookings()->with(['apartment.owner', 'apartment.images'])->paginate(10);
        return $this->sendPaginatedResponse($bookings, 'My bookings:',200);
    }
}
