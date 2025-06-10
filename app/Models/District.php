<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $primaryKey = 'district_id';
    public $timestamps = true;

    protected $fillable = [
        'district_name',
        'province_id'
    ];

    /**
     * Get the province that owns the district.
     */
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }

    /**
     * Get the services in this district.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the places in this district.
     */
    public function places()
    {
        return $this->hasMany(Place::class);
    }
} 