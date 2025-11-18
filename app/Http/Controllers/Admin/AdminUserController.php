<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = $request->get('search');
        $perPage = $request->get('per_page', 15);

        if ($query) {
            $users = $this->userService->searchUsers($query, $perPage);
        } else {
            $users = $this->userService->getAllUsers($perPage);
        }

        $roles = $this->userService->getAllRoles();

        return view('admin.users.index', compact('users', 'roles', 'query'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = $this->userService->getAllRoles();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = $this->userService->createUser(
            $validated,
            $validated['password']
        );

        // Assign roles
        if (isset($validated['roles'])) {
            foreach ($validated['roles'] as $roleName) {
                $this->userService->assignRole($user->id, $roleName);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно создан.');
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): View
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            abort(404);
        }

        $user->load(['roles', 'bookings']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(string $id): View
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            abort(404);
        }

        $user->load('roles');
        $roles = $this->userService->getAllRoles();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            abort(404);
        }

        $validated = $request->validate([
            'full_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        // Update user data
        $updateData = [
            'full_name' => $validated['full_name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        $this->userService->updateUser($id, $updateData);

        // Update password if provided
        if (isset($validated['password'])) {
            $this->userService->updatePassword($id, $validated['password']);
        }

        // Sync roles
        if (isset($validated['roles'])) {
            $user->load('roles');
            $currentRoles = $user->roles->pluck('name')->toArray();

            // Remove roles that are not in the new list
            foreach ($currentRoles as $roleName) {
                if (!in_array($roleName, $validated['roles'])) {
                    $this->userService->removeRole($id, $roleName);
                }
            }

            // Add new roles
            foreach ($validated['roles'] as $roleName) {
                if (!in_array($roleName, $currentRoles)) {
                    $this->userService->assignRole($id, $roleName);
                }
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно обновлен.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            abort(404);
        }

        $this->userService->deleteUser($id);

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно удален.');
    }
}

