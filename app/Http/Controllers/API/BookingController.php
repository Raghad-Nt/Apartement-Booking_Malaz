<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Apartment;
use App\Models\Wallet;
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

        return $this->sendPaginatedResponse($bookings, 'bookings retrieved');
      }

    
      //Store a newly created resource in storage.
     
    public function store(StoreBookingRequest $request)
    {
        try {
            $user = $request->user();
            $apartment = Apartment::findOrFail($request->apartment_id);

            // Calculate total price based on days
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $days = $startDate->diffInDays($endDate);
            $totalPrice = $days * $apartment->price;

            // Create booking with pending status
            $booking = $user->bookings()->create([
                'apartment_id' => $request->apartment_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'pending',
                'total_price' => $totalPrice
            ]);

            // Load relationships
            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'booking created. Pending owner approval.');
        } catch (Exception $e) {
            // Handle creation errors
            return $this->sendError('booking creation failed', ['error' => $e->getMessage()]);
        }
    }

    
     
     
    public function show(Booking $booking)
    {
        // Load relationships
        $booking->load(['user', 'apartment']);
        return $this->sendResponse(new BookingResource($booking), 'booking retrieved');
          }

    
      //Update the specified resource in storage.
     
    public function update(Request $request, Booking $booking)
    {
        $user = $request->user();

        // Check admin or owner permissions
        if (!$user->isAdmin() && $user->id !== $booking->apartment->owner_id) {
            return $this->sendError('unauthorized');
        }

        $request->validate([
            'status' => 'required|in:confirmed,rejected,cancelled'
        ]);

        try {
            // If confirming the booking, process payment automatically
            if ($request->status === 'confirmed' && $booking->status === 'pending') {
                DB::beginTransaction();
                
                try {
                    // Get tenant (booking user) wallet
                    $tenant = $booking->user;
                    $tenantWallet = $tenant->wallet;
                    
                    if (!$tenantWallet) {
                        DB::rollBack();
                        return $this->sendError('Tenant does not have a wallet');
                    }

                    // Check if tenant has sufficient balance
                    if ($tenantWallet->balance < $booking->total_price) {
                        DB::rollBack();
                        return $this->sendError('Insufficient balance in tenant wallet. Balance: ' . $tenantWallet->balance . ', Required: ' . $booking->total_price);
                    }

                    // Get renter (apartment owner) wallet or create one
                    $renter = $booking->apartment->owner;
                    $renterWallet = $renter->wallet;
                    
                    if (!$renterWallet) {
                        $renterWallet = new Wallet(['user_id' => $renter->id, 'balance' => 0]);
                        $renter->wallet()->save($renterWallet);
                    }

                    // Deduct from tenant wallet
                    $tenantWallet->balance -= $booking->total_price;
                    $tenantWallet->save();

                    // Add to renter wallet
                    $renterWallet->balance += $booking->total_price;
                    $renterWallet->save();

                    // Update booking status
                    $booking->update(['status' => $request->status]);
                    
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    return $this->sendError('Payment processing failed', ['error' => $e->getMessage()]);
                }
            } else {
                // For other status updates (rejected, cancelled), just update status
                $booking->update(['status' => $request->status]);
            }

            // Load relationships
            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'booking status updated');
        } catch (Exception $e) {
            return $this->sendError('booking update failed', ['error' => $e->getMessage()]);
        }
    }

    
      //Cancel a booking by user
     
    public function cancel(Request $request, Booking $booking)
    {
        // Check if user owns the booking
        if ($request->user()->id !== $booking->user_id) {
            return $this->sendError('unauthorized', ['error' => 'unauthorized']);
        }

        // Check if booking is in pending status
        if ($booking->status !== 'pending') {
            return $this->sendError('invalid action', ['error' => 'invalid action']);
        }

        try {
            // Update status to cancelled
            $booking->update(['status' => 'cancelled']);

            // Load relationships
            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'booking cancelled');
        } catch (Exception $e) {
            return $this->sendError('booking cancellation failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified booking details in storage.
     *
     * @param  \App\Http\Requests\UpdateBookingRequest  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function updateDetails(UpdateBookingRequest $request, Booking $booking)
    {
        try {
            // Check if user owns the booking
            if ($request->user()->id !== $booking->user_id) {
                return $this->sendError('unauthorized', ['error' => 'unauthorized']);
            }

            // Check if booking is in pending status
            if ($booking->status !== 'pending') {
                return $this->sendError('invalid action', ['error' => 'only pending bookings can be modified']);
            }

            // Calculate total price based on days if dates are provided
            $totalPrice = $booking->total_price;
            if ($request->has('start_date') || $request->has('end_date')) {
                $startDate = $request->has('start_date') ? Carbon::parse($request->start_date) : $booking->start_date;
                $endDate = $request->has('end_date') ? Carbon::parse($request->end_date) : $booking->end_date;
                $days = $startDate->diffInDays($endDate);
                $totalPrice = $days * $booking->apartment->price;
            }

            // Update booking with provided fields
            $booking->update(array_merge(
                $request->only(['start_date', 'end_date']),
                ['total_price' => $totalPrice]
            ));

            // Load relationships
            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'booking updated');
        } catch (Exception $e) {
            return $this->sendError('booking update failed', ['error' => $e->getMessage()]);
        }
    }

    
      //Get current user's bookings
     
    public function myBookings(Request $request)
    {
        $bookings = $request->user()->bookings()->with(['apartment.owner', 'apartment.images'])->paginate(10);
        return $this->sendPaginatedResponse($bookings, 'my bookings retrieved');
    }
}