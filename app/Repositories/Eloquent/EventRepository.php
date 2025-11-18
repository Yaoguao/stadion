<?php

namespace App\Repositories\Eloquent;

use App\Models\Event;
use App\Repositories\Interfaces\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Event $model)
    {
        parent::__construct($model);
    }

    /**
     * Get published events.
     */
    public function getPublished(): Collection
    {
        return $this->model->where('is_published', true)
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->get();
    }

    /**
     * Get upcoming events.
     */
    public function getUpcoming(?Carbon $from = null): Collection
    {
        $from = $from ?? now();
        
        return $this->model->where('start_at', '>=', $from)
            ->orderBy('start_at')
            ->get();
    }

    /**
     * Get events by venue.
     */
    public function getByVenue(string $venueId): Collection
    {
        return $this->model->where('venue_id', $venueId)
            ->orderBy('start_at')
            ->get();
    }

    /**
     * Get events with available seats.
     */
    public function getWithAvailableSeats(): Collection
    {
        return $this->model->whereHas('seatInstances', function ($query) {
            $query->where('status', 'available');
        })->get();
    }

    /**
     * Search events by title or description.
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('title', 'ilike', "%{$query}%")
              ->orWhere('description', 'ilike', "%{$query}%");
        })->orderBy('start_at')->paginate($perPage);
    }

    /**
     * Get events in date range.
     */
    public function getInDateRange(Carbon $start, Carbon $end): Collection
    {
        return $this->model->whereBetween('start_at', [$start, $end])
            ->orderBy('start_at')
            ->get();
    }

    /**
     * Publish event.
     */
    public function publish(string $eventId): bool
    {
        return $this->update($eventId, ['is_published' => true]);
    }

    /**
     * Unpublish event.
     */
    public function unpublish(string $eventId): bool
    {
        return $this->update($eventId, ['is_published' => false]);
    }

    /**
     * Get event with relations.
     */
    public function findWithRelations(string $id, array $relations = ['venue', 'seatInstances']): ?Event
    {
        return $this->model->with($relations)->find($id);
    }
}

