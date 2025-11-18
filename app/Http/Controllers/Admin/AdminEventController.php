<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminEventController extends Controller
{
    public function __construct(
        private EventService $eventService
    ) {}

    /**
     * Display a listing of events.
     */
    public function index(Request $request): View
    {
        $query = $request->get('search');
        $perPage = $request->get('per_page', 15);

        if ($query) {
            $events = $this->eventService->searchEvents($query, $perPage);
        } else {
            $events = $this->eventService->getAllEvents($perPage);
        }

        $events->load('venue');

        return view('admin.events.index', compact('events', 'query'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): View
    {
        $venues = Venue::all();
        return view('admin.events.create', compact('venues'));
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'venue_id' => 'required|exists:venues,id',
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'start_at' => 'required|date|after:now',
            'end_at' => 'nullable|date|after:start_at',
            'image_url' => 'nullable|url|max:255',
            'is_published' => 'boolean',
        ]);

        $event = $this->eventService->createEvent($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Событие успешно создано.');
    }

    /**
     * Display the specified event.
     */
    public function show(string $id): View
    {
        $event = $this->eventService->getEventById($id, true);

        if (!$event) {
            abort(404);
        }

        $event->load(['venue', 'seatInstances', 'bookings']);

        return view('admin.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(string $id): View
    {
        $event = $this->eventService->getEventById($id);

        if (!$event) {
            abort(404);
        }

        $venues = Venue::all();

        return view('admin.events.edit', compact('event', 'venues'));
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $event = $this->eventService->getEventById($id);

        if (!$event) {
            abort(404);
        }

        $validated = $request->validate([
            'venue_id' => 'required|exists:venues,id',
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'image_url' => 'nullable|url|max:255',
            'is_published' => 'boolean',
        ]);

        $this->eventService->updateEvent($id, $validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Событие успешно обновлено.');
    }

    /**
     * Remove the specified event.
     */
    public function destroy(string $id): RedirectResponse
    {
        $event = $this->eventService->getEventById($id);

        if (!$event) {
            abort(404);
        }

        $this->eventService->deleteEvent($id);

        return redirect()->route('admin.events.index')
            ->with('success', 'Событие успешно удалено.');
    }

    /**
     * Publish the specified event.
     */
    public function publish(string $id): RedirectResponse
    {
        $this->eventService->publishEvent($id);

        return redirect()->back()
            ->with('success', 'Событие опубликовано.');
    }

    /**
     * Unpublish the specified event.
     */
    public function unpublish(string $id): RedirectResponse
    {
        $this->eventService->unpublishEvent($id);

        return redirect()->back()
            ->with('success', 'Событие снято с публикации.');
    }
}

