<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Services\UserManagement\UserManagementService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct(private readonly UserManagementService $userManagementService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-users');
        $currentUser = auth()->user();
        $roles = $this->userManagementService->getAssignableRolesForUser($currentUser);

        return view('content.user-management.users.index', compact('roles'));
    }

    /**
     * Get data for DataTable (server-side processing)
     */
    public function getData(Request $request)
    {
        $this->authorize('view-users');
        
        $query = $this->userManagementService->getUserListingQuery();
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
            ->editColumn('username', function ($row) {
                return '<span class="text-muted">' . $row->username . '</span>';
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
                    ? '<button class="btn btn-sm btn-icon edit-record btn-text-secondary rounded-pill waves-effect" data-id="' . $row->id . '"><i class="ti ti-edit"></i></button>'
                    : '';
                
                $deleteBtn = $canDelete 
                    ? '<button class="btn btn-sm btn-icon delete-record btn-text-secondary rounded-pill waves-effect" data-id="' . $row->id . '"><i class="ti ti-trash"></i></button>'
                    : '';
                
                return '<div class="d-flex align-items-center gap-50">' . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['name', 'username', 'roles', 'is_active', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-users');
        
        try {
            $this->userManagementService->createUser(auth()->user(), $request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
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
        $this->userManagementService->ensureCanManageUser(auth()->user(), $user);

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
        
        try {
            $this->userManagementService->updateUser(auth()->user(), $user, $request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
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
        
        $user = User::with('roles')->findOrFail($id);
        
        try {
            $this->userManagementService->deleteUser(auth()->user(), $user);
        } catch (\DomainException $e) {
            $status = $e->getMessage() === 'You cannot delete your own account.' ? 422 : 403;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }

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
        
        $user = User::with('roles')->findOrFail($id);
        
        try {
            $user = $this->userManagementService->toggleStatus(auth()->user(), $user);
        } catch (\DomainException $e) {
            $status = $e->getMessage() === 'You cannot change your own status.' ? 422 : 403;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.',
            'is_active' => $user->is_active
        ]);
    }

}
