<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class Home extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 12;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Получаем события с поиском или без
        if ($this->search) {
            $events = \App\Models\Event::where('is_published', true)
                ->where('start_at', '>=', now())
                ->where(function ($q) {
                    $q->where('title', 'ilike', "%{$this->search}%")
                      ->orWhere('description', 'ilike', "%{$this->search}%");
                })
                ->with(['venue'])
                ->orderBy('start_at', 'asc')
                ->paginate($this->perPage);
        } else {
            $events = \App\Models\Event::where('is_published', true)
                ->where('start_at', '>=', now())
                ->with(['venue'])
                ->orderBy('start_at', 'asc')
                ->paginate($this->perPage);
        }

        // Получаем популярные события (с наибольшим количеством бронирований)
        $popularEvents = \App\Models\Event::where('is_published', true)
            ->where('start_at', '>=', now())
            ->withCount('bookings')
            ->with(['venue'])
            ->orderBy('bookings_count', 'desc')
            ->limit(3)
            ->get();

        // Статистика
        $totalEvents = \App\Models\Event::where('is_published', true)
            ->where('start_at', '>=', now())
            ->count();
        
        $totalVenues = \App\Models\Venue::count();

        return view('livewire.home', [
            'events' => $events,
            'popularEvents' => $popularEvents,
            'totalEvents' => $totalEvents,
            'totalVenues' => $totalVenues,
        ])->layout('layouts.app');
    }
}
