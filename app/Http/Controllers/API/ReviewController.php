<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Apartment;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    
     // Display a listing of the resource.
     
    public function index(Request $request)
    {
        $query = Review::with(['user', 'apartment']);

        // Filter by apartment
        if ($request->has('apartment_id')) {
            $query->where('apartment_id', $request->apartment_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $reviews = $query->paginate(10);

        return $this->sendPaginatedResponse($reviews, 'messages.reviews_retrieved');
    }

    
      //Store a newly created resource in storage.
     
    public function store(StoreReviewRequest $request)
    {
        try {
            $user = $request->user();
            $apartment = Apartment::findOrFail($request->apartment_id);

            // Create or update review
            $review = $user->reviews()->updateOrCreate(
                ['apartment_id' => $request->apartment_id],
                [
                    'rating' => $request->rating,
                    'comment' => $request->comment
                ]
            );

            // Load relationships
            $review->load(['user', 'apartment']);

            return $this->sendResponse(new ReviewResource($review), 'messages.review_saved');
        } catch (\Exception $e) {
            return $this->sendError('messages.review_save_failed', ['error' => $e->getMessage()]);
        }
    }

    
      //Display the specified resource.
     
    public function show(Review $review)
    {
        $review->load(['user', 'apartment']);
        return $this->sendResponse(new ReviewResource($review), 'messages.review_retrieved');
    }

    
     // Update the specified resource in storage.
     
    public function update(StoreReviewRequest $request, Review $review)
    {
        // Check if user owns the review
        if ($request->user()->id !== $review->user_id) {
            return $this->sendError('messages.unauthorized', ['error' => 'messages.unauthorized']);
        }

        try {
            $review->update([
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

            // Load relationships
            $review->load(['user', 'apartment']);

            return $this->sendResponse(new ReviewResource($review), 'messages.review_updated');
        } catch (\Exception $e) {
            return $this->sendError('messages.review_update_failed', ['error' => $e->getMessage()]);
        }
    }

    
     // Remove the specified resource from storage.
     
    public function destroy(Request $request, Review $review)
    {
        // Check if user owns the review or is admin
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


      //Get reviews for a specific apartment
     
    public function apartmentReviews(Apartment $apartment)
    {
        $reviews = $apartment->reviews()->with('user')->paginate(10);
        return $this->sendPaginatedResponse($reviews, 'messages.apartment_reviews_retrieved');
    }
}