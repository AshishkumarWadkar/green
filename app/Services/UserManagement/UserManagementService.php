<?php

namespace App\Services\UserManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserManagementService
{
    public function getUserListingQuery(?string $search = null): Builder
    {
        $searchTerm = trim((string) $search);

        return User::query()
            ->with('roles')
            ->when($searchTerm !== '', function (Builder $builder) use ($searchTerm) {
                $builder->where(function (Builder $inner) use ($searchTerm) {
                    $inner->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('username', 'like', "%{$searchTerm}%");
                });
            })
            ->orderByDesc('id');
    }

    /**
     * Validate and create a user.
     *
     * @throws ValidationException
     */
    public function createUser(User $actingUser, array $payload): User
    {
        $validator = Validator::make($payload, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();
        $this->ensureRolesAreAssignable($actingUser, $data['roles'] ?? []);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
        ]);

        $this->syncRoles($user, $data['roles'] ?? null);

        return $user->load('roles');
    }

    /**
     * Validate and update a user.
     *
     * @throws ValidationException
     */
    public function updateUser(User $actingUser, User $targetUser, array $payload): User
    {
        $validator = Validator::make($payload, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $targetUser->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = $validator->validated();
        $this->ensureCanManageUser($actingUser, $targetUser);
        $this->ensureRolesAreAssignable($actingUser, $data['roles'] ?? []);

        $updateData = [
            'name' => $data['name'],
            'username' => $data['username'],
        ];

        if (array_key_exists('is_active', $data)) {
            $updateData['is_active'] = (bool) $data['is_active'];
        }

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $targetUser->update($updateData);
        $this->syncRoles($targetUser, $data['roles'] ?? null);

        return $targetUser->load('roles');
    }

    public function deleteUser(User $actingUser, User $targetUser): void
    {
        $this->ensureCanManageUser($actingUser, $targetUser);

        if ($actingUser->id === $targetUser->id) {
            throw new \DomainException('You cannot delete your own account.');
        }

        $targetUser->delete();
    }

    public function toggleStatus(User $actingUser, User $targetUser): User
    {
        $this->ensureCanManageUser($actingUser, $targetUser);

        if ($actingUser->id === $targetUser->id) {
            throw new \DomainException('You cannot change your own status.');
        }

        $targetUser->is_active = !$targetUser->is_active;
        $targetUser->save();

        return $targetUser->load('roles');
    }

    public function ensureCanManageUser(User $actingUser, User $targetUser): void
    {
        $actingUser->loadMissing('roles');
        $targetUser->loadMissing('roles');

        if (!$this->canManageUser($actingUser, $targetUser)) {
            throw new \DomainException('You are not allowed to manage users with a higher role than yours.');
        }
    }

    public function getAssignableRolesForUser(User $user)
    {
        $user->loadMissing('roles');
        $allRoles = Role::all();
        $actingDepth = $this->getUserHighestRoleDepth($user);

        if ($actingDepth === null) {
            return collect([]);
        }

        $reportsToConfig = config('reporting.reports_to_role', []);

        return $allRoles->filter(function (Role $role) use ($actingDepth, $reportsToConfig) {
            $roleDepth = $this->getRoleDepth($role->name, $reportsToConfig);

            return $actingDepth <= $roleDepth;
        })->values();
    }

    public function getAssignableRoleIdsForUser(User $user): array
    {
        return $this->getAssignableRolesForUser($user)->pluck('id')->map('intval')->toArray();
    }

    private function ensureRolesAreAssignable(User $actingUser, array $requestedRoleIds): void
    {
        if (empty($requestedRoleIds)) {
            return;
        }

        $assignableRoleIds = $this->getAssignableRoleIdsForUser($actingUser);
        $invalidRoleIds = array_diff(array_map('intval', $requestedRoleIds), $assignableRoleIds);

        if (!empty($invalidRoleIds)) {
            throw new \DomainException('You are not allowed to assign one or more of the selected roles.');
        }
    }

    private function syncRoles(User $user, ?array $roleIds): void
    {
        if ($roleIds === null) {
            return;
        }

        if (empty($roleIds)) {
            $user->syncRoles([]);
            return;
        }

        $roles = Role::whereIn('id', $roleIds)->get();
        $user->syncRoles($roles);
    }

    private function canManageUser(User $actingUser, User $targetUser): bool
    {
        $actingDepth = $this->getUserHighestRoleDepth($actingUser);
        $targetDepth = $this->getUserHighestRoleDepth($targetUser);

        if ($actingDepth === null) {
            return false;
        }

        if ($targetDepth === null) {
            return true;
        }

        return $actingDepth <= $targetDepth;
    }

    private function getUserHighestRoleDepth(User $user): ?int
    {
        $roles = $user->roles->pluck('name')->toArray();

        if (empty($roles)) {
            return null;
        }

        $reportsToConfig = config('reporting.reports_to_role', []);
        $depths = array_map(function ($roleName) use ($reportsToConfig) {
            return $this->getRoleDepth($roleName, $reportsToConfig);
        }, $roles);

        return min($depths);
    }

    private function getRoleDepth(string $roleName, array $reportsToConfig): int
    {
        static $cache = [];

        if (isset($cache[$roleName])) {
            return $cache[$roleName];
        }

        $depth = 0;
        $current = $roleName;
        $visited = [];

        while (isset($reportsToConfig[$current]) && !in_array($current, $visited, true)) {
            $visited[] = $current;
            $current = $reportsToConfig[$current];
            $depth++;
        }

        $cache[$roleName] = $depth;

        return $depth;
    }
}
