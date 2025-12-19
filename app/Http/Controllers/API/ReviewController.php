<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Apartment;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    
     
     
    public function index(Request $request)
    {
        $query = Review::with(['user', 'apartment']);

        
        if ($request->has('apartment_id')) {
            $query->where('apartment_id', $request->apartment_id);
        }

       
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $reviews = $query->paginate(10);

        return $this->sendPaginatedResponse($reviews, 'messages.reviews_retrieved');
    }

    
      
     
    public function store(StoreReviewRequest $request)
    {
        try {
            $user = $request->user();
            $apartment = Apartment::findOrFail($request->apartment_id);

            
            $review = $user->reviews()->updateOrCreate(
                ['apartment_id' => $request->apartment_id],
                [
                    'rating' => $request->rating,
                    'comment' => $request->comment
                ]
            );

            
            $review->load(['user', 'apartment']);

            return $this->sendResponse(new ReviewResource($review), 'messages.review_saved');
        } catch (\Exception $e) {
            return $this->sendError('messages.review_save_failed', ['error' => $e->getMessage()]);
        }
    }

    
      
     
    public function show(Review $review)
    {
        $review->load(['user', 'apartment']);
        return $this->sendResponse(new ReviewResource($review), 'messages.review_retrieved');
    }

    
     
     
    public function update(StoreReviewRequest $request, Review $review)
    {
        
        if ($request->user()->id !== $review->user_id) {
            return $this->sendError('messages.unauthorized', ['error' => 'messages.unauthorized']);
        }

        try {
            $review->update([
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

           
            $review->load(['user', 'apartment']);

            return $this->sendResponse(new ReviewResource($review), 'messages.review_updated');
        } catch (\Exception $e) {
            return $this->sendError('messages.review_update_failed', ['error' => $e->getMessage()]);
        }
    }

    
     
     
    public function destroy(Request $request, Review $review)
    {
        
        if ($request->user()->id !== $review->user_id && !$request->user()->isAdmin()) {
            return $this->sendError('messages.unauthorized', ['error' => 'messages.unauthorized']);
        }

        try {
            $review->delete();
            return $this->sendResponse([], 'messages.review_deleted');
        } catch (\Exception $e) {
            return $this->sendError('messages.review_deletion_failed', ['error' => $e->getMessage()]);
        }
    }


      
     
    public function apartmentReviews(Apartment $apartment)
    {
        $reviews = $apartment->reviews()->with('user')->paginate(10);
        return $this->sendPaginatedResponse($reviews, 'apartment reviews retrieved');
    }
}