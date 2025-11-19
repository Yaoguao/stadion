<?php

namespace App\Livewire;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class Profile extends Component
{
    use WithPagination;

    public $activeTab = 'profile';
    
    // Профиль
    public $full_name;
    public $email;
    public $phone;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    protected $rules = [
        'full_name' => 'required|min:3|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:20',
        'current_password' => 'nullable|required_with:new_password',
        'new_password' => 'nullable|min:6|confirmed',
    ];

    protected $messages = [
        'full_name.required' => 'ФИО обязательно для заполнения.',
        'full_name.min' => 'ФИО должно содержать минимум 3 символа.',
        'email.required' => 'Email обязателен для заполнения.',
        'email.email' => 'Введите корректный email адрес.',
        'current_password.required_with' => 'Текущий пароль обязателен для смены пароля.',
        'new_password.min' => 'Новый пароль должен содержать минимум 6 символов.',
        'new_password.confirmed' => 'Пароли не совпадают.',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->full_name = $user->full_name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        
        // Проверяем, нужно ли переключиться на вкладку бронирований
        if (session('activeTab') === 'bookings') {
            $this->activeTab = 'bookings';
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updateProfile()
    {
        $this->validate([
            'full_name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $user->update([
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        session()->flash('success', 'Профиль успешно обновлен.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password_hash)) {
            $this->addError('current_password', 'Текущий пароль неверен.');
            return;
        }

        $user->update([
            'password_hash' => Hash::make($this->new_password),
        ]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        session()->flash('success', 'Пароль успешно изменен.');
    }

    public function render()
    {
        $user = Auth::user();
        
        $bookings = null;
        $tickets = null;

        if ($this->activeTab === 'bookings') {
            $bookings = Booking::where('user_id', $user->id)
                ->with(['event.venue', 'payment', 'bookingItems.seatInstance.seat'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        if ($this->activeTab === 'tickets') {
            // Получаем билеты через bookingItems
            $tickets = \App\Models\Ticket::whereHas('bookingItem.booking', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->where('status', Booking::STATUS_PAID);
                })
                ->with([
                    'bookingItem.booking.event.venue',
                    'bookingItem.seatInstance.seat'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('livewire.profile', [
            'user' => $user,
            'bookings' => $bookings,
            'tickets' => $tickets,
        ])->layout('layouts.app');
    }
}

