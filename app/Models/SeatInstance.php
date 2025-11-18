<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SeatInstance extends Model
{
    use HasFactory, HasUuids;

    /**
     * Seat status constants.
     */
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_SOLD = 'sold';
    public const STATUS_BLOCKED = 'blocked';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'seat_id',
        'price',
        'status',
        'reserved_by_booking_id',
        'reserved_expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'reserved_expires_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the event that owns the seat instance.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the seat that owns the seat instance.
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
     * Get the booking items for the seat instance.
     */
    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    /**
     * Check if the seat instance is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    /**
     * Check if the reservation has expired.
     */
    public function isReservationExpired(): bool
    {
        if ($this->status !== self::STATUS_RESERVED) {
            return false;
        }

        return $this->reserved_expires_at && $this->reserved_expires_at->isPast();
    }
}
