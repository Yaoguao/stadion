<?php

namespace App\Livewire\Admin\Events;

use App\Models\Venue;
use App\Services\EventService;
use Livewire\Component;

class Create extends Component
{
    public $venue_id = '';
    public $title = '';
    public $description = '';
    public $start_at = '';
    public $end_at = '';
    public $image_url = '';
    public $is_published = false;

    protected $rules = [
        'venue_id' => 'required|exists:venues,id',
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_at' => 'required|date|after:now',
        'end_at' => 'required|date|after:start_at',
        'image_url' => 'nullable|url|max:500',
        'is_published' => 'boolean',
    ];

    protected $messages = [
        'venue_id.required' => 'Выберите место проведения.',
        'venue_id.exists' => 'Выбранное место проведения не существует.',
        'title.required' => 'Название события обязательно.',
        'start_at.required' => 'Дата начала обязательна.',
        'start_at.after' => 'Дата начала должна быть в будущем.',
        'end_at.required' => 'Дата окончания обязательна.',
        'end_at.after' => 'Дата окончания должна быть после даты начала.',
        'image_url.url' => 'Введите корректный URL изображения.',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function save()
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

        $eventService->createEvent($eventData);

        session()->flash('message', 'Событие успешно создано.');
        
        return $this->redirect(route('admin.events.index'), navigate: true);
    }

    public function render()
    {
        $venues = Venue::orderBy('name')->get();

        return view('livewire.admin.events.create', [
            'venues' => $venues,
        ])->layout('layouts.admin');
    }
}
