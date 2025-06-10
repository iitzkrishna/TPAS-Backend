<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $primaryKey = 'place_id';
    public $timestamps = true;

    protected $fillable = [
        'place_name',
        'district_id'
    ];

    /**
     * Get the district that owns the place.
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'district_id');
    }
} 