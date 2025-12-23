<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use Exception;
use Illuminate\Http\Request;

class MessageController extends BaseController
{
    
      //Send a message
     
    public function send(SendMessageRequest $request)
    {
        try {
            $user = $request->user();
            $receiver = User::findOrFail($request->receiver_id);

            // Check if apartment_id is provided and user has access
            $apartment = null;
            if ($request->apartment_id) {
                $apartment = Apartment::findOrFail($request->apartment_id);
                
                // Verify that either sender or receiver is the apartment owner
                if ($apartment->owner_id !== $user->id && $apartment->owner_id !== $receiver->id) {
                    return $this->sendError('You are not authorized to message about this apartment');
                }
            }

            // Check if booking_id is provided and user has access
            $booking = null;
            if ($request->booking_id) {
                $booking = Booking::findOrFail($request->booking_id);
                
                // Verify that either sender or receiver is involved in the booking
                if ($booking->user_id !== $user->id && $booking->user_id !== $receiver->id && 
                    $booking->apartment->owner_id !== $user->id && $booking->apartment->owner_id !== $receiver->id) {
                    return $this->sendError('You are not authorized to message about this booking');
                }
            }

            // Create message
            $message = $user->sentMessages()->create([
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'is_read' => false,
                'apartment_id' => $request->apartment_id,
                'booking_id' => $request->booking_id
            ]);

            // Load relationships
            $message->load(['sender', 'receiver', 'apartment', 'booking']);

            return $this->sendResponse(new MessageResource($message), 'messages.message_sent');
        } catch (Exception $e) {
            return $this->sendError('messages.message_send_failed', ['error' => $e->getMessage()]);
        }
    }

    
    
     
    public function conversation(Request $request, User $user)
    {
        $currentUser = $request->user();

        // Get messages between the two users
        $messages = Message::where(function ($query) use ($currentUser, $user) {
            $query->where('sender_id', $currentUser->id)
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($currentUser, $user) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $currentUser->id);
        })->with(['sender', 'receiver', 'apartment', 'booking'])
          ->orderBy('created_at', 'asc')
          ->paginate(20);

        // Mark received messages as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->sendPaginatedResponse($messages, 'messages.conversation_retrieved');
    }

    
    
     
    public function inbox(Request $request)
    {
        $user = $request->user();

        // Get distinct conversations with latest message from each
        $conversations = Message::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id);
        })
        ->with(['sender', 'receiver', 'apartment', 'booking'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->unique(function ($item) use ($user) {
            // Group by the other participant in the conversation
            return $item->sender_id === $user->id ? $item->receiver_id : $item->sender_id;
        })
        ->take(20)
        ->values();

        return $this->sendResponse(MessageResource::collection($conversations), 'messages.inbox_retrieved');
    }

    
    
     
    public function apartmentMessages(Request $request, Apartment $apartment)
    {
        $user = $request->user();

        // Check if user is authorized to view messages about this apartment
        // User must be either the owner of the apartment or have booked it
        if ($apartment->owner_id !== $user->id && !$apartment->bookings()->where('user_id', $user->id)->exists()) {
            return $this->sendError('You are not authorized to view messages about this apartment');
        }

        // Get messages related to this apartment
        $messages = Message::where('apartment_id', $apartment->id)
            ->with(['sender', 'receiver', 'apartment', 'booking'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Mark received messages as read for this user
        Message::where('apartment_id', $apartment->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->sendPaginatedResponse($messages, 'messages.apartment_messages_retrieved');
    }
}