<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'tourist_id',
        'start_date',
        'end_date',
        'trip_type',
        'status',
        'is_completed'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_completed' => 'boolean'
    ];

    public function tourist()
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

    public function prompts()
    {
        return $this->hasMany(TripPrompt::class);
    }
} 