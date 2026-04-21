<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserManagement\PasswordResetRequestResource;
use App\Models\PasswordResetRequest;
use App\Services\PasswordReset\PasswordResetRequestFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthForgotPasswordController extends Controller
{
    public function __construct(private readonly PasswordResetRequestFlowService $flowService)
    {
    }

    /**
     * Check status or create / resubmit a password reset request.
     */
    public function lookup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255'],
            'resubmit' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $username = $validator->validated()['username'];
        $resubmit = $request->boolean('resubmit');

        $result = $this->flowService->processLookup($username, $resubmit);

        return match ($result['outcome']) {
            PasswordResetRequestFlowService::OUTCOME_INVALID_USERNAME => response()->json([
                'message' => 'Invalid username',
                'outcome' => 'invalid_username',
            ], 422),
            PasswordResetRequestFlowService::OUTCOME_RESUBMIT_INVALID => response()->json([
                'message' => 'There is no declined request to resubmit for this account.',
                'outcome' => 'resubmit_invalid',
            ], 422),
            PasswordResetRequestFlowService::OUTCOME_SUBMITTED => response()->json([
                'message' => 'Your request has been submitted. Please contact the approver.',
                'outcome' => 'submitted',
                'data' => isset($result['request']) ? new PasswordResetRequestResource($result['request']) : null,
            ]),
            PasswordResetRequestFlowService::OUTCOME_PENDING => response()->json([
                'message' => 'Your request is pending approval. Please contact the approver.',
                'outcome' => 'pending',
                'data' => new PasswordResetRequestResource($result['request']),
            ]),
            PasswordResetRequestFlowService::OUTCOME_DECLINED => response()->json([
                'message' => 'Your request was declined. You may submit a new request.',
                'outcome' => 'declined',
                'can_resubmit' => true,
                'data' => new PasswordResetRequestResource($result['request']),
            ]),
            PasswordResetRequestFlowService::OUTCOME_APPROVED => $this->approvedLookupResponse($result['request']),
            default => response()->json(['message' => 'Unexpected state.'], 500),
        };
    }

    /**
     * Set a new password using a reset_token returned from lookup when outcome is approved.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reset_token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $plainToken = $validator->validated()['reset_token'];
        $hash = hash('sha256', $plainToken);

        $resetRequest = PasswordResetRequest::query()
            ->where('reset_token_hash', $hash)
            ->where('status', PasswordResetRequest::STATUS_APPROVED)
            ->first();

        if (!$resetRequest || !$resetRequest->reset_token_expires_at || $resetRequest->reset_token_expires_at->isPast()) {
            return response()->json([
                'message' => 'Invalid or expired reset token. Request a new token from Forgot Password with your username.',
                'outcome' => 'invalid_token',
            ], 422);
        }

        $user = $resetRequest->user;
        $user->password = $validator->validated()['password'];
        $user->save();

        $resetRequest->update([
            'status' => PasswordResetRequest::STATUS_COMPLETED,
            'reset_token_hash' => null,
            'reset_token_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Your password has been updated. You may sign in.',
            'outcome' => 'password_updated',
        ]);
    }

    private function approvedLookupResponse(PasswordResetRequest $resetRequest): JsonResponse
    {
        $plainToken = $resetRequest->issueApiResetToken(60);

        $resetRequest->refresh();

        return response()->json([
            'message' => 'Your request is approved. Use reset_token with the reset endpoint to set a new password.',
            'outcome' => 'approved',
            'reset_token' => $plainToken,
            'reset_token_expires_at' => $resetRequest->reset_token_expires_at?->toIso8601String(),
            'data' => new PasswordResetRequestResource($resetRequest),
        ]);
    }
}
