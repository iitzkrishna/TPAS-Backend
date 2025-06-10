<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'business_registration_number',
        'business_address',
        'business_phone',
        'business_email',
        'business_website',
        'business_description',
        'business_logo',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Get the user that owns the partner.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the services for the partner.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }
} 