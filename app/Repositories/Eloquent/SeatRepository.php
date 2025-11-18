<?php

namespace App\Repositories\Eloquent;

use App\Models\Seat;
use App\Repositories\Interfaces\SeatRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SeatRepository extends BaseRepository implements SeatRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Seat $model)
    {
        parent::__construct($model);
    }

    /**
     * Get seats by venue.
     */
    public function getByVenue(string $venueId): Collection
    {
        return $this->model->where('venue_id', $venueId)
            ->orderBy('sector')
            ->orderBy('row_num')
            ->orderBy('seat_number')
            ->get();
    }

    /**
     * Get seats by sector.
     */
    public function getBySector(string $venueId, string $sector): Collection
    {
        return $this->model->where('venue_id', $venueId)
            ->where('sector', $sector)
            ->orderBy('row_num')
            ->orderBy('seat_number')
            ->get();
    }

    /**
     * Get seats by zone.
     */
    public function getByZone(string $venueId, string $zone): Collection
    {
        return $this->model->where('venue_id', $venueId)
            ->where('zone', $zone)
            ->orderBy('sector')
            ->orderBy('row_num')
            ->orderBy('seat_number')
            ->get();
    }

    /**
     * Get wheelchair accessible seats.
     */
    public function getWheelchairAccessible(string $venueId): Collection
    {
        return $this->model->where('venue_id', $venueId)
            ->where('is_wheelchair', true)
            ->get();
    }

    /**
     * Get seats in price range.
     */
    public function getInPriceRange(string $venueId, float $minPrice, float $maxPrice): Collection
    {
        return $this->model->where('venue_id', $venueId)
            ->whereBetween('base_price', [$minPrice, $maxPrice])
            ->orderBy('base_price')
            ->get();
    }
}

