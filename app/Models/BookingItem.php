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

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'seat_instance_id',
        'price',
        'fee',
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
            'fee' => 'decimal:2',
        ];
    }

    /**
     * Get the booking that owns the booking item.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the seat instance that owns the booking item.
     */
    public function seatInstance(): BelongsTo
    {
        return $this->belongsTo(SeatInstance::class);
    }

    /**
     * Get the ticket for the booking item.
     */
    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    /**
     * Get the total price (price + fee).
     */
    public function getTotalPriceAttribute(): float
    {
        return (float) $this->price + (float) $this->fee;
    }
}
