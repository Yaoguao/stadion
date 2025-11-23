<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    protected $messages = [
        'email.required' => 'Email обязателен для заполнения.',
        'email.email' => 'Введите корректный email адрес.',
        'password.required' => 'Пароль обязателен для заполнения.',
        'password.min' => 'Пароль должен содержать минимум 6 символов.',
    ];

    public function login()
    {
        $this->validate();

        // Laravel Auth использует password, но у нас password_hash
        // Нужно проверить вручную
        $user = \App\Models\User::where('email', $this->email)->first();

        if ($user && Hash::check($this->password, $user->password_hash)) {
            Auth::login($user, $this->remember);
            
            session()->regenerate();
            
            // Редирект в зависимости от роли
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            
            return redirect()->route('home');
        }

        $this->addError('email', 'Неверный email или пароль.');
    }

    public function render()
    {
        return view('livewire.login')->layout('layouts.app');
    }
}

