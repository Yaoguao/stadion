<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'address',
        'city',
        'timezone',
        'seat_map',
    ];

    protected $casts = [
        'seat_map' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the events for this venue.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the seats for this venue.
     */
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }
}

