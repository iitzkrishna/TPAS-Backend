<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'partner_id',
        'title',
        'type',
        'subtype',
        'amount',
        'thumbnail',
        'description',
        'discount_percentage',
        'discount_expires_on',
        'status_visibility',
        'location',
        'district_id',
        'availability'
    ];

    protected $casts = [
        'availability' => 'array',
        'discount_expires_on' => 'datetime',
        'amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2'
    ];

    /**
     * Get the partner that owns the service.
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the district where the service is located.
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'district_id');
    }

    /**
     * Get the images for the service.
     */
    public function images()
    {
        return $this->hasMany(ServicePackageImage::class);
    }

    /**
     * Get the reviews for the service.
     */
    public function reviews()
    {
        return $this->hasMany(ServicePackageReview::class);
    }
} 