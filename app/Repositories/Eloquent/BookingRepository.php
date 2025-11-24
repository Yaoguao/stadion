<?php

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Repositories\Interfaces\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookingRepository extends BaseRepository implements BookingRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Booking $model)
    {
        parent::__construct($model);
    }

    /**
     * Get bookings by user.
     */
    public function getByUser(string $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get bookings by event.
     */
    public function getByEvent(string $eventId): Collection
    {
        return $this->model->where('event_id', $eventId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get bookings by status.
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get expired bookings.
     */
    public function getExpired(): Collection
    {
        return $this->model->where(function ($query) {
            $query->where('status', Booking::STATUS_EXPIRED)
                  ->orWhere(function ($q) {
                      $q->where('status', Booking::STATUS_PENDING)
                        ->where('expires_at', '<=', now());
                  });
        })->get();
    }

    /**
     * Get pending bookings.
     */
    public function getPending(): Collection
    {
        return $this->getByStatus(Booking::STATUS_PENDING);
    }

    /**
     * Get paid bookings.
     */
    public function getPaid(): Collection
    {
        return $this->getByStatus(Booking::STATUS_PAID);
    }

    /**
     * Create booking with items.
     */
    public function createWithItems(array $bookingData, array $itemsData): Booking
    {
        return DB::transaction(function () use ($bookingData, $itemsData) {
            $booking = $this->create($bookingData);

            foreach ($itemsData as $itemData) {
                $itemData['booking_id'] = $booking->id;
                $itemData['created_at'] = now();
                BookingItem::create($itemData);
            }

            return $booking->fresh(['bookingItems']);
        });
    }

    /**
     * Mark booking as paid.
     */
    public function markAsPaid(string $bookingId): bool
    {
        return $this->update($bookingId, [
            'status' => Booking::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Cancel booking.
     */
    public function cancelBooking(string $bookingId): bool
    {
        return DB::transaction(function () use ($bookingId) {
            $booking = $this->findOrFail($bookingId);
            
            // Release reserved seats
            $booking->reservedSeatInstances()->update([
                'status' => 'available',
                'reserved_by_booking_id' => null,
                'reserved_expires_at' => null,
            ]);

            return $this->update($bookingId, [
                'status' => Booking::STATUS_CANCELLED,
            ]);
        });
    }

    /**
     * Expire booking.
     */
    public function expireBooking(string $bookingId): bool
    {
        return DB::transaction(function () use ($bookingId) {
            $booking = $this->findOrFail($bookingId);
            
            // Release reserved seats
            $booking->reservedSeatInstances()->update([
                'status' => 'available',
                'reserved_by_booking_id' => null,
                'reserved_expires_at' => null,
            ]);

            return $this->update($bookingId, [
                'status' => Booking::STATUS_EXPIRED,
            ]);
        });
    }

    /**
     * Get bookings by idempotency key.
     */
    public function findByIdempotencyKey(string $key): ?Booking
    {
        return $this->model->where('idempotency_key', $key)->first();
    }

    /**
     * Get booking with relations.
     */
    public function findWithRelations(string $id, array $relations = ['user', 'event', 'bookingItems', 'payment']): ?Booking
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Paginate records with relations.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->with(['user', 'event', 'payment'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, $columns);
    }
}

