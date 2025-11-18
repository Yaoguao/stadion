<?php

namespace App\Repositories\Eloquent;

use App\Models\SeatInstance;
use App\Repositories\Interfaces\SeatInstanceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SeatInstanceRepository extends BaseRepository implements SeatInstanceRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(SeatInstance $model)
    {
        parent::__construct($model);
    }

    /**
     * Get seat instances by event.
     */
    public function getByEvent(string $eventId): Collection
    {
        return $this->model->where('event_id', $eventId)->get();
    }

    /**
     * Get available seat instances for event.
     */
    public function getAvailable(string $eventId): Collection
    {
        return $this->model->where('event_id', $eventId)
            ->where('status', SeatInstance::STATUS_AVAILABLE)
            ->get();
    }

    /**
     * Get reserved seat instances.
     */
    public function getReserved(string $eventId): Collection
    {
        return $this->model->where('event_id', $eventId)
            ->where('status', SeatInstance::STATUS_RESERVED)
            ->get();
    }

    /**
     * Get sold seat instances.
     */
    public function getSold(string $eventId): Collection
    {
        return $this->model->where('event_id', $eventId)
            ->where('status', SeatInstance::STATUS_SOLD)
            ->get();
    }

    /**
     * Reserve seat instance atomically.
     */
    public function reserve(string $seatInstanceId, string $bookingId, \DateTime $expiresAt): bool
    {
        return DB::transaction(function () use ($seatInstanceId, $bookingId, $expiresAt) {
            $seatInstance = $this->model->where('id', $seatInstanceId)
                ->where('status', SeatInstance::STATUS_AVAILABLE)
                ->lockForUpdate()
                ->first();

            if (!$seatInstance) {
                return false;
            }

            return $seatInstance->update([
                'status' => SeatInstance::STATUS_RESERVED,
                'reserved_by_booking_id' => $bookingId,
                'reserved_expires_at' => $expiresAt,
            ]);
        });
    }

    /**
     * Release reservation.
     */
    public function releaseReservation(string $seatInstanceId): bool
    {
        return $this->update($seatInstanceId, [
            'status' => SeatInstance::STATUS_AVAILABLE,
            'reserved_by_booking_id' => null,
            'reserved_expires_at' => null,
        ]);
    }

    /**
     * Mark as sold.
     */
    public function markAsSold(string $seatInstanceId): bool
    {
        return $this->update($seatInstanceId, [
            'status' => SeatInstance::STATUS_SOLD,
            'reserved_by_booking_id' => null,
            'reserved_expires_at' => null,
        ]);
    }

    /**
     * Get expired reservations.
     */
    public function getExpiredReservations(): Collection
    {
        return $this->model->where('status', SeatInstance::STATUS_RESERVED)
            ->where('reserved_expires_at', '<=', now())
            ->get();
    }

    /**
     * Release expired reservations.
     */
    public function releaseExpiredReservations(): int
    {
        return $this->model->where('status', SeatInstance::STATUS_RESERVED)
            ->where('reserved_expires_at', '<=', now())
            ->update([
                'status' => SeatInstance::STATUS_AVAILABLE,
                'reserved_by_booking_id' => null,
                'reserved_expires_at' => null,
            ]);
    }
}

