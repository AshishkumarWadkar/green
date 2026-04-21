@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Password Reset Requests')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">Password Reset Requests</h5>
    <p class="card-text text-muted small mb-0 mt-1">
      Approve or decline requests submitted from Forgot Password. Users are not notified automatically—they rely on this list and follow-up with approvers.
    </p>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-password-reset-requests table border-top">
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>Name</th>
          <th>Username</th>
          <th>Status</th>
          <th>Requested</th>
          <th>Reviewed</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-password-reset-requests.js'])
@endsection
