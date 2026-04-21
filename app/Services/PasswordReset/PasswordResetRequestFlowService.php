<?php

namespace App\Services\PasswordReset;

use App\Models\PasswordResetRequest;
use App\Models\User;

class PasswordResetRequestFlowService
{
    public const OUTCOME_INVALID_USERNAME = 'invalid_username';

    public const OUTCOME_RESUBMIT_INVALID = 'resubmit_invalid';

    public const OUTCOME_SUBMITTED = 'submitted';

    public const OUTCOME_PENDING = 'pending';

    public const OUTCOME_APPROVED = 'approved';

    public const OUTCOME_DECLINED = 'declined';

    /**
     * Shared forgot-password username lookup (web + API).
     *
     * @return array{outcome: string, user?: User, request?: ?PasswordResetRequest}
     */
    public function processLookup(string $username, bool $resubmit): array
    {
        $username = trim($username);
        $user = User::where('username', $username)->first();

        if (!$user) {
            return ['outcome' => self::OUTCOME_INVALID_USERNAME];
        }

        $existing = PasswordResetRequest::where('user_id', $user->id)->first();

        if ($resubmit) {
            if (!$existing || $existing->status !== PasswordResetRequest::STATUS_DECLINED) {
                return [
                    'outcome' => self::OUTCOME_RESUBMIT_INVALID,
                    'user' => $user,
                    'request' => $existing,
                ];
            }

            $existing->update([
                'username' => $user->username,
                'status' => PasswordResetRequest::STATUS_PENDING,
                'requested_at' => now(),
                'reviewed_at' => null,
                'reviewed_by_id' => null,
                'reset_token_hash' => null,
                'reset_token_expires_at' => null,
            ]);

            return [
                'outcome' => self::OUTCOME_SUBMITTED,
                'user' => $user,
                'request' => $existing->fresh(),
            ];
        }

        if ($existing) {
            if ($existing->status === PasswordResetRequest::STATUS_PENDING) {
                return [
                    'outcome' => self::OUTCOME_PENDING,
                    'user' => $user,
                    'request' => $existing,
                ];
            }

            if ($existing->status === PasswordResetRequest::STATUS_APPROVED) {
                return [
                    'outcome' => self::OUTCOME_APPROVED,
                    'user' => $user,
                    'request' => $existing,
                ];
            }

            if ($existing->status === PasswordResetRequest::STATUS_DECLINED) {
                return [
                    'outcome' => self::OUTCOME_DECLINED,
                    'user' => $user,
                    'request' => $existing,
                ];
            }
        }

        $resetRequest = PasswordResetRequest::updateOrCreate(
            ['user_id' => $user->id],
            [
                'username' => $user->username,
                'status' => PasswordResetRequest::STATUS_PENDING,
                'requested_at' => now(),
                'reviewed_at' => null,
                'reviewed_by_id' => null,
                'reset_token_hash' => null,
                'reset_token_expires_at' => null,
            ]
        );

        return [
            'outcome' => self::OUTCOME_SUBMITTED,
            'user' => $user,
            'request' => $resetRequest,
        ];
    }
}
