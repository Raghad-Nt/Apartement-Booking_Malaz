<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class MessageController extends BaseController
{
    
      
     
    public function send(SendMessageRequest $request)
    {
        try {
            $user = $request->user();
            $receiver = User::findOrFail($request->receiver_id);

            
            $message = $user->sentMessages()->create([
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'is_read' => false
            ]);

            
            $message->load(['sender', 'receiver']);

            return $this->sendResponse(new MessageResource($message), 'messages.message_sent');
        } catch (Exception $e) {
            return $this->sendError('messages.message_send_failed', ['error' => $e->getMessage()]);
        }
    }

    
      
     
    public function conversation(Request $request, User $user)
    {
        $currentUser = $request->user();

        
        $messages = Message::where(function ($query) use ($currentUser, $user) {
            $query->where('sender_id', $currentUser->id)
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($currentUser, $user) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $currentUser->id);
        })->with(['sender', 'receiver'])
          ->orderBy('created_at', 'asc')
          ->paginate(20);

        
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->sendPaginatedResponse($messages, 'messages.conversation_retrieved');
    }

    
      
     
    public function inbox(Request $request)
    {
        $user = $request->user();

        
        $conversations = Message::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id);
        })->orderBy('created_at', 'desc')
          ->get()
          ->unique(function ($item) use ($user) {
              return $item->sender_id === $user->id ? $item->receiver_id : $item->sender_id;
          })
          ->take(20)
          ->values();

        
        $conversations->load(['sender', 'receiver']);

        return $this->sendResponse(MessageResource::collection($conversations), 'messages.inbox_retrieved');
    }
}