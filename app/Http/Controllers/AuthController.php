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
      'username' => 'required|string',
      'password' => 'required|string',
    ], [
      'username.required' => 'Username is required.',
      'password.required' => 'Password is required.',
    ]);

    if ($validator->fails()) {
      return back()
        ->withErrors($validator)
        ->withInput($request->only('username'));
    }

    $credentials = $request->only('username', 'password');
    $remember = $request->has('remember-me');

    $user = \App\Models\User::where('username', $credentials['username'])->first();

    if (!$user) {
      return back()
        ->withErrors(['username' => 'These credentials do not match our records.'])
        ->withInput($request->only('username'));
    }

    if (Auth::attempt(['username' => $user->username, 'password' => $credentials['password']], $remember)) {
      $request->session()->regenerate();

      return redirect()->intended(route('pages-home'));
    }

    return back()
      ->withErrors(['password' => 'The provided password is incorrect.'])
      ->withInput($request->only('username'));
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
