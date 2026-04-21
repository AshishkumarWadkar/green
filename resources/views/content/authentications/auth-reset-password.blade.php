@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Set New Password - Green Solar Energy')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

@section('page-style')
@include('content.authentications.partials.auth-branded-styles')
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/pages-auth.js'
])
@endsection

@section('content')
<div class="auth-page-wrapper">
  <div class="auth-side-content">
    <div class="brand-overlay">
      <h2>Green Solar Energy</h2>
      <p>Your reset request was approved. Choose a strong password for your account.</p>
    </div>
  </div>

  <div class="auth-form-side">
    <div class="w-100" style="max-width: 400px;">
      <div class="app-brand mb-10">
        <a href="{{ url('/') }}" class="app-brand-link gap-2">
          <span class="app-brand-logo">
            <img src="{{ asset('assets/img/branding/logo.png') }}" alt="Logo">
          </span>
          <span class="app-brand-text demo text-heading fw-bold fs-3" style="text-transform: none;">Green Solar</span>
        </a>
      </div>

      <div class="auth-header mb-8">
        <h3>Set new password</h3>
        <p>Account: <strong>{{ $resetRequest->username }}</strong></p>
      </div>

      <form class="mb-4" action="{{ route('password-reset.update') }}" method="POST">
        @csrf
        <div class="mb-6 form-password-toggle">
          <label class="form-label" for="password">New password</label>
          <div class="input-group input-group-merge">
            <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" />
            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="mb-6 form-password-toggle">
          <label class="form-label" for="password_confirmation">Confirm password</label>
          <div class="input-group input-group-merge">
            <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" required autocomplete="new-password" />
            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
          </div>
        </div>
        <button class="btn btn-login btn-primary d-grid w-100 text-white" type="submit">Save password</button>
      </form>

      <div class="text-center mt-8">
        <a href="{{ route('login') }}" class="text-primary">Back to sign in</a>
      </div>
    </div>
  </div>
</div>
@endsection
