<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('manage-roles');
        
        $permissions = Permission::all()->groupBy(function ($permission) {
            // Group permissions by category
            $name = $permission->name;

            if (str_contains($name, 'dashboard')) {
                return 'dashboard';
            } elseif (str_contains($name, 'enquir')) {
                return 'enquiries';
            } elseif (
                str_contains($name, 'master-data') ||
                str_contains($name, 'sources') ||
                str_contains($name, 'areas') ||
                str_contains($name, 'statuses') ||
                str_contains($name, 'follow-up-results') ||
                str_contains($name, 'settings')
            ) {
                // Check master-data related permissions before generic follow-up checks
                return 'master-data';
            } elseif (str_contains($name, 'follow-up')) {
                return 'follow-ups';
            } elseif (str_contains($name, 'report')) {
                return 'reports';
            } elseif (str_contains($name, 'user') || str_contains($name, 'role')) {
                return 'user-management';
            } else {
                return 'other';
            }
        });
        
        return view('content.user-management.roles.index', compact('permissions'));
    }

    /**
     * Get data for DataTable (server-side processing)
     */
    public function getData(Request $request)
    {
        $this->authorize('manage-roles');
        
        $query = Role::with(['permissions', 'users']);
        $start = $request->get('start', 0);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('fake_id', function ($row) use ($start) {
                static $index = 0;
                $index++;
                return $start + $index;
            })
            ->editColumn('name', function ($row) {
                return '<span class="fw-medium">' . $row->name . '</span>';
            })
            ->addColumn('permissions_count', function ($row) {
                $count = $row->permissions->count();
                return '<span class="badge bg-label-info">' . $count . ' Permissions</span>';
            })
            ->addColumn('users_count', function ($row) {
                $count = $row->users->count();
                return '<span class="badge bg-label-secondary">' . $count . ' Users</span>';
            })
            ->addColumn('action', function ($row) {
                return '<div class="d-flex align-items-center gap-50">' .
                    '<button class="btn btn-sm btn-icon edit-record btn-text-secondary rounded-pill waves-effect" data-id="' . $row->id . '" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddRole"><i class="ti ti-edit"></i></button>' .
                    '<button class="btn btn-sm btn-icon delete-record btn-text-secondary rounded-pill waves-effect" data-id="' . $row->id . '"><i class="ti ti-trash"></i></button>' .
                    '</div>';
            })
            ->rawColumns(['name', 'permissions_count', 'users_count', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('manage-roles');
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        // Assign permissions
        if ($request->has('permissions') && is_array($request->permissions)) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->authorize('manage-roles');
        
        $role = Role::with('permissions')->findOrFail($id);
        $role->permission_ids = $role->permissions->pluck('id')->toArray();
        
        return response()->json($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('manage-roles');
        
        $role = Role::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $role->update([
            'name' => $request->name,
        ]);

        // Sync permissions
        if ($request->has('permissions') && is_array($request->permissions)) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorize('manage-roles');
        
        $role = Role::findOrFail($id);
        
        // Prevent deleting roles that have users
        if ($role->users->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role. There are users assigned to this role.'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.'
        ]);
    }
}
