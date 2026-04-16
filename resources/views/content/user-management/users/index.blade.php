@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'User Management')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Users</h5>
    <button type="button" class="btn btn-primary add-new" id="openUserModalBtn">
      <i class="ti ti-plus me-1"></i> Add New User
    </button>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>Name</th>
          <th>Username</th>
          <th>Roles</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal to add/edit user -->
<div class="modal fade" id="userFormModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="userFormModalLabel" class="modal-title">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="add-new-user pt-0" id="addUserForm">
          <input type="hidden" id="user_id" name="id">
          <div class="row g-4">
            <div class="col-md-6 mb-6">
              <label for="user_name" class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="user_name" placeholder="Enter user name" name="name" aria-label="User name" />
            </div>
            <div class="col-md-6 mb-6">
              <label for="user_username" class="form-label">Username <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="user_username" placeholder="Enter username" name="username" aria-label="Username" />
            </div>

            <div class="col-md-6 mb-6 form-password-toggle">
              <label for="user_password" class="form-label">Password <span class="text-danger" id="password_required">*</span></label>
              <div class="input-group input-group-merge">
                <input type="password" class="form-control" id="user_password" placeholder="Enter password" name="password" aria-label="Password" />
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
              </div>
              <small class="text-muted" id="password_hint">Leave blank to keep current password</small>
            </div>

            <div class="col-md-6 mb-6 form-password-toggle">
              <label for="user_password_confirmation" class="form-label">Confirm Password <span class="text-danger" id="password_confirmation_required">*</span></label>
              <div class="input-group input-group-merge">
                <input type="password" class="form-control" id="user_password_confirmation" placeholder="Confirm password" name="password_confirmation" aria-label="Confirm Password" />
                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
              </div>
            </div>

            <div class="col-md-8 mb-6">
              <label for="user_roles" class="form-label">Roles</label>
              <select class="select2 form-select" id="user_roles" name="roles[]" multiple>
                @foreach($roles as $role)
                  <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-4 mb-6 d-flex align-items-center">
              <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" id="user_is_active" name="is_active" value="1" checked>
                <label class="form-check-label" for="user_is_active">Active Account</label>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary data-submit" form="addUserForm">Submit</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js',
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-user-management.js'])
@endsection
