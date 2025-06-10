<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackageReview extends Model
{
    protected $table = 'service_packages_reviews';

    protected $fillable = [
        'service_id',
        'title',
        'rating',
        'review'
    ];

    protected $casts = [
        'rating' => 'double'
    ];

    /**
     * Get the service that owns the review.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
} 