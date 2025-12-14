<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'price',
        'location',
        'province',
        'city',
        'features',
        'owner_id',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2'
    ];

    
     // Get the owner of the apartment
     
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    
      //Get all images for the apartment
     
    public function images()
    {
        return $this->hasMany(ApartmentImage::class);
    }

    
      //Get all bookings for the apartment
     
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    
      //Get all reviews for the apartment
     
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    
      //Get all favorites for the apartment
     
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    
      //Scope a query to only include apartments from a specific province
     
    public function scopeInProvince($query, $province)
    {
        return $query->where('province', $province);
    }

    
      //Scope a query to only include apartments from a specific city
     
    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    
      //Scope a query to only include apartments within a price range
     
    public function scopePriceBetween($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    
      //Scope a query to only include apartments with specific features
     
    public function scopeHasFeatures($query, $features)
    {
        if (is_array($features)) {
            foreach ($features as $feature) {
                $query->whereJsonContains('features', $feature);
            }
        } else {
            $query->whereJsonContains('features', $features);
        }
        
        return $query;
    }
}