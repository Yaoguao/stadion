<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeatInstance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'event_id',
        'seat_id',
        'price',
        'status',
        'reserved_by_booking_id',
        'reserved_expires_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'reserved_expires_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const CREATED_AT = null;
    const UPDATED_AT = 'updated_at';

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_SOLD = 'sold';
    public const STATUS_BLOCKED = 'blocked';

    /**
     * Get the event that owns this seat instance.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the seat that this instance is based on.
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    /**
     * Get the booking that reserved this seat instance.
     */
    public function reservedByBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'reserved_by_booking_id');
    }

    /**
     * Get the booking items for this seat instance.
     */
    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }
}

