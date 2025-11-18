<?php

namespace App\Repositories\Interfaces;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface extends RepositoryInterface
{
    /**
     * Get payments by booking.
     */
    public function getByBooking(string $bookingId): Collection;

    /**
     * Get payments by status.
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get payments by provider.
     */
    public function getByProvider(string $provider): Collection;

    /**
     * Find payment by transaction ID.
     */
    public function findByTransactionId(string $transactionId): ?Payment;

    /**
     * Find payment by idempotency key.
     */
    public function findByIdempotencyKey(string $key): ?Payment;

    /**
     * Mark payment as successful.
     */
    public function markAsSuccess(string $paymentId, string $transactionId = null): bool;

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(string $paymentId): bool;

    /**
     * Mark payment as refunded.
     */
    public function markAsRefunded(string $paymentId): bool;
}

