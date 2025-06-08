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

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }
} 