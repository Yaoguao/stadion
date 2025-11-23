<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\Interfaces\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class EventService
{
    public function __construct(
        private EventRepositoryInterface $eventRepository
    ) {}

    /**
     * Get all events with pagination.
     */
    public function getAllEvents(int $perPage = 15): LengthAwarePaginator
    {
        return $this->eventRepository->paginate($perPage);
    }

    /**
     * Get event by ID.
     */
    public function getEventById(string $id, bool $withRelations = false): ?Event
    {
        if ($withRelations) {
            return $this->eventRepository->findWithRelations($id);
        }

        return $this->eventRepository->find($id);
    }

    /**
     * Create a new event.
     */
    public function createEvent(array $data): Event
    {
        $event = $this->eventRepository->create($data);
        
        // Автоматически создаем экземпляры мест для события
        $this->generateSeatInstances($event->id, $event->venue_id);
        
        return $event;
    }

    /**
     * Generate seat instances for event from venue seats.
     */
    public function generateSeatInstances(string $eventId, string $venueId): int
    {
        $seats = \App\Models\Seat::where('venue_id', $venueId)->get();
        $created = 0;

        foreach ($seats as $seat) {
            // Проверяем, не существует ли уже экземпляр для этого места и события
            $exists = \App\Models\SeatInstance::where('event_id', $eventId)
                ->where('seat_id', $seat->id)
                ->exists();

            if (!$exists) {
                \App\Models\SeatInstance::create([
                    'event_id' => $eventId,
                    'seat_id' => $seat->id,
                    'price' => $seat->base_price,
                    'status' => \App\Models\SeatInstance::STATUS_AVAILABLE,
                ]);
                $created++;
            }
        }

        return $created;
    }

    /**
     * Update event.
     */
    public function updateEvent(string $id, array $data): bool
    {
        return $this->eventRepository->update($id, $data);
    }

    /**
     * Delete event.
     */
    public function deleteEvent(string $id): bool
    {
        return $this->eventRepository->delete($id);
    }

    /**
     * Publish event.
     */
    public function publishEvent(string $id): bool
    {
        return $this->eventRepository->publish($id);
    }

    /**
     * Unpublish event.
     */
    public function unpublishEvent(string $id): bool
    {
        return $this->eventRepository->unpublish($id);
    }

    /**
     * Get published events.
     */
    public function getPublishedEvents(): Collection
    {
        return $this->eventRepository->getPublished();
    }

    /**
     * Get upcoming events.
     */
    public function getUpcomingEvents(?Carbon $from = null): Collection
    {
        return $this->eventRepository->getUpcoming($from);
    }

    /**
     * Get events by venue.
     */
    public function getEventsByVenue(string $venueId): Collection
    {
        return $this->eventRepository->getByVenue($venueId);
    }

    /**
     * Search events.
     */
    public function searchEvents(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->eventRepository->search($query, $perPage);
    }

    /**
     * Get events in date range.
     */
    public function getEventsInDateRange(Carbon $start, Carbon $end): Collection
    {
        return $this->eventRepository->getInDateRange($start, $end);
    }
}

