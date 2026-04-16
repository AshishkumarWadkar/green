<?php

namespace App\Http\Controllers\Api\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserManagement\UserResource;
use App\Models\User;
use App\Services\UserManagement\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(private readonly UserManagementService $userManagementService)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('view-users');

        $perPage = max(1, min((int) $request->integer('per_page', 15), 100));
        $search = trim((string) $request->query('search', ''));
        $query = $this->userManagementService->getUserListingQuery($search);

        return UserResource::collection($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create-users');

        try {
            $user = $this->userManagementService->createUser($request->user(), $request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json([
            'message' => 'User created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view-users');

        try {
            $this->userManagementService->ensureCanManageUser(auth()->user(), $user);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        $user->load('roles');

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('edit-users');

        try {
            $user = $this->userManagementService->updateUser($request->user(), $user, $request->all());
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete-users');

        try {
            $this->userManagementService->deleteUser($request->user(), $user);
        } catch (\DomainException $e) {
            $status = $e->getMessage() === 'You cannot delete your own account.' ? 422 : 403;
            return response()->json([
                'message' => $e->getMessage(),
            ], $status);
        }

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        $this->authorize('edit-users');

        try {
            $user = $this->userManagementService->toggleStatus($request->user(), $user);
        } catch (\DomainException $e) {
            $status = $e->getMessage() === 'You cannot change your own status.' ? 422 : 403;
            return response()->json([
                'message' => $e->getMessage(),
            ], $status);
        }

        return response()->json([
            'message' => 'User status updated successfully.',
            'data' => new UserResource($user),
        ]);
    }

    public function assignableRoles(Request $request): JsonResponse
    {
        $this->authorize('view-users');

        $roles = $this->userManagementService->getAssignableRolesForUser($request->user())
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])->values();

        return response()->json([
            'data' => $roles,
        ]);
    }
}
