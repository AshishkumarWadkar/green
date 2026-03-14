@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Role Management')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-3">Roles & Permissions</h5>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-roles table border-top">
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>Role Name</th>
          <th>Permissions</th>
          <th>Users</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Offcanvas to add/edit role -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddRole" aria-labelledby="offcanvasAddRoleLabel" style="width: 600px;">
  <div class="offcanvas-header">
    <h5 id="offcanvasAddRoleLabel" class="offcanvas-title">Add Role</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0">
    <form class="add-new-role pt-0" id="addRoleForm">
      <input type="hidden" id="role_id" name="id">
      <div class="mb-6">
        <label for="role_name" class="form-label">Role Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="role_name" placeholder="Enter role name" name="name" aria-label="Role name" />
      </div>
      <div class="mb-6">
        <label class="form-label">Permissions</label>
        <div class="permissions-container" style="max-height: 400px; overflow-y: auto; border: 1px solid #d9dee3; border-radius: 0.375rem; padding: 1rem;">
          @foreach($permissions as $group => $groupPermissions)
            <div class="mb-4">
              <h6 class="text-capitalize mb-2">{{ $group }} Permissions</h6>
              @foreach($groupPermissions as $permission)
                <div class="form-check mb-2">
                  <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}">
                  <label class="form-check-label" for="permission_{{ $permission->id }}">
                    {{ str_replace('-', ' ', $permission->name) }}
                  </label>
                </div>
              @endforeach
            </div>
          @endforeach
        </div>
        <div class="mt-2">
          <button type="button" class="btn btn-sm btn-label-secondary" id="selectAllPermissions">Select All</button>
          <button type="button" class="btn btn-sm btn-label-secondary" id="deselectAllPermissions">Deselect All</button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Submit</button>
      <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
    </form>
  </div>
</div>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-role-management.js'])
@endsection
