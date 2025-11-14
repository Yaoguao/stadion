<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'venue_id',
        'sector',
        'zone',
        'row_num',
        'seat_number',
        'base_price',
        'view_rating',
        'is_wheelchair',
    ];

    protected $casts = [
        'row_num' => 'integer',
        'seat_number' => 'integer',
        'base_price' => 'decimal:2',
        'view_rating' => 'integer',
        'is_wheelchair' => 'boolean',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the venue that owns this seat.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get the seat instances for this seat.
     */
    public function seatInstances(): HasMany
    {
        return $this->hasMany(SeatInstance::class);
    }
}

