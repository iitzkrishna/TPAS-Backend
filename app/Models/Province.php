<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $primaryKey = 'province_id';
    public $timestamps = true;

    protected $fillable = [
        'province_name'
    ];

    public function districts()
    {
        return $this->hasMany(District::class, 'province_id', 'province_id');
    }
} 