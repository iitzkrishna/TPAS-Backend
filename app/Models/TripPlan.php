<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripPlan extends Model
{
    protected $fillable = [
        'trip_id',
        'total_days',
        'stay_points',
        'itinerary'
    ];

    protected $casts = [
        'stay_points' => 'array',
        'itinerary' => 'array'
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
} 