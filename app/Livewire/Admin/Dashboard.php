<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalUsers;
    public $newUsersThisMonth;
    public $totalEvents;
    public $publishedEvents;
    public $upcomingEvents;
    public $totalBookings;
    public $pendingBookings;
    public $paidBookings;
    public $cancelledBookings;
    public $totalRevenue;
    public $todayRevenue;
    public $thisMonthRevenue;

    public function mount()
    {
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        // Статистика пользователей
        $this->totalUsers = User::count();
        $this->newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Статистика событий
        $this->totalEvents = Event::count();
        $this->publishedEvents = Event::where('is_published', true)->count();
        $this->upcomingEvents = Event::where('start_at', '>=', now())
            ->where('is_published', true)
            ->count();

        // Статистика бронирований
        $this->totalBookings = Booking::count();
        $this->pendingBookings = Booking::where('status', Booking::STATUS_PENDING)->count();
        $this->paidBookings = Booking::where('status', Booking::STATUS_PAID)->count();
        $this->cancelledBookings = Booking::where('status', Booking::STATUS_CANCELLED)->count();

        // Статистика платежей
        $this->totalRevenue = Payment::where('status', Payment::STATUS_SUCCESS)
            ->sum('amount');
        $this->todayRevenue = Payment::where('status', Payment::STATUS_SUCCESS)
            ->whereDate('created_at', today())
            ->sum('amount');
        $this->thisMonthRevenue = Payment::where('status', Payment::STATUS_SUCCESS)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
    }

    public function render()
    {
        // Последние бронирования
        $recentBookings = Booking::with(['user', 'event'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Предстоящие события
        $upcomingEventsList = Event::with('venue')
            ->where('start_at', '>=', now())
            ->where('is_published', true)
            ->orderBy('start_at', 'asc')
            ->limit(5)
            ->get();

        return view('livewire.admin.dashboard', [
            'recentBookings' => $recentBookings,
            'upcomingEventsList' => $upcomingEventsList,
        ])->layout('layouts.admin');
    }
}
