<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'apartment_id',
        'rating',
        'comment'
    ];

    
     //Get the user that made the review
     
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
      //Get the apartment that was reviewed
     
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}