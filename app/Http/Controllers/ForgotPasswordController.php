<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use App\Services\PasswordReset\PasswordResetRequestFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(private readonly PasswordResetRequestFlowService $flowService)
    {
    }

    public function showForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('pages-home');
        }

        $pageConfigs = ['myLayout' => 'blank'];

        return view('content.authentications.auth-forgot-password', compact('pageConfigs'));
    }

    public function lookupUsername(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('pages-home');
        }

        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'resubmit' => 'sometimes|boolean',
        ]);

        $result = $this->flowService->processLookup($validated['username'], $request->boolean('resubmit'));

        return match ($result['outcome']) {
            PasswordResetRequestFlowService::OUTCOME_INVALID_USERNAME => back()
                ->withErrors(['username' => 'Invalid username'])
                ->withInput($request->only('username')),
            PasswordResetRequestFlowService::OUTCOME_RESUBMIT_INVALID => back()
                ->withErrors(['username' => 'There is no declined request to resubmit for this account.'])
                ->withInput($request->only('username')),
            PasswordResetRequestFlowService::OUTCOME_SUBMITTED => back()
                ->with('status', 'Your request has been submitted. Please contact the approver.')
                ->withInput($request->only('username')),
            PasswordResetRequestFlowService::OUTCOME_PENDING => back()
                ->with('info', 'Your request is pending approval. Please contact the approver.')
                ->withInput($request->only('username')),
            PasswordResetRequestFlowService::OUTCOME_APPROVED => tap(
                redirect()->route('password-reset.show'),
                function () use ($request, $result) {
                    $request->session()->put('password_reset_request_id', $result['request']->id);
                }
            ),
            PasswordResetRequestFlowService::OUTCOME_DECLINED => back()
                ->with('declined', true)
                ->withInput($request->only('username')),
            default => back()->withInput($request->only('username')),
        };
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('pages-home');
        }

        $id = $request->session()->get('password_reset_request_id');
        if (!$id) {
            return redirect()
                ->route('forgot-password')
                ->withErrors(['username' => 'Please start again from Forgot Password and enter your username.']);
        }

        $resetRequest = PasswordResetRequest::where('id', $id)
            ->where('status', PasswordResetRequest::STATUS_APPROVED)
            ->first();

        if (!$resetRequest) {
            $request->session()->forget('password_reset_request_id');

            return redirect()
                ->route('forgot-password')
                ->withErrors(['username' => 'This reset session is no longer valid. Please enter your username again.']);
        }

        $pageConfigs = ['myLayout' => 'blank'];

        return view('content.authentications.auth-reset-password', [
            'pageConfigs' => $pageConfigs,
            'resetRequest' => $resetRequest,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('pages-home');
        }

        $id = $request->session()->get('password_reset_request_id');
        if (!$id) {
            return redirect()
                ->route('forgot-password')
                ->withErrors(['username' => 'Please start again from Forgot Password.']);
        }

        $resetRequest = PasswordResetRequest::where('id', $id)
            ->where('status', PasswordResetRequest::STATUS_APPROVED)
            ->first();

        if (!$resetRequest) {
            $request->session()->forget('password_reset_request_id');

            return redirect()
                ->route('forgot-password')
                ->withErrors(['username' => 'This reset session is no longer valid.']);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $resetRequest->user;
        $user->password = $validated['password'];
        $user->save();

        $resetRequest->update([
            'status' => PasswordResetRequest::STATUS_COMPLETED,
            'reset_token_hash' => null,
            'reset_token_expires_at' => null,
        ]);

        $request->session()->forget('password_reset_request_id');
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Your password has been updated. You may sign in.');
    }
}
