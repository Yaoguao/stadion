<?php

namespace App\Repositories\Interfaces;

use App\Models\Seat;
use Illuminate\Database\Eloquent\Collection;

interface SeatRepositoryInterface extends RepositoryInterface
{
    /**
     * Get seats by venue.
     */
    public function getByVenue(string $venueId): Collection;

    /**
     * Get seats by sector.
     */
    public function getBySector(string $venueId, string $sector): Collection;

    /**
     * Get seats by zone.
     */
    public function getByZone(string $venueId, string $zone): Collection;

    /**
     * Get wheelchair accessible seats.
     */
    public function getWheelchairAccessible(string $venueId): Collection;

    /**
     * Get seats in price range.
     */
    public function getInPriceRange(string $venueId, float $minPrice, float $maxPrice): Collection;
}

