<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-users');
        
        $currentUser = auth()->user()->loadMissing('roles');
        $roles = $this->getAssignableRolesForUser($currentUser);

        return view('content.user-management.users.index', compact('roles'));
    }

    /**
     * Get data for DataTable (server-side processing)
     */
    public function getData(Request $request)
    {
        $this->authorize('view-users');
        
        $query = User::with('roles');
        $start = $request->get('start', 0);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('fake_id', function ($row) use ($start) {
                static $index = 0;
                $index++;
                return $start + $index;
            })
            ->editColumn('name', function ($row) {
                return '<span class="text-heading fw-medium">' . $row->name . '</span>';
            })
            ->editColumn('email', function ($row) {
                return '<span class="text-muted">' . $row->email . '</span>';
            })
            ->addColumn('roles', function ($row) {
                $roles = $row->roles->pluck('name')->toArray();
                if (empty($roles)) {
                    return '<span class="badge bg-label-secondary">No Role</span>';
                }
                $badges = array_map(function ($role) {
                    $roleColors = [
                        'CEO' => 'bg-label-danger',
                        'MD' => 'bg-label-info',
                        'Sales Manager' => 'bg-label-warning',
                        'Sales' => 'bg-label-success',
                    ];
                    $color = $roleColors[$role] ?? 'bg-label-primary';
                    return '<span class="badge ' . $color . ' me-1">' . $role . '</span>';
                }, $roles);
                return implode('', $badges);
            })
            ->editColumn('is_active', function ($row) {
                $status = $row->is_active ? 'checked' : '';
                return '<div class="form-check form-switch d-flex justify-content-center">' .
                    '<input class="form-check-input toggle-status" type="checkbox" data-id="' . $row->id . '" ' . $status . '>' .
                    '</div>';
            })
            ->addColumn('action', function ($row) {
                $canEdit = auth()->user()->can('edit-users');
                $canDelete = auth()->user()->can('delete-users');
                
                $editBtn = $canEdit 
                    ? '<button class="btn btn-sm btn-icon edit-record btn-text-secondary rounded-pill waves-effect" data-id="' . $row->id . '" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser"><i class="ti ti-edit"></i></button>'
                    : '';
                
                $deleteBtn = $canDelete 
                    ? '<button class="btn btn-sm btn-icon delete-record btn-text-secondary rounded-pill waves-effect" data-id="' . $row->id . '"><i class="ti ti-trash"></i></button>'
                    : '';
                
                return '<div class="d-flex align-items-center gap-50">' . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['name', 'email', 'roles', 'is_active', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-users');
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $currentUser = auth()->user()->loadMissing('roles');
        $assignableRoleIds = $this->getAssignableRoleIdsForUser($currentUser);

        if ($request->has('roles') && is_array($request->roles)) {
            $requestedRoleIds = array_map('intval', $request->roles);
            $invalidRoleIds = array_diff($requestedRoleIds, $assignableRoleIds);

            if (!empty($invalidRoleIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to assign one or more of the selected roles.',
                ], 403);
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        // Assign roles
        if ($request->has('roles') && is_array($request->roles)) {
            $roles = Role::whereIn('id', $request->roles)->get();
            $user->syncRoles($roles);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->authorize('edit-users');
        
        $user = User::with('roles')->findOrFail($id);

        $currentUser = auth()->user()->loadMissing('roles');
        if (!$this->canManageUser($currentUser, $user)) {
            abort(403, 'You are not allowed to manage users with a higher role than yours.');
        }

        $user->role_ids = $user->roles->pluck('id')->toArray();
        
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('edit-users');
        
        $user = User::with('roles')->findOrFail($id);

        $currentUser = auth()->user()->loadMissing('roles');
        if (!$this->canManageUser($currentUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to manage users with a higher role than yours.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $assignableRoleIds = $this->getAssignableRoleIdsForUser($currentUser);

        if ($request->has('roles') && is_array($request->roles)) {
            $requestedRoleIds = array_map('intval', $request->roles);
            $invalidRoleIds = array_diff($requestedRoleIds, $assignableRoleIds);

            if (!empty($invalidRoleIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not allowed to assign one or more of the selected roles.',
                ], 403);
            }
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->has('is_active')) {
            $data['is_active'] = (bool)$request->is_active;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sync roles
        if ($request->has('roles') && is_array($request->roles)) {
            $roles = Role::whereIn('id', $request->roles)->get();
            $user->syncRoles($roles);
        } else {
            $user->syncRoles([]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorize('delete-users');
        
        $user = User::findOrFail($id);

        $currentUser = auth()->user()->loadMissing('roles');
        $user->loadMissing('roles');

        if (!$this->canManageUser($currentUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to delete users with a higher role than yours.'
            ], 403);
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.'
        ]);
    }

    /**
     * Toggle user status (active/inactive).
     */
    public function toggleStatus(Request $request, $id)
    {
        $this->authorize('edit-users');
        
        $user = User::findOrFail($id);

        $currentUser = auth()->user()->loadMissing('roles');
        if (!$this->canManageUser($currentUser, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to manage this user.'
            ], 403);
        }

        // Prevent toggling yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own status.'
            ], 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.',
            'is_active' => $user->is_active
        ]);
    }

    /**
     * Determine if the given acting user can manage the target user
     * based on role hierarchy.
     */
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

    /**
     * Get the "depth" of the highest role for the given user.
     * Lower depth means higher in the hierarchy.
     */
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

    /**
     * Compute depth of a role name within the hierarchy.
     * Roles without a parent are considered top-level (depth 0).
     */
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

    /**
     * Return the list of roles the given user is allowed to assign.
     */
    private function getAssignableRolesForUser(User $user)
    {
        $allRoles = Role::all();
        $actingDepth = $this->getUserHighestRoleDepth($user);

        if ($actingDepth === null) {
            return collect([]);
        }

        $reportsToConfig = config('reporting.reports_to_role', []);

        return $allRoles->filter(function (Role $role) use ($actingDepth, $reportsToConfig) {
            $roleDepth = $this->getRoleDepth($role->name, $reportsToConfig);

            return $actingDepth <= $roleDepth;
        });
    }

    /**
     * Get assignable role IDs for the given user.
     */
    private function getAssignableRoleIdsForUser(User $user): array
    {
        return $this->getAssignableRolesForUser($user)->pluck('id')->map('intval')->toArray();
    }
}
