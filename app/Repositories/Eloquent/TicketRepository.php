<?php

namespace App\Repositories\Eloquent;

use App\Models\Ticket;
use App\Repositories\Interfaces\TicketRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TicketRepository extends BaseRepository implements TicketRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Ticket $model)
    {
        parent::__construct($model);
    }

    /**
     * Find ticket by QR code.
     */
    public function findByQrCode(string $qrCode): ?Ticket
    {
        return $this->model->where('qr_code', $qrCode)->first();
    }

    /**
     * Get tickets by booking item.
     */
    public function getByBookingItem(string $bookingItemId): Collection
    {
        return $this->model->where('booking_item_id', $bookingItemId)->get();
    }

    /**
     * Get validated tickets.
     */
    public function getValidated(): Collection
    {
        return $this->model->where('validated', true)->get();
    }

    /**
     * Get non-validated tickets.
     */
    public function getNonValidated(): Collection
    {
        return $this->model->where('validated', false)->get();
    }

    /**
     * Validate ticket.
     */
    public function validateTicket(string $ticketId): bool
    {
        $ticket = $this->findOrFail($ticketId);
        
        if ($ticket->validated) {
            return false;
        }

        return $this->update($ticketId, [
            'validated' => true,
            'validated_at' => now(),
        ]);
    }

    /**
     * Check if ticket is valid for validation.
     */
    public function canBeValidated(string $ticketId): bool
    {
        $ticket = $this->model->with('bookingItem.booking')->findOrFail($ticketId);
        
        // Ticket should not be already validated
        if ($ticket->validated) {
            return false;
        }

        // Check if booking is paid
        $booking = $ticket->bookingItem->booking;
        if (!$booking || $booking->status !== 'paid') {
            return false;
        }

        return true;
    }
}

