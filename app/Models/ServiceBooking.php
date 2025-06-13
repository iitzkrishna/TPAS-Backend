<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceBooking extends Model
{
    protected $table = 'service_booking';

    protected $fillable = [
        'service_id',
        'tourist_id',
        'request',
        'pref_start_date',
        'pref_end_date',
        'adults',
        'childrens',
        'total_charge',
    ];

    protected $casts = [
        'pref_start_date' => 'datetime',
        'pref_end_date' => 'datetime',
        'total_charge' => 'decimal:2',
    ];

    /**
     * Get the service that owns the booking.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the tourist that owns the booking.
     */
    public function tourist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tourist_id');
    }

    /**
     * Get the cancel request associated with the booking.
     */
    public function cancelRequest(): HasOne
    {
        return $this->hasOne(CancelRequest::class);
    }
} 