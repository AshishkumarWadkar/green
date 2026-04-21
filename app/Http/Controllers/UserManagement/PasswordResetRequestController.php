<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PasswordResetRequestController extends Controller
{
    public function index()
    {
        $this->authorize('manage-password-reset-requests');

        return view('content.user-management.password-reset-requests.index');
    }

    public function getData(Request $request)
    {
        $this->authorize('manage-password-reset-requests');

        $query = PasswordResetRequest::query()
            ->with(['user', 'reviewedBy'])
            ->orderByDesc('requested_at');

        $start = $request->get('start', 0);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('fake_id', function ($row) use ($start) {
                static $index = 0;
                $index++;

                return $start + $index;
            })
            ->addColumn('name', function (PasswordResetRequest $row) {
                return $row->user
                    ? '<span class="text-heading fw-medium">' . e($row->user->name) . '</span>'
                    : '<span class="text-muted">—</span>';
            })
            ->editColumn('username', function (PasswordResetRequest $row) {
                return '<span class="text-muted">' . e($row->username) . '</span>';
            })
            ->editColumn('status', function (PasswordResetRequest $row) {
                $map = [
                    PasswordResetRequest::STATUS_PENDING => 'bg-label-warning',
                    PasswordResetRequest::STATUS_APPROVED => 'bg-label-success',
                    PasswordResetRequest::STATUS_DECLINED => 'bg-label-danger',
                    PasswordResetRequest::STATUS_COMPLETED => 'bg-label-secondary',
                ];
                $class = $map[$row->status] ?? 'bg-label-secondary';

                return '<span class="badge ' . $class . '">' . e(ucfirst($row->status)) . '</span>';
            })
            ->editColumn('requested_at', function (PasswordResetRequest $row) {
                return $row->requested_at?->format('Y-m-d H:i') ?? '—';
            })
            ->addColumn('reviewed', function (PasswordResetRequest $row) {
                if (!$row->reviewed_at) {
                    return '<span class="text-muted">—</span>';
                }
                $by = $row->reviewedBy ? e($row->reviewedBy->name) : '—';

                return '<div class="small">' . e($row->reviewed_at->format('Y-m-d H:i')) . '<br><span class="text-muted">' . $by . '</span></div>';
            })
            ->addColumn('action', function (PasswordResetRequest $row) {
                if ($row->status !== PasswordResetRequest::STATUS_PENDING) {
                    return '<span class="text-muted small">—</span>';
                }

                return '<div class="d-flex align-items-center gap-50">' .
                    '<button type="button" class="btn btn-sm btn-success btn-approve" data-id="' . $row->id . '">Approve</button>' .
                    '<button type="button" class="btn btn-sm btn-label-danger btn-decline" data-id="' . $row->id . '">Decline</button>' .
                    '</div>';
            })
            ->rawColumns(['name', 'username', 'status', 'reviewed', 'action'])
            ->make(true);
    }

    public function approve(Request $request, int $id)
    {
        $this->authorize('manage-password-reset-requests');

        $resetRequest = PasswordResetRequest::findOrFail($id);

        if ($resetRequest->status !== PasswordResetRequest::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be approved.',
            ], 422);
        }

        $resetRequest->update([
            'status' => PasswordResetRequest::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by_id' => $request->user()->id,
            'reset_token_hash' => null,
            'reset_token_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request approved. The user can complete reset from Forgot Password.',
        ]);
    }

    public function decline(Request $request, int $id)
    {
        $this->authorize('manage-password-reset-requests');

        $resetRequest = PasswordResetRequest::findOrFail($id);

        if ($resetRequest->status !== PasswordResetRequest::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be declined.',
            ], 422);
        }

        $resetRequest->update([
            'status' => PasswordResetRequest::STATUS_DECLINED,
            'reviewed_at' => now(),
            'reviewed_by_id' => $request->user()->id,
            'reset_token_hash' => null,
            'reset_token_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request declined.',
        ]);
    }
}
