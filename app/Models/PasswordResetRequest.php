<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'username',
        'status',
        'requested_at',
        'reviewed_at',
        'reviewed_by_id',
        'reset_token_hash',
        'reset_token_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'reset_token_expires_at' => 'datetime',
        ];
    }

    /**
     * Issue a new API reset token (plain text returned once). Invalidates any previous token.
     */
    public function issueApiResetToken(int $ttlMinutes = 60): string
    {
        $plain = \Illuminate\Support\Str::password(64);
        $this->forceFill([
            'reset_token_hash' => hash('sha256', $plain),
            'reset_token_expires_at' => now()->addMinutes($ttlMinutes),
        ])->save();

        return $plain;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
