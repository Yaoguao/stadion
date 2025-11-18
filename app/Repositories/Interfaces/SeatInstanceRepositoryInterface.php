<?php

namespace App\Repositories\Interfaces;

use App\Models\SeatInstance;
use Illuminate\Database\Eloquent\Collection;

interface SeatInstanceRepositoryInterface extends RepositoryInterface
{
    /**
     * Get seat instances by event.
     */
    public function getByEvent(string $eventId): Collection;

    /**
     * Get available seat instances for event.
     */
    public function getAvailable(string $eventId): Collection;

    /**
     * Get reserved seat instances.
     */
    public function getReserved(string $eventId): Collection;

    /**
     * Get sold seat instances.
     */
    public function getSold(string $eventId): Collection;

    /**
     * Reserve seat instance.
     */
    public function reserve(string $seatInstanceId, string $bookingId, \DateTime $expiresAt): bool;

    /**
     * Release reservation.
     */
    public function releaseReservation(string $seatInstanceId): bool;

    /**
     * Mark as sold.
     */
    public function markAsSold(string $seatInstanceId): bool;

    /**
     * Get expired reservations.
     */
    public function getExpiredReservations(): Collection;
}

