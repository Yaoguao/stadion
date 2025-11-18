<?php

namespace App\Services;

use App\Models\Booking;
use App\Repositories\Interfaces\BookingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BookingService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository
    ) {}

    /**
     * Get all bookings with pagination.
     */
    public function getAllBookings(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->bookingRepository->paginate($perPage);
    }

    /**
     * Get booking by ID.
     */
    public function getBookingById(string $id, bool $withRelations = false): ?Booking
    {
        if ($withRelations) {
            return $this->bookingRepository->findWithRelations($id);
        }

        return $this->bookingRepository->find($id);
    }

    /**
     * Get bookings by user.
     */
    public function getBookingsByUser(string $userId): Collection
    {
        return $this->bookingRepository->getByUser($userId);
    }

    /**
     * Get bookings by event.
     */
    public function getBookingsByEvent(string $eventId): Collection
    {
        return $this->bookingRepository->getByEvent($eventId);
    }

    /**
     * Get bookings by status.
     */
    public function getBookingsByStatus(string $status): Collection
    {
        return $this->bookingRepository->getByStatus($status);
    }

    /**
     * Get pending bookings.
     */
    public function getPendingBookings(): Collection
    {
        return $this->bookingRepository->getPending();
    }

    /**
     * Get paid bookings.
     */
    public function getPaidBookings(): Collection
    {
        return $this->bookingRepository->getPaid();
    }

    /**
     * Get expired bookings.
     */
    public function getExpiredBookings(): Collection
    {
        return $this->bookingRepository->getExpired();
    }

    /**
     * Create booking with items.
     */
    public function createBooking(array $bookingData, array $itemsData): Booking
    {
        return $this->bookingRepository->createWithItems($bookingData, $itemsData);
    }

    /**
     * Mark booking as paid.
     */
    public function markBookingAsPaid(string $bookingId): bool
    {
        return $this->bookingRepository->markAsPaid($bookingId);
    }

    /**
     * Cancel booking.
     */
    public function cancelBooking(string $bookingId): bool
    {
        return $this->bookingRepository->cancelBooking($bookingId);
    }

    /**
     * Expire booking.
     */
    public function expireBooking(string $bookingId): bool
    {
        return $this->bookingRepository->expireBooking($bookingId);
    }

    /**
     * Find booking by idempotency key.
     */
    public function findBookingByIdempotencyKey(string $key): ?Booking
    {
        return $this->bookingRepository->findByIdempotencyKey($key);
    }
}

