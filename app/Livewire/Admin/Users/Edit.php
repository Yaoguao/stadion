<?php

namespace App\Livewire\Admin\Users;

use App\Services\UserService;
use Livewire\Component;

class Edit extends Component
{
    public $userId;
    public $full_name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedRoles = [];
    public $user;

    protected $rules = [
        'full_name' => 'required|string|max:100',
        'email' => 'required|email|max:150',
        'phone' => 'nullable|string|max:20',
        'password' => 'nullable|string|min:8|confirmed',
    ];

    protected $messages = [
        'full_name.required' => 'Имя обязательно для заполнения.',
        'email.required' => 'Email обязателен для заполнения.',
        'email.email' => 'Введите корректный email адрес.',
        'password.min' => 'Пароль должен содержать минимум 8 символов.',
        'password.confirmed' => 'Пароли не совпадают.',
    ];

    public function mount($user)
    {
        $this->userId = $user;
        $this->loadUser();
    }

    public function loadUser()
    {
        $userService = app(UserService::class);
        $this->user = $userService->getUserById($this->userId);

        if (!$this->user) {
            session()->flash('error', 'Пользователь не найден.');
            return $this->redirect(route('admin.users.index'), navigate: true);
        }

        // Загружаем роли, если они еще не загружены
        if (!$this->user->relationLoaded('roles')) {
            $this->user->load('roles');
        }

        $this->full_name = $this->user->full_name ?? '';
        $this->email = $this->user->email ?? '';
        $this->phone = $this->user->phone ?? '';
        
        // Загружаем текущие роли пользователя
        $this->selectedRoles = $this->user->roles->pluck('id')->toArray();
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'email') {
            $this->validateOnly('email', [
                'email' => 'required|email|max:150|unique:users,email,' . $this->userId,
            ]);
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function update()
    {
        // Валидация email с учетом текущего пользователя
        $this->validate([
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,' . $this->userId,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $userService = app(UserService::class);
        
        $userData = [
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];

        // Обновление пользователя
        $userService->updateUser($this->userId, $userData);

        // Обновление пароля, если указан
        if (!empty($this->password)) {
            $userService->updatePassword($this->userId, $this->password);
        }

        // Обновление ролей
        $currentRoleIds = $this->user->roles->pluck('id')->toArray();
        
        // Удаляем роли, которые были сняты
        foreach ($currentRoleIds as $roleId) {
            if (!in_array($roleId, $this->selectedRoles)) {
                $role = \App\Models\Role::find($roleId);
                if ($role) {
                    $userService->removeRole($this->userId, $role->name);
                }
            }
        }

        // Добавляем новые роли
        foreach ($this->selectedRoles as $roleId) {
            if (!in_array($roleId, $currentRoleIds)) {
                $role = \App\Models\Role::find($roleId);
                if ($role) {
                    $userService->assignRole($this->userId, $role->name);
                }
            }
        }

        session()->flash('message', 'Пользователь успешно обновлен.');
        
        return $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render()
    {
        $userService = app(UserService::class);
        $roles = $userService->getAllRoles();

        return view('livewire.admin.users.edit', [
            'roles' => $roles,
        ])->layout('layouts.admin');
    }
}
