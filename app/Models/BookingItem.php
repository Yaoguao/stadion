<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookingItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'booking_id',
        'seat_instance_id',
        'price',
        'fee',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'fee' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the booking that owns this item.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the seat instance for this booking item.
     */
    public function seatInstance(): BelongsTo
    {
        return $this->belongsTo(SeatInstance::class);
    }

    /**
     * Get the ticket for this booking item.
     */
    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }
}

