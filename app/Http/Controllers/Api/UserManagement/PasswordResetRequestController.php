<?php

namespace App\Http\Controllers\Api\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserManagement\PasswordResetRequestResource;
use App\Models\PasswordResetRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasswordResetRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage-password-reset-requests');

        $perPage = max(1, min((int) $request->integer('per_page', 15), 100));
        $status = $request->query('status');

        $query = PasswordResetRequest::query()
            ->with(['user', 'reviewedBy'])
            ->orderByDesc('requested_at');

        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        return PasswordResetRequestResource::collection($query->paginate($perPage));
    }

    public function approve(Request $request, PasswordResetRequest $passwordResetRequest): JsonResponse
    {
        $this->authorize('manage-password-reset-requests');

        if ($passwordResetRequest->status !== PasswordResetRequest::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending requests can be approved.',
            ], 422);
        }

        $passwordResetRequest->update([
            'status' => PasswordResetRequest::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by_id' => $request->user()->id,
            'reset_token_hash' => null,
            'reset_token_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Request approved. The user can complete reset from the app (Forgot Password) or web.',
            'data' => new PasswordResetRequestResource($passwordResetRequest->fresh(['user', 'reviewedBy'])),
        ]);
    }

    public function decline(Request $request, PasswordResetRequest $passwordResetRequest): JsonResponse
    {
        $this->authorize('manage-password-reset-requests');

        if ($passwordResetRequest->status !== PasswordResetRequest::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending requests can be declined.',
            ], 422);
        }

        $passwordResetRequest->update([
            'status' => PasswordResetRequest::STATUS_DECLINED,
            'reviewed_at' => now(),
            'reviewed_by_id' => $request->user()->id,
            'reset_token_hash' => null,
            'reset_token_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Request declined.',
            'data' => new PasswordResetRequestResource($passwordResetRequest->fresh(['user', 'reviewedBy'])),
        ]);
    }
}
