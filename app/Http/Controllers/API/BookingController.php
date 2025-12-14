<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

class BookingController extends BaseController
{
    
      //Display a listing of the resource.
     
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'apartment']);

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by apartment
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

            // Calculate total price
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

            return $this->sendResponse(new BookingResource($booking), 'booking created');
         }
             catch (Exception $e) {
          return $this->sendError('booking creation failed', ['error' => $e->getMessage()]);
         }
    }

    
     // Display the specified resource.
     
    public function show(Booking $booking)
    {
        $booking->load(['user', 'apartment']);
        return $this->sendResponse(new BookingResource($booking), 'booking retrieved');
          }

    
      //Update the specified resource in storage.
     
    public function update(Request $request, Booking $booking)
    {
        $user = $request->user();

        // Only admin or apartment owner can update booking status
        if (!$user->isAdmin() && $user->id !== $booking->apartment->owner_id) {
            return $this->sendError('unauthorized', [], 401);
                  }

        $request->validate([
            'status' => 'required|in:confirmed,rejected,cancelled'
        ]);

        try {
            $booking->update(['status' => $request->status]);

            // Load relationships
            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'booking status updated');
        } catch (Exception $e) {
            return $this->sendError('booking update failed', ['error' => $e->getMessage()]);
        }
    }

    
      //Cancel a booking (by the user who made it)
     
    public function cancel(Request $request, Booking $booking)
    {
        // Only the user who made the booking can cancel it
        if ($request->user()->id !== $booking->user_id) {
            return $this->sendError('unauthorized', ['error' => 'unauthorized']);
        }

        // Only pending bookings can be cancelled
        if ($booking->status !== 'pending') {
            return $this->sendError('invalid action', ['error' => 'invalid action']);
        }

        try {
            $booking->update(['status' => 'cancelled']);

            // Load relationships
            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'booking cancelled');
        } catch (Exception $e) {
            return $this->sendError('booking cancellation failed', ['error' => $e->getMessage()]);
        }
    }

    
      //Get user's bookings
     
    public function myBookings(Request $request)
    {
        $bookings = $request->user()->bookings()->with(['apartment.owner', 'apartment.images'])->paginate(10);
        return $this->sendPaginatedResponse($bookings, 'my bookings retrieved');
    }
}