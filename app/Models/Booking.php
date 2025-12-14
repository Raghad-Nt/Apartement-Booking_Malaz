<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
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
        'start_date',
        'end_date',
        'status',
        'total_price'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_price' => 'decimal:2'
    ];

    
     // Get the user that made the booking
     
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
      //Get the apartment that was booked
     
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}