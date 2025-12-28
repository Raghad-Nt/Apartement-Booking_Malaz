<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
      
        'apartment_id',
        'booking_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
      
    ];

    
     // Get the sender of the message
     
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    
    
      //Get the receiver of the message
     
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    
      //Get the apartment associated with the message
     
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    
      //Get the booking associated with the message
     
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}