<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Assign role to user.
     */
    public function assignRole(string $userId, string $roleId): bool
    {
        $user = $this->findOrFail($userId);
        
        if (!$user->roles()->where('roles.id', $roleId)->exists()) {
            $user->roles()->attach($roleId);
            return true;
        }

        return false;
    }

    /**
     * Assign role by name.
     */
    public function assignRoleByName(string $userId, string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            return false;
        }

        return $this->assignRole($userId, $role->id);
    }

    /**
     * Remove role from user.
     */
    public function removeRole(string $userId, string $roleId): bool
    {
        $user = $this->findOrFail($userId);
        $user->roles()->detach($roleId);
        return true;
    }

    /**
     * Remove role by name.
     */
    public function removeRoleByName(string $userId, string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            return false;
        }

        return $this->removeRole($userId, $role->id);
    }

    /**
     * Get users with specific role.
     */
    public function getUsersByRole(string $roleName): Collection
    {
        return $this->model->whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->get();
    }

    /**
     * Get users with bookings.
     */
    public function getUsersWithBookings(array $columns = ['*']): Collection
    {
        return $this->model->with('bookings')->get($columns);
    }

    /**
     * Search users by name or email.
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('full_name', 'ilike', "%{$query}%")
              ->orWhere('email', 'ilike', "%{$query}%");
        })->paginate($perPage);
    }

    /**
     * Create user with password hash.
     */
    public function createWithPassword(array $data, string $password): User
    {
        $data['password_hash'] = bcrypt($password);
        return $this->create($data);
    }

    /**
     * Update user password.
     */
    public function updatePassword(string $userId, string $password): bool
    {
        return $this->update($userId, [
            'password_hash' => bcrypt($password),
        ]);
    }
}

