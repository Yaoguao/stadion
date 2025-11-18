<?php

namespace App\Livewire\Admin\Users;

use App\Services\UserService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteUser($userId)
    {
        $userService = app(UserService::class);
        $userService->deleteUser($userId);
        
        session()->flash('message', 'Пользователь успешно удален.');
    }

    public function render()
    {
        $userService = app(UserService::class);
        
        if ($this->search) {
            $users = $userService->searchUsers($this->search, $this->perPage);
        } else {
            $users = $userService->getAllUsers($this->perPage);
        }

        return view('livewire.admin.users.index', [
            'users' => $users,
        ])->layout('layouts.admin');
    }
}
