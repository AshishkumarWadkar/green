@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', $viewStatus === 'cancelled' ? 'Cancelled Enquiries' : 'Enquiries')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('content')
<div class="card mb-4">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">{{ $viewStatus === 'cancelled' ? 'Cancelled Enquiries' : 'All Enquiries' }}</h5>
    <div class="d-flex align-items-center gap-2">
      @can('create-enquiries')
      <a href="{{ route('enquiries.create') }}" class="btn btn-primary btn-sm">
        <i class="ti ti-plus me-1"></i> Add New Enquiry
      </a>
      @endcan
      <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="false" aria-controls="collapseFilter">
        <i class="ti ti-filter me-1"></i> Filters <i class="ti ti-chevron-down ms-1"></i>
      </button>
    </div>
  </div>
  <div class="collapse" id="collapseFilter">
    <div class="card-body">
      <!-- Filters -->
      <form id="filterForm" class="row g-3">
        <div class="col-md-2">
          <label class="form-label">Date From</label>
          <input type="text" class="form-control flatpickr" id="filter_date_from" name="date_from" placeholder="Select date">
        </div>
        <div class="col-md-2">
          <label class="form-label">Date To</label>
          <input type="text" class="form-control flatpickr" id="filter_date_to" name="date_to" placeholder="Select date">
        </div>
        <div class="col-md-2">
          <label class="form-label">Source</label>
          <select class="form-select select2" id="filter_source" name="source_id">
            <option value="">All Sources</option>
            @foreach($sources as $source)
              <option value="{{ $source->id }}">{{ $source->name }}</option>
            @endforeach
          </select>
        </div>
        @unless(auth()->user()->hasRole('Sales'))
        <div class="col-md-2">
          <label class="form-label">Assigned To</label>
          <select class="form-select select2" id="filter_assigned" name="assigned_to">
            <option value="">All Users</option>
            @foreach($users as $user)
              <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
          </select>
        </div>
        @endunless
        <div class="col-md-2">
          <label class="form-label">Lead Type</label>
          <select class="form-select select2" id="filter_lead_type" name="lead_type">
            <option value="">All Types</option>
            @foreach($leadTypes as $lt)
              <option value="{{ $lt->id }}">{{ $lt->name }}</option>
            @endforeach
          </select>
        </div>
        @if($viewStatus !== 'cancelled')
        <div class="col-md-2">
          <label class="form-label">Status</label>
          <select class="form-select select2" id="filter_status" name="status">
            <option value="">All Statuses</option>
            <option value="Pending">Pending</option>
            <option value="Accepted">Accepted</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
        @endif
        <div class="col-md-12">
          <input type="hidden" name="view" id="view_status" value="{{ $viewStatus }}">
          <button type="button" class="btn btn-primary" id="applyFilters">
            <i class="ti ti-filter me-1"></i> Apply Filters
          </button>
          <button type="button" class="btn btn-label-secondary" id="resetFilters">
            <i class="ti ti-refresh me-1"></i> Reset
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-datatable table-responsive">
    <table class="datatables-enquiries table border-top">
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>Date</th>
          <th>Customer</th>
          <th>Mobile</th>
          <th>Source</th>
          <th>Assigned To</th>
          <th>Created By</th>
          <th>Lead Type</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Offcanvas to edit enquiry -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditEnquiry" aria-labelledby="offcanvasEditEnquiryLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasEditEnquiryLabel" class="offcanvas-title">Edit Enquiry</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0">
    <form class="edit-enquiry pt-0" id="editEnquiryForm">
      <input type="hidden" id="enquiry_id" name="id">
      <div class="mb-3">
        <label for="enquiry_date" class="form-label">Enquiry Date <span class="text-danger">*</span></label>
        <input type="text" class="form-control flatpickr" id="enquiry_date" name="enquiry_date" placeholder="Select date" />
      </div>
      <div class="mb-3">
        <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Enter customer name" />
      </div>
      <div class="mb-3">
        <label for="mobile_number" class="form-label">Mobile Number <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="mobile_number" name="mobile_number" placeholder="Enter mobile number" />
      </div>
      <div class="mb-3">
        <label for="alternate_mobile" class="form-label">Alternate Mobile</label>
        <input type="text" class="form-control" id="alternate_mobile" name="alternate_mobile" placeholder="Enter alternate mobile" />
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" />
      </div>
      <div class="mb-3">
        <label for="enquiry_source_id" class="form-label">Source <span class="text-danger">*</span></label>
        <select class="form-select select2" id="enquiry_source_id" name="enquiry_source_id">
          <option value="">Select source</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="product_service" class="form-label">Product / Service</label>
        <input type="text" class="form-control" id="product_service" name="product_service" placeholder="Enter product/service" />
      </div>
      <div class="mb-3">
        <label for="assigned_to_readonly" class="form-label">Assigned To <span class="text-danger">*</span></label>
        @if(auth()->user()->hasRole('Sales'))
        <input type="text" class="form-control" id="assigned_to_readonly" value="{{ auth()->user()->name }}" readonly>
        <input type="hidden" name="assigned_to" id="assigned_to" value="{{ auth()->id() }}">
        @else
        <select class="form-select select2" id="assigned_to" name="assigned_to">
          <option value="">Select user</option>
        </select>
        @endif
      </div>
      <div class="mb-3">
        <label for="lead_type" class="form-label">Lead Type <span class="text-danger">*</span></label>
        <select class="form-select select2" id="lead_type" name="lead_type">
          <option value="">Select lead type</option>
          <option value="Hot">Hot</option>
          <option value="Cold">Cold</option>
          <option value="Warm">Warm</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select select2" id="status" name="status">
          <option value="Pending">Pending</option>
          <option value="Accepted">Accepted</option>
          <option value="Cancelled">Cancelled</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="initial_remark" class="form-label">Initial Remark</label>
        <textarea class="form-control" id="initial_remark" name="initial_remark" rows="3" placeholder="Enter remark"></textarea>
      </div>
      <button type="submit" class="btn btn-primary me-sm-3 me-1 data-submit">Update</button>
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
  'resources/assets/vendor/libs/@form-validation/auto-focus.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/cleavejs/cleave.js',
  'resources/assets/vendor/libs/cleavejs/cleave-phone.js'
])
@endsection

@section('page-script')
<script>
  window.enquiryConfig = {
    sources: @json($sources),
    users: @json($users),
    leadTypes: @json($leadTypes),
    viewStatus: '{{ $viewStatus }}'
  };
</script>
@vite(['resources/assets/js/app-enquiries.js'])
@endsection
