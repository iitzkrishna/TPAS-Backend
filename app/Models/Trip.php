<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'tourist_id',
        'start_date',
        'end_date',
        'trip_type',
        'destinations',
        'interests',
        'status',
        'is_completed'
    ];

    protected $casts = [
        'destinations' => 'array',
        'interests' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_completed' => 'boolean'
    ];

    protected $attributes = [
        'status' => 'pending',
        'is_completed' => false
    ];

    public function tourist(): BelongsTo
    {
        return $this->belongsTo(Tourist::class);
    }

    public function destinations()
    {
        return $this->belongsToMany(District::class, 'trip_destinations');
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'trip_interests');
    }

    public function prompts(): HasMany
    {
        return $this->hasMany(TripPrompt::class);
    }

    public function plan(): HasOne
    {
        return $this->hasOne(TripPlan::class);
    }
} 