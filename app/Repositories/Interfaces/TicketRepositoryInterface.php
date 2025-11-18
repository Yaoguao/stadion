<?php

namespace App\Repositories\Interfaces;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

interface TicketRepositoryInterface extends RepositoryInterface
{
    /**
     * Find ticket by QR code.
     */
    public function findByQrCode(string $qrCode): ?Ticket;

    /**
     * Get tickets by booking item.
     */
    public function getByBookingItem(string $bookingItemId): Collection;

    /**
     * Get validated tickets.
     */
    public function getValidated(): Collection;

    /**
     * Get non-validated tickets.
     */
    public function getNonValidated(): Collection;

    /**
     * Validate ticket.
     */
    public function validateTicket(string $ticketId): bool;

    /**
     * Check if ticket is valid for validation.
     */
    public function canBeValidated(string $ticketId): bool;
}

