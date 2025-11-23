<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Register extends Component
{
    public $full_name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'full_name' => 'required|min:3|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|min:6|confirmed',
    ];

    protected $messages = [
        'full_name.required' => 'ФИО обязательно для заполнения.',
        'full_name.min' => 'ФИО должно содержать минимум 3 символа.',
        'email.required' => 'Email обязателен для заполнения.',
        'email.email' => 'Введите корректный email адрес.',
        'email.unique' => 'Пользователь с таким email уже существует.',
        'password.required' => 'Пароль обязателен для заполнения.',
        'password.min' => 'Пароль должен содержать минимум 6 символов.',
        'password.confirmed' => 'Пароли не совпадают.',
    ];

    public function register()
    {
        $this->validate();

        // Создаем пользователя
        $user = User::create([
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password_hash' => Hash::make($this->password),
        ]);

        // Назначаем роль "user" по умолчанию
        // Создаем роль "user", если её нет
        $userRole = Role::firstOrCreate(
            ['name' => 'user'],
            [
                'display_name' => 'Пользователь',
                'description' => 'Обычный пользователь системы'
            ]
        );
        
        // Назначаем роль пользователю
        $user->roles()->attach($userRole->id, ['assigned_at' => now()]);

        // Автоматически авторизуем пользователя
        Auth::login($user);
        
        session()->regenerate();

        // Редирект на главную
        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.register')->layout('layouts.app');
    }
}

