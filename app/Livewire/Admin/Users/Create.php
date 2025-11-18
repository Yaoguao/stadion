<?php

namespace App\Livewire\Admin\Users;

use App\Services\UserService;
use Livewire\Component;

class Create extends Component
{
    public $full_name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedRoles = [];

    protected $rules = [
        'full_name' => 'required|string|max:100',
        'email' => 'required|email|max:150|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|string|min:8|confirmed',
    ];

    protected $messages = [
        'full_name.required' => 'Имя обязательно для заполнения.',
        'email.required' => 'Email обязателен для заполнения.',
        'email.email' => 'Введите корректный email адрес.',
        'email.unique' => 'Пользователь с таким email уже существует.',
        'password.required' => 'Пароль обязателен для заполнения.',
        'password.min' => 'Пароль должен содержать минимум 8 символов.',
        'password.confirmed' => 'Пароли не совпадают.',
    ];

    public function mount()
    {
        // Инициализация компонента
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function save()
    {
        $this->validate();

        $userService = app(UserService::class);
        
        $userData = [
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];

        $user = $userService->createUser($userData, $this->password);

        // Назначение ролей
        foreach ($this->selectedRoles as $roleId) {
            $role = \App\Models\Role::find($roleId);
            if ($role) {
                $userService->assignRole($user->id, $role->name);
            }
        }

        session()->flash('message', 'Пользователь успешно создан.');
        
        return $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render()
    {
        $userService = app(UserService::class);
        $roles = $userService->getAllRoles();

        return view('livewire.admin.users.create', [
            'roles' => $roles,
        ])->layout('layouts.admin');
    }
}
