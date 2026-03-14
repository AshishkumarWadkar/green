@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Login - Green Solar Energy')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

@section('page-style')
<style>
  :root {
    --primary-green: #28c76f;
    --hover-green: #21a35c;
    --dark-text: #2f2b3d;
    --muted-text: #6f6b7d;
  }

  .auth-page-wrapper {
    display: flex;
    min-height: 100vh;
    width: 100%;
    background-color: #f8f7fa;
    position: relative;
    overflow: hidden;
  }

  /* Left Side: Image Content */
  .auth-side-content {
    display: none;
    flex: 1;
    background: url('/assets/img/branding/login-bg.png') no-repeat center center;
    background-size: cover;
    position: relative;
  }

  .auth-side-content::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.45) 0%, rgba(15, 20, 18, 0.75) 100%);
  }

  .auth-side-content .brand-overlay {
    position: absolute;
    bottom: 80px;
    left: 80px;
    z-index: 2;
    color: #fff;
    max-width: 450px;
  }

  .brand-overlay h2 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
    color: #fff;
  }

  .brand-overlay p {
    font-size: 1.1rem;
    line-height: 1.6;
    opacity: 0.9;
  }

  /* Right Side: Login Form */
  .auth-form-side {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    background: #fff;
    padding: 3rem;
  }

  @media (min-width: 992px) {
    .auth-side-content {
      display: flex;
    }
    .auth-form-side {
      width: 450px;
      min-width: 450px;
    }
  }

  @media (min-width: 1200px) {
    .auth-form-side {
      width: 500px;
      min-width: 500px;
    }
  }

  .app-brand-logo img {
    width: 50px;
    height: 50px;
    object-fit: contain;
  }

  .auth-header h3 {
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 0.5rem;
  }

  .auth-header p {
    color: var(--muted-text);
  }

  .form-label {
    font-size: 0.8125rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--dark-text);
    margin-bottom: 0.5rem;
  }

  .form-control {
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    border-radius: 0.5rem;
    border: 1px solid #dbdade;
  }

  .form-control:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.15rem rgba(40, 199, 111, 0.12);
  }

  .btn-login {
    background-color: var(--primary-green) !important;
    border-color: var(--primary-green) !important;
    padding: 0.75rem;
    font-weight: 700;
    border-radius: 0.5rem;
    transition: all 0.2s ease-in-out;
  }

  .btn-login:hover {
    background-color: var(--hover-green) !important;
    border-color: var(--hover-green) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 199, 111, 0.3);
  }

  .text-primary {
    color: var(--primary-green) !important;
    font-weight: 600;
  }

  .form-check-input:checked {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
  }

  .input-group-text {
    border-color: #dbdade;
    background-color: #f8f7fa;
  }
</style>
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
  <!-- Left Section (Image) -->
  <div class="auth-side-content">
    <div class="brand-overlay">
      <h2>Green Solar Energy</h2>
      <p>Powering your future with sustainable energy solutions. Management portal for seamless operations and real-time tracking.</p>
    </div>
  </div>

  <!-- Right Section (Form) -->
  <div class="auth-form-side">
    <div class="w-100" style="max-width: 400px;">
      <!-- Logo -->
      <div class="app-brand mb-10">
        <a href="{{url('/')}}" class="app-brand-link gap-2">
          <span class="app-brand-logo">
            <img src="{{ asset('assets/img/branding/logo.png') }}" alt="Logo">
          </span>
          <span class="app-brand-text demo text-heading fw-bold fs-3" style="text-transform: none;">Green Solar</span>
        </a>
      </div>
      <!-- /Logo -->
      
      <div class="auth-header mb-8">
        <h3>Welcome Back!</h3>
        <p>Enter your credentials to access your dashboard.</p>
      </div>

      <form id="formAuthentication" class="mb-4" action="{{ route('login.post') }}" method="POST">
        @csrf
        <div class="mb-6">
          <label for="email" class="form-label">Email or Username</label>
          <input type="text" class="form-control @error('email-username') is-invalid @enderror" id="email" name="email-username" value="{{ old('email-username') }}" placeholder="Enter your email" autofocus>
          @error('email-username')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        <div class="mb-6 form-password-toggle">
          <div class="d-flex justify-content-between">
            <label class="form-label" for="password">Password</label>
            <a href="javascript:void(0);" class="text-primary small">
              Forgot password?
            </a>
          </div>
          <div class="input-group input-group-merge">
            <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
            <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
            @error('password')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        
        <div class="mb-8">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember-me" name="remember-me">
            <label class="form-check-label" for="remember-me">
              Keep me logged in
            </label>
          </div>
        </div>
        
        <div class="mb-0">
          <button class="btn btn-login btn-primary d-grid w-100 text-white" type="submit">Sign In</button>
        </div>
      </form>

      <div class="text-center mt-8">
        <p class="mb-0">
          <span class="text-muted">Having trouble logging in?</span>
          <br>
          <a href="{{ config('variables.creatorUrl') }}" target="_blank" class="text-primary">
            Contact System Administrator
          </a>
        </p>
      </div>
    </div>
  </div>
</div>
@endsection
