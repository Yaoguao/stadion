<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_item_id',
        'qr_code',
        'validated',
        'validated_at',
        'seat_label',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'validated' => 'boolean',
            'issued_at' => 'datetime',
            'validated_at' => 'datetime',
        ];
    }

    /**
     * Get the booking item that owns the ticket.
     */
    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(BookingItem::class);
    }

    /**
     * Check if the ticket is validated.
     */
    public function isValidated(): bool
    {
        return $this->validated;
    }

    /**
     * Validate the ticket.
     */
    public function validate(): bool
    {
        if ($this->validated) {
            return false;
        }

        $this->update([
            'validated' => true,
            'validated_at' => now(),
        ]);

        return true;
    }
}
