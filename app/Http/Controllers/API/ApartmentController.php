<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\StoreApartmentRequest;
use App\Http\Resources\ApartmentResource;
use App\Models\Apartment;
use App\Models\ApartmentImage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApartmentController extends BaseController
{
    
      //Display a listing of the resource.
     
    public function index(Request $request)
    {
        $query = Apartment::with(['owner', 'images']);

        // Apply filters
        if ($request->has('province')) {
            $query->inProvince($request->province);
        }

        if ($request->has('city')) {
            $query->inCity($request->city);
        }

        if ($request->has('min_price') || $request->has('max_price')) {
            $min = $request->min_price ?? 0;
            $max = $request->max_price ?? 999999999;
            $query->priceBetween($min, $max);
        }

        if ($request->has('features')) {
            $features = explode(',', $request->features);
            $query->hasFeatures($features);
        }

        // Apply status filter (only show available apartments by default)
        if (!$request->has('status')) {
            $query->where('status', 'available');
        } elseif ($request->status != 'all') {
            $query->where('status', $request->status);
        }

        $apartments = $query->paginate(10);

        // Return paginated response
        return $this->sendPaginatedResponse($apartments, 'apartments retrieved');
    }

    
     //Store a newly created resource in storage.
     
    public function store(StoreApartmentRequest $request)
    {
        try {
            $user = $request->user();

            // Create apartment with owner relationship
            $apartment = $user->apartments()->create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'province' => $request->province,
                'city' => $request->city,
                'features' => $request->features ?? [],
                'status' => 'available'
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('apartment_images', 'public');
                    ApartmentImage::create([
                        'apartment_id' => $apartment->id,
                        'image_path' => $imagePath
                    ]);
                }
            }

            // Load relationships
            $apartment->load(['owner', 'images']);

            return $this->sendResponse(new ApartmentResource($apartment), 'apartment created');

} catch (Exception $e) {
    // Handle creation errors
    return $this->sendError('apartment creation failed', ['error' => $e->getMessage()]);
}
    }

    
     
     
    public function show(Apartment $apartment)
    {
        // Load relationships
        $apartment->load(['owner', 'images']);
        return $this->sendResponse(new ApartmentResource($apartment), 'apartment retrieved');
    }

    
       //Update the specified resource in storage.
     
    public function update(Request $request, Apartment $apartment)
    {
        // Check ownership
        if ($request->user()->id !== $apartment->owner_id) {
            return $this->sendError('unauthorized');
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'province' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'features' => 'nullable|array',
            'status' => 'sometimes|in:available,booked,maintenance'
        ]);

        try {
            // Update apartment with provided fields
            $apartment->update($request->only([
                'title', 'description', 'price', 'province', 'city', 'features', 'status'
            ]));

            // Load relationships
            $apartment->load(['owner', 'images']);

            return $this->sendResponse(new ApartmentResource($apartment), 'apartment updated');
        } catch (Exception $e) {
            // Handle update errors
            return $this->sendError('apartment update failed', ['error' => $e->getMessage()]);
        }
    }

    
     //Remove the specified resource from storage.
     
    public function destroy(Request $request, Apartment $apartment)
    {
        // Check ownership or admin privileges
        if ($request->user()->id !== $apartment->owner_id && !$request->user()->isAdmin()) {
            // Return unauthorized error
            return $this->sendError('unauthorized');
        }

        try {
            // Delete associated images
            foreach ($apartment->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            // Delete apartment
            $apartment->delete();

            return $this->sendResponse([], 'apartment deleted');
                 } catch (Exception $e) {
                   // Handle deletion errors
                   return $this->sendError('apartment deletion failed', ['error' => $e->getMessage()]);
                 }
                }

    
      //Add apartment to favorites
     
    public function addToFavorites(Request $request, Apartment $apartment)
    {
        $user = $request->user();
        
        // Check if user is tenant
        if (!$user->isTenant()) {
            return $this->sendError('Only tenants can add apartments to favorites');
        }
        
        // Check if already favorited
        $favorite = $user->favorites()->where('apartment_id', $apartment->id)->first();
        
        if ($favorite) {
            return $this->sendError('Apartment already added to favorites');
        } else {
            // Add to favorites
            $user->favorites()->create(['apartment_id' => $apartment->id]);
            return $this->sendResponse([], 'Apartment added to favorites');
        }
    }
    
      //Remove apartment from favorites
     
    public function removeFromFavorites(Request $request, Apartment $apartment)
    {
        $user = $request->user();
        
        // Check if user is tenant
        if (!$user->isTenant()) {
            return $this->sendError('Only tenants can remove apartments from favorites');
        }
        
        // Check if already favorited
        $favorite = $user->favorites()->where('apartment_id', $apartment->id)->first();
        
        if (!$favorite) {
            return $this->sendError('Apartment already removed from favorites');
        } else {
            // Remove from favorites
            $favorite->delete();
            return $this->sendResponse([], 'Apartment removed from favorites');
        }
    }

    
      //Get user's favorite apartments
     
    public function favorites(Request $request)
    {
        $favorites = $request->user()->favorites()->with('apartment.owner', 'apartment.images')->paginate(10);
        $apartments = $favorites->map(function ($favorite) {
            return $favorite->apartment;
        });

        return $this->sendPaginatedResponse($apartments, 'favorites retrieved');
    }
}