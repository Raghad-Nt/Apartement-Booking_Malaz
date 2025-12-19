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
    
       
     
    public function index(Request $request)
    {
        $query = Apartment::with(['owner', 'images']);

        
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

        
        if (!$request->has('status')) {
            $query->where('status', 'available');
        } elseif ($request->status != 'all') {
            $query->where('status', $request->status);
        }

        $apartments = $query->paginate(10);

        return $this->sendPaginatedResponse($apartments, 'apartments retrieved');
    }

    
     
     
    public function store(StoreApartmentRequest $request)
    {
        try {
            $user = $request->user();

            
            $apartment = $user->apartments()->create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'location' => $request->location,
                'province' => $request->province,
                'city' => $request->city,
                'features' => $request->features ?? [],
                'status' => 'available'
            ]);

            
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('apartment_images', 'public');
                    ApartmentImage::create([
                        'apartment_id' => $apartment->id,
                        'image_path' => $imagePath
                    ]);
                }
            }

            
            $apartment->load(['owner', 'images']);

            return $this->sendResponse(new ApartmentResource($apartment), 'apartment created');

} catch (Exception $e) {
    return $this->sendError('apartment creation failed', ['error' => $e->getMessage()]);
}
    }

    
     
     
    public function show(Apartment $apartment)
    {
        $apartment->load(['owner', 'images']);
        return $this->sendResponse(new ApartmentResource($apartment), 'apartment retrieved');
    }

    
       
     
    public function update(Request $request, Apartment $apartment)
    {
        
        if ($request->user()->id !== $apartment->owner_id) {
            return $this->sendError('unauthorized');
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'location' => 'sometimes|string|max:255',
            'province' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'features' => 'nullable|array',
            'status' => 'sometimes|in:available,booked,maintenance'
        ]);

        try {
            $apartment->update($request->only([
                'title', 'description', 'price', 'location', 'province', 'city', 'features', 'status'
            ]));

            
            $apartment->load(['owner', 'images']);

            return $this->sendResponse(new ApartmentResource($apartment), 'apartment updated');
        } catch (Exception $e) {
            return $this->sendError('apartment update failed', ['error' => $e->getMessage()]);
        }
    }

    
     
     
    public function destroy(Request $request, Apartment $apartment)
    {
        
        if ($request->user()->id !== $apartment->owner_id && !$request->user()->isAdmin()) {
            
            return $this->sendError('unauthorized');
        }

        try {
            
            foreach ($apartment->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            
            $apartment->delete();

            return $this->sendResponse([], 'apartment deleted');
                 } catch (Exception $e) {
                   return $this->sendError('apartment deletion failed', ['error' => $e->getMessage()]);
                 }
                }

    
      
     
    public function toggleFavorite(Request $request, Apartment $apartment)
    {
        $user = $request->user();
        
        
        $favorite = $user->favorites()->where('apartment_id', $apartment->id)->first();
        
        if ($favorite) {
            
            $favorite->delete();
            return $this->sendResponse([], 'favorite removed');
        } else {
           
            $user->favorites()->create(['apartment_id' => $apartment->id]);
            return $this->sendResponse([], 'favorite added');
        }
    }

    
      
     
    public function favorites(Request $request)
    {
        $favorites = $request->user()->favorites()->with('apartment.owner', 'apartment.images')->paginate(10);
        $apartments = $favorites->map(function ($favorite) {
            return $favorite->apartment;
        });

        return $this->sendPaginatedResponse($apartments, 'favorites retrieved');
    }
}