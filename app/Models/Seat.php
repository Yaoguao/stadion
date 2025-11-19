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
        'venue_id',
        'sector',
        'zone',
        'row_num',
        'seat_number',
        'base_price',
        'view_rating',
        'is_wheelchair',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'view_rating' => 'integer',
            'is_wheelchair' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the venue that owns the seat.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get the seat instances for the seat.
     */
    public function seatInstances(): HasMany
    {
        return $this->hasMany(SeatInstance::class);
    }

    /**
     * Get a formatted seat label.
     */
    public function getLabelAttribute(): string
    {
        $parts = array_filter([
            $this->sector,
            $this->row_num ? "Row {$this->row_num}" : null,
            $this->seat_number ? "Seat {$this->seat_number}" : null,
        ]);

        return implode(' ', $parts) ?: "Seat {$this->id}";
    }
}
