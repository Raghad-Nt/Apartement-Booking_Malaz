<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'mobile',
        'profile_image',
        'id_image'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    
      //Check if user is admin
     
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    
      //Check if user is tenant (renter)
     
    public function isTenant()
    {
        return $this->role === 'tenant';
    }

    
      //Check if user is renter (owner)
     
    public function isRenter()
    {
        return $this->role === 'renter';
    }

    
     // Check if user account is active
     
    public function isActive()
    {
        return $this->status === 'active';
    }

    
      //User apartments (if user is a renter/owner)
     
    public function apartments()
    {
        return $this->hasMany(Apartment::class, 'owner_id');
    }

    
      //User bookings
     
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    
      //User reviews
     
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    
      //User favorites
     
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    
      //Sent messages
     
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    
      //Received messages
     
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}