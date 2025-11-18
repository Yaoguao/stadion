<?php

namespace App\Livewire\Admin\Events;

use App\Services\EventService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    public $filterPublished = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterPublished' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterPublished()
    {
        $this->resetPage();
    }

    public function deleteEvent($eventId)
    {
        $eventService = app(EventService::class);
        $eventService->deleteEvent($eventId);
        
        session()->flash('message', 'Событие успешно удалено.');
    }

    public function togglePublish($eventId, $isPublished)
    {
        $eventService = app(EventService::class);
        
        if ($isPublished) {
            $eventService->unpublishEvent($eventId);
            session()->flash('message', 'Событие снято с публикации.');
        } else {
            $eventService->publishEvent($eventId);
            session()->flash('message', 'Событие опубликовано.');
        }
    }

    public function render()
    {
        $eventService = app(EventService::class);
        
        if ($this->search) {
            $events = $eventService->searchEvents($this->search, $this->perPage);
        } else {
            $events = $eventService->getAllEvents($this->perPage);
        }

        // Фильтр по публикации применяется к результатам пагинации
        if ($this->filterPublished === 'published') {
            $events->getCollection()->transform(function ($event) {
                return $event->is_published === true ? $event : null;
            });
            $events->setCollection($events->getCollection()->filter());
        } elseif ($this->filterPublished === 'unpublished') {
            $events->getCollection()->transform(function ($event) {
                return $event->is_published === false ? $event : null;
            });
            $events->setCollection($events->getCollection()->filter());
        }

        return view('livewire.admin.events.index', [
            'events' => $events,
        ])->layout('layouts.admin');
    }
}
