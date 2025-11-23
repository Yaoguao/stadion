<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\BookEvent;
use App\Livewire\Home;
use App\Livewire\Login;
use App\Livewire\Profile;
use App\Livewire\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Главная страница - публичная
Route::get('/', Home::class)->name('home');

// Авторизация
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// Выход
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout')->middleware('auth');

// Личный кабинет
Route::middleware('auth')->group(function () {
    Route::get('/profile', Profile::class)->name('profile');
});

// Бронирование билетов
Route::get('/events/{eventId}/book', BookEvent::class)->name('events.book');

// Админ панель роуты - только для админов
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    
    // Users routes
    Route::get('/users', \App\Livewire\Admin\Users\Index::class)->name('users.index');
    Route::get('/users/create', \App\Livewire\Admin\Users\Create::class)->name('users.create');
    Route::get('/users/{user:id}/edit', \App\Livewire\Admin\Users\Edit::class)->name('users.edit');
    
    // Events routes
    Route::get('/events', \App\Livewire\Admin\Events\Index::class)->name('events.index');
    Route::get('/events/create', \App\Livewire\Admin\Events\Create::class)->name('events.create');
    Route::get('/events/{event:id}/edit', \App\Livewire\Admin\Events\Edit::class)->name('events.edit');
    Route::get('/events/{event:id}/seats', \App\Livewire\Admin\Events\ManageSeats::class)->name('events.manage-seats');
    
    // Bookings routes
    Route::get('/bookings', \App\Livewire\Admin\Bookings\Index::class)->name('bookings.index');
    
    // Payments routes
    Route::get('/payments', \App\Livewire\Admin\Payments\Index::class)->name('payments.index');
});
