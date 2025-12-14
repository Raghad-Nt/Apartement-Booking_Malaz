<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApartmentImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'apartment_id',
        'image_path'
    ];

    
      //Get the apartment that owns the image
     
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}