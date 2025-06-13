<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceWishlist extends Model
{
    protected $fillable = [
        'service_id',
        'tourist_id',
        'rating',
        'review'
    ];

    protected $casts = [
        'rating' => 'double'
    ];

    /**
     * Get the service that owns the wishlist.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the tourist that owns the wishlist.
     */
    public function tourist(): BelongsTo
    {
        return $this->belongsTo(Tourist::class);
    }
} 