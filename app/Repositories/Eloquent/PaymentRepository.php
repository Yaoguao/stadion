<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get payments by booking.
     */
    public function getByBooking(string $bookingId): Collection
    {
        return $this->model->where('booking_id', $bookingId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get payments by status.
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get payments by provider.
     */
    public function getByProvider(string $provider): Collection
    {
        return $this->model->where('provider', $provider)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find payment by transaction ID.
     */
    public function findByTransactionId(string $transactionId): ?Payment
    {
        return $this->model->where('transaction_id', $transactionId)->first();
    }

    /**
     * Find payment by idempotency key.
     */
    public function findByIdempotencyKey(string $key): ?Payment
    {
        return $this->model->where('idempotency_key', $key)->first();
    }

    /**
     * Mark payment as successful.
     */
    public function markAsSuccess(string $paymentId, string $transactionId = null): bool
    {
        $data = ['status' => Payment::STATUS_SUCCESS];
        
        if ($transactionId) {
            $data['transaction_id'] = $transactionId;
        }

        return $this->update($paymentId, $data);
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(string $paymentId): bool
    {
        return $this->update($paymentId, ['status' => Payment::STATUS_FAILED]);
    }

    /**
     * Mark payment as refunded.
     */
    public function markAsRefunded(string $paymentId): bool
    {
        return $this->update($paymentId, ['status' => Payment::STATUS_REFUNDED]);
    }

    /**
     * Paginate records with relations.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->with(['booking.user', 'booking.event'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, $columns);
    }
}

