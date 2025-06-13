<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CancelRequest extends Model
{
    protected $fillable = [
        'service_booking_id',
        'reason',
        'status',
        'approved_by',
    ];

    /**
     * Get the service booking that owns the cancel request.
     */
    public function serviceBooking(): BelongsTo
    {
        return $this->belongsTo(ServiceBooking::class);
    }

    /**
     * Get the user who approved/rejected the cancel request.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
} 