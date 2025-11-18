<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

// Главная страница - публичная
Route::get('/', Home::class)->name('home');

// Админ панель роуты
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    
    // Users routes
    Route::get('/users', \App\Livewire\Admin\Users\Index::class)->name('users.index');
    Route::get('/users/create', \App\Livewire\Admin\Users\Create::class)->name('users.create');
    Route::get('/users/{user:id}/edit', \App\Livewire\Admin\Users\Edit::class)->name('users.edit');
    
    // Events routes
    Route::get('/events', \App\Livewire\Admin\Events\Index::class)->name('events.index');
    Route::get('/events/create', \App\Livewire\Admin\Events\Create::class)->name('events.create');
    Route::get('/events/{event:id}/edit', \App\Livewire\Admin\Events\Edit::class)->name('events.edit');
    
    // Bookings routes
    Route::get('/bookings', \App\Livewire\Admin\Bookings\Index::class)->name('bookings.index');
    
    // Payments routes
    Route::get('/payments', \App\Livewire\Admin\Payments\Index::class)->name('payments.index');
});
