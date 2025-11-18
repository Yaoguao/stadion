<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(Request $request)
    {
        // Статистика пользователей
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Статистика событий
        $totalEvents = Event::count();
        $publishedEvents = Event::where('is_published', true)->count();
        $upcomingEvents = Event::where('start_at', '>=', now())
            ->where('is_published', true)
            ->count();

        // Статистика бронирований
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', Booking::STATUS_PENDING)->count();
        $paidBookings = Booking::where('status', Booking::STATUS_PAID)->count();
        $cancelledBookings = Booking::where('status', Booking::STATUS_CANCELLED)->count();

        // Статистика платежей
        $totalRevenue = Payment::where('status', Payment::STATUS_SUCCESS)
            ->sum('amount');
        $todayRevenue = Payment::where('status', Payment::STATUS_SUCCESS)
            ->whereDate('created_at', today())
            ->sum('amount');
        $thisMonthRevenue = Payment::where('status', Payment::STATUS_SUCCESS)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

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

        return view('admin.dashboard', compact(
            'totalUsers',
            'newUsersThisMonth',
            'totalEvents',
            'publishedEvents',
            'upcomingEvents',
            'totalBookings',
            'pendingBookings',
            'paidBookings',
            'cancelledBookings',
            'totalRevenue',
            'todayRevenue',
            'thisMonthRevenue',
            'recentBookings',
            'upcomingEventsList'
        ));
    }
}

