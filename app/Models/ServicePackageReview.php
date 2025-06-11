<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePackageReview extends Model
{
    use HasFactory;

    protected $table = 'service_packages_reviews';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_id',
        'tourist_id',
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
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the tourist who wrote the review.
     */
    public function tourist(): BelongsTo
    {
        return $this->belongsTo(Tourist::class);
    }
} 