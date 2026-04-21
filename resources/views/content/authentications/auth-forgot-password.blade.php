@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Forgot Password - Green Solar Energy')

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
      <p>Password resets require approval. Submit your username, then follow up with your approver.</p>
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
        <h3>Forgot Password</h3>
        <p>Enter your username to check status or submit a new request.</p>
      </div>

      @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">{{ session('status') }}</div>
      @endif

      @if (session('info'))
        <div class="alert alert-warning mb-4" role="alert">{{ session('info') }}</div>
      @endif

      @if (session('declined'))
        <div class="alert alert-danger mb-4" role="alert">
          Your request was declined. You may submit a new request.
        </div>
        <form class="mb-4" action="{{ route('forgot-password.lookup') }}" method="POST">
          @csrf
          <input type="hidden" name="username" value="{{ old('username') }}">
          <input type="hidden" name="resubmit" value="1">
          <button type="submit" class="btn btn-login btn-primary w-100 text-white">Submit a new request</button>
        </form>
      @endif

      @if (!session('declined'))
        <form class="mb-4" action="{{ route('forgot-password.lookup') }}" method="POST">
          @csrf
          <div class="mb-6">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" placeholder="Enter your username" autofocus required>
            @error('username')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <button class="btn btn-login btn-primary d-grid w-100 text-white" type="submit">Continue</button>
        </form>
      @endif

      <div class="text-center mt-8">
        <a href="{{ route('login') }}" class="text-primary">Back to sign in</a>
      </div>
    </div>
  </div>
</div>
@endsection
