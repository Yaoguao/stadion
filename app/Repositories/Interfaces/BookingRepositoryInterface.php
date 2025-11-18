<?php

namespace App\Repositories\Interfaces;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

interface BookingRepositoryInterface extends RepositoryInterface
{
    /**
     * Get bookings by user.
     */
    public function getByUser(string $userId): Collection;

    /**
     * Get bookings by event.
     */
    public function getByEvent(string $eventId): Collection;

    /**
     * Get bookings by status.
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get expired bookings.
     */
    public function getExpired(): Collection;

    /**
     * Get pending bookings.
     */
    public function getPending(): Collection;

    /**
     * Get paid bookings.
     */
    public function getPaid(): Collection;

    /**
     * Create booking with items.
     */
    public function createWithItems(array $bookingData, array $itemsData): Booking;

    /**
     * Mark booking as paid.
     */
    public function markAsPaid(string $bookingId): bool;

    /**
     * Cancel booking.
     */
    public function cancelBooking(string $bookingId): bool;

    /**
     * Expire booking.
     */
    public function expireBooking(string $bookingId): bool;

    /**
     * Get bookings by idempotency key.
     */
    public function findByIdempotencyKey(string $key): ?Booking;

    /**
     * Get booking with relations.
     */
    public function findWithRelations(string $id, array $relations = ['user', 'event', 'bookingItems', 'payment']): ?Booking;
}

