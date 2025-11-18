<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminBookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    /**
     * Display a listing of bookings.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status');
        $perPage = $request->get('per_page', 15);

        if ($status) {
            $bookingsCollection = $this->bookingService->getBookingsByStatus($status);
            // Convert collection to paginator manually
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $items = $bookingsCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $bookings = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $bookingsCollection->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $bookings = $this->bookingService->getAllBookings($perPage);
        }

        $bookings->load(['user', 'event', 'payment']);

        return view('admin.bookings.index', compact('bookings', 'status'));
    }

    /**
     * Display the specified booking.
     */
    public function show(string $id): View
    {
        $booking = $this->bookingService->getBookingById($id, true);

        if (!$booking) {
            abort(404);
        }

        $booking->load(['user', 'event', 'bookingItems.seatInstance.seat', 'payment']);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Mark booking as paid.
     */
    public function markAsPaid(string $id): RedirectResponse
    {
        $booking = $this->bookingService->getBookingById($id);

        if (!$booking) {
            abort(404);
        }

        $this->bookingService->markBookingAsPaid($id);

        return redirect()->back()
            ->with('success', 'Бронирование отмечено как оплаченное.');
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(string $id): RedirectResponse
    {
        $booking = $this->bookingService->getBookingById($id);

        if (!$booking) {
            abort(404);
        }

        $this->bookingService->cancelBooking($id);

        return redirect()->back()
            ->with('success', 'Бронирование отменено.');
    }

    /**
     * Expire the specified booking.
     */
    public function expire(string $id): RedirectResponse
    {
        $booking = $this->bookingService->getBookingById($id);

        if (!$booking) {
            abort(404);
        }

        $this->bookingService->expireBooking($id);

        return redirect()->back()
            ->with('success', 'Бронирование истекло.');
    }
}

