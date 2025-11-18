<?php

namespace App\Repositories\Interfaces;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

interface EventRepositoryInterface extends RepositoryInterface
{
    /**
     * Get published events.
     */
    public function getPublished(): Collection;

    /**
     * Get upcoming events.
     */
    public function getUpcoming(?Carbon $from = null): Collection;

    /**
     * Get events by venue.
     */
    public function getByVenue(string $venueId): Collection;

    /**
     * Get events with available seats.
     */
    public function getWithAvailableSeats(): Collection;

    /**
     * Search events by title or description.
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get events in date range.
     */
    public function getInDateRange(Carbon $start, Carbon $end): Collection;

    /**
     * Publish event.
     */
    public function publish(string $eventId): bool;

    /**
     * Unpublish event.
     */
    public function unpublish(string $eventId): bool;

    /**
     * Get event with relations.
     */
    public function findWithRelations(string $id, array $relations = ['venue', 'seatInstances']): ?Event;
}

