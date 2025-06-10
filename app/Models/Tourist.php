<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tourist extends Model
{
    protected $fillable = [
        'user_id'
    ];

    /**
     * Get the user that owns the tourist.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the locations for the tourist.
     */
    public function locations()
    {
        return $this->hasMany(TouristLocation::class);
    }

    /**
     * Get the service bookings for the tourist.
     */
    public function serviceBookings()
    {
        return $this->hasMany(ServiceBooking::class);
    }

    /**
     * Get the wishlists for the tourist.
     */
    public function wishlists()
    {
        return $this->hasMany(ServiceWishlist::class);
    }
} 