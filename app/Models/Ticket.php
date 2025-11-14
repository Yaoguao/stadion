<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'booking_item_id',
        'qr_code',
        'issued_at',
        'validated',
        'validated_at',
        'seat_label',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'validated' => 'boolean',
        'validated_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the booking item that owns this ticket.
     */
    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(BookingItem::class);
    }
}

