<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
  /**
   * Show the login form
   */
  public function showLoginForm()
  {
    // Redirect if already authenticated
    if (Auth::check()) {
      return redirect()->route('pages-home');
    }

    $pageConfigs = ['myLayout' => 'blank'];
    return view('content.authentications.auth-login-basic', ['pageConfigs' => $pageConfigs]);
  }

  /**
   * Handle login request
   */
  public function login(Request $request)
  {
    // Redirect if already authenticated
    if (Auth::check()) {
      return redirect()->route('pages-home');
    }

    // Validate the request
    $validator = Validator::make($request->all(), [
      'email-username' => 'required|string',
      'password' => 'required|string',
    ], [
      'email-username.required' => 'Email or username is required.',
      'password.required' => 'Password is required.',
    ]);

    if ($validator->fails()) {
      return back()
        ->withErrors($validator)
        ->withInput($request->only('email-username'));
    }

    $credentials = $request->only('email-username', 'password');
    $remember = $request->has('remember-me');

    // Try to authenticate using email or username
    // First, try to find user by email
    $user = \App\Models\User::where('email', $credentials['email-username'])->first();

    // If not found by email, try by username (if you have a username field)
    // For now, we'll use email only as the User model uses email
    if (!$user) {
      return back()
        ->withErrors(['email-username' => 'These credentials do not match our records.'])
        ->withInput($request->only('email-username'));
    }

    // Attempt to authenticate
    if (Auth::attempt(['email' => $user->email, 'password' => $credentials['password']], $remember)) {
      $request->session()->regenerate();

      return redirect()->intended(route('pages-home'));
    }

    return back()
      ->withErrors(['password' => 'The provided password is incorrect.'])
      ->withInput($request->only('email-username'));
  }

  /**
   * Handle logout request
   */
  public function logout(Request $request)
  {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
  }
}
