<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Assign role to user.
     */
    public function assignRole(string $userId, string $roleId): bool;

    /**
     * Remove role from user.
     */
    public function removeRole(string $userId, string $roleId): bool;

    /**
     * Get users with specific role.
     */
    public function getUsersByRole(string $roleName): Collection;

    /**
     * Get users with bookings.
     */
    public function getUsersWithBookings(array $columns = ['*']): Collection;

    /**
     * Search users by name or email.
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator;
}

