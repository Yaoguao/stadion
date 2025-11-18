<?php

namespace App\Livewire\Admin\Events;

use App\Models\Venue;
use App\Services\EventService;
use Livewire\Component;

class Edit extends Component
{
    public $eventId;
    public $venue_id = '';
    public $title = '';
    public $description = '';
    public $start_at = '';
    public $end_at = '';
    public $image_url = '';
    public $is_published = false;
    public $event;

    protected $rules = [
        'venue_id' => 'required|exists:venues,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_at' => 'required|date',
        'end_at' => 'required|date|after:start_at',
        'image_url' => 'nullable|url|max:500',
        'is_published' => 'boolean',
    ];

    protected $messages = [
        'venue_id.required' => 'Выберите место проведения.',
        'venue_id.exists' => 'Выбранное место проведения не существует.',
        'title.required' => 'Название события обязательно.',
        'start_at.required' => 'Дата начала обязательна.',
        'end_at.required' => 'Дата окончания обязательна.',
        'end_at.after' => 'Дата окончания должна быть после даты начала.',
        'image_url.url' => 'Введите корректный URL изображения.',
    ];

    public function mount($event)
    {
        $this->eventId = $event;
        $this->loadEvent();
    }

    public function loadEvent()
    {
        $eventService = app(EventService::class);
        $this->event = $eventService->getEventById($this->eventId, true);

        if (!$this->event) {
            session()->flash('error', 'Событие не найдено.');
            return $this->redirect(route('admin.events.index'), navigate: true);
        }

        $this->venue_id = $this->event->venue_id ?? '';
        $this->title = $this->event->title ?? '';
        $this->description = $this->event->description ?? '';
        $this->start_at = $this->event->start_at ? $this->event->start_at->format('Y-m-d\TH:i') : '';
        $this->end_at = $this->event->end_at ? $this->event->end_at->format('Y-m-d\TH:i') : '';
        $this->image_url = $this->event->image_url ?? '';
        $this->is_published = $this->event->is_published ?? false;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function update()
    {
        $this->validate();

        $eventService = app(EventService::class);
        
        $eventData = [
            'venue_id' => $this->venue_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'image_url' => $this->image_url ?: null,
            'is_published' => $this->is_published,
        ];

        $eventService->updateEvent($this->eventId, $eventData);

        session()->flash('message', 'Событие успешно обновлено.');
        
        return $this->redirect(route('admin.events.index'), navigate: true);
    }

    public function render()
    {
        $venues = Venue::orderBy('name')->get();

        return view('livewire.admin.events.edit', [
            'venues' => $venues,
        ])->layout('layouts.admin');
    }
}
