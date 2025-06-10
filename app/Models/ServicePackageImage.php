<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackageImage extends Model
{
    protected $table = 'service_packages_images';

    protected $fillable = [
        'service_id',
        'image_key',
        'order'
    ];

    /**
     * Get the service that owns the image.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
} 