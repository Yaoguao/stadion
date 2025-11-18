<?php

namespace App\Livewire\Admin\Bookings;

use App\Models\Booking;
use App\Services\BookingService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    public $filterStatus = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function cancelBooking($bookingId)
    {
        $bookingService = app(BookingService::class);
        $bookingService->cancelBooking($bookingId);
        
        session()->flash('message', 'Бронирование успешно отменено.');
    }

    public function markAsPaid($bookingId)
    {
        $bookingService = app(BookingService::class);
        $bookingService->markBookingAsPaid($bookingId);
        
        session()->flash('message', 'Бронирование отмечено как оплаченное.');
    }

    public function render()
    {
        $bookingService = app(BookingService::class);
        
        if ($this->filterStatus) {
            $bookings = $bookingService->getBookingsByStatus($this->filterStatus);
            // Конвертируем коллекцию в пагинатор
            $bookings = new \Illuminate\Pagination\LengthAwarePaginator(
                $bookings->forPage(\Illuminate\Pagination\Paginator::resolveCurrentPage(), $this->perPage),
                $bookings->count(),
                $this->perPage,
                \Illuminate\Pagination\Paginator::resolveCurrentPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $bookings = $bookingService->getAllBookings($this->perPage);
        }

        return view('livewire.admin.bookings.index', [
            'bookings' => $bookings,
        ])->layout('layouts.admin');
    }
}
