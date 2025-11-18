<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\SeatInstance;
use App\Repositories\Interfaces\SeatInstanceRepositoryInterface;
use App\Repositories\Interfaces\SeatRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SeatService
{
    public function __construct(
        private SeatRepositoryInterface $seatRepository,
        private SeatInstanceRepositoryInterface $seatInstanceRepository
    ) {}

    /**
     * Get seats by venue.
     */
    public function getSeatsByVenue(string $venueId): Collection
    {
        return $this->seatRepository->getByVenue($venueId);
    }

    /**
     * Get seats by sector.
     */
    public function getSeatsBySector(string $venueId, string $sector): Collection
    {
        return $this->seatRepository->getBySector($venueId, $sector);
    }

    /**
     * Get seats by zone.
     */
    public function getSeatsByZone(string $venueId, string $zone): Collection
    {
        return $this->seatRepository->getByZone($venueId, $zone);
    }

    /**
     * Get wheelchair accessible seats.
     */
    public function getWheelchairAccessibleSeats(string $venueId): Collection
    {
        return $this->seatRepository->getWheelchairAccessible($venueId);
    }

    /**
     * Get available seat instances for event.
     */
    public function getAvailableSeatInstances(string $eventId): Collection
    {
        return $this->seatInstanceRepository->getAvailable($eventId);
    }

    /**
     * Get reserved seat instances for event.
     */
    public function getReservedSeatInstances(string $eventId): Collection
    {
        return $this->seatInstanceRepository->getReserved($eventId);
    }

    /**
     * Get sold seat instances for event.
     */
    public function getSoldSeatInstances(string $eventId): Collection
    {
        return $this->seatInstanceRepository->getSold($eventId);
    }

    /**
     * Reserve seat instance.
     */
    public function reserveSeat(string $seatInstanceId, string $bookingId, \DateTime $expiresAt): bool
    {
        return $this->seatInstanceRepository->reserve($seatInstanceId, $bookingId, $expiresAt);
    }

    /**
     * Release seat reservation.
     */
    public function releaseSeatReservation(string $seatInstanceId): bool
    {
        return $this->seatInstanceRepository->releaseReservation($seatInstanceId);
    }

    /**
     * Mark seat as sold.
     */
    public function markSeatAsSold(string $seatInstanceId): bool
    {
        return $this->seatInstanceRepository->markAsSold($seatInstanceId);
    }

    /**
     * Get expired reservations.
     */
    public function getExpiredReservations(): Collection
    {
        return $this->seatInstanceRepository->getExpiredReservations();
    }

    /**
     * Release expired reservations.
     */
    public function releaseExpiredReservations(): int
    {
        return $this->seatInstanceRepository->releaseExpiredReservations();
    }
}

