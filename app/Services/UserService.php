<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get all users with pagination.
     */
    public function getAllUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    /**
     * Get user by ID.
     */
    public function getUserById(string $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data, ?string $password = null): User
    {
        if ($password) {
            return $this->userRepository->createWithPassword($data, $password);
        }

        return $this->userRepository->create($data);
    }

    /**
     * Update user.
     */
    public function updateUser(string $id, array $data): bool
    {
        return $this->userRepository->update($id, $data);
    }

    /**
     * Delete user.
     */
    public function deleteUser(string $id): bool
    {
        return $this->userRepository->delete($id);
    }

    /**
     * Assign role to user.
     */
    public function assignRole(string $userId, string $roleName): bool
    {
        return $this->userRepository->assignRoleByName($userId, $roleName);
    }

    /**
     * Remove role from user.
     */
    public function removeRole(string $userId, string $roleName): bool
    {
        return $this->userRepository->removeRoleByName($userId, $roleName);
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $roleName): Collection
    {
        return $this->userRepository->getUsersByRole($roleName);
    }

    /**
     * Search users.
     */
    public function searchUsers(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->search($query, $perPage);
    }

    /**
     * Update user password.
     */
    public function updatePassword(string $userId, string $password): bool
    {
        return $this->userRepository->updatePassword($userId, $password);
    }

    /**
     * Get all roles.
     */
    public function getAllRoles(): Collection
    {
        return Role::all();
    }
}

