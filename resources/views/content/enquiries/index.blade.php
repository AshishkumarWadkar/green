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
      <button type="button" class="btn btn-primary btn-sm" id="openAddEnquiryModal">
        <i class="ti ti-plus me-1"></i> Add New Enquiry
      </button>
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
        <div class="col-md-2">
          <label class="form-label">Location</label>
          <select class="form-select select2" id="filter_location" name="location">
            <option value="">All Locations</option>
            @foreach($locations as $location)
              <option value="{{ $location }}">{{ $location }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Pincode</label>
          <select class="form-select select2" id="filter_pincode" name="pincode">
            <option value="">All Pincodes</option>
            @foreach($pincodes as $pincode)
              <option value="{{ $pincode }}">{{ $pincode }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Enquiry Type</label>
          <select class="form-select select2" id="filter_enquiry_type" name="enquiry_type">
            <option value="">All Types</option>
            <option value="Residential">Residential</option>
            <option value="Industrial">Industrial</option>
            <option value="Commercial">Commercial</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Finance Type</label>
          <select class="form-select select2" id="filter_finance_type" name="finance_type">
            <option value="">All Finance Types</option>
            <option value="Credit">Credit</option>
            <option value="Cash">Cash</option>
            <option value="EMI">EMI</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Profession</label>
          <select class="form-select select2" id="filter_customer_profession" name="customer_profession">
            <option value="">All Professions</option>
            @foreach($professions as $profession)
              <option value="{{ $profession->name }}">{{ $profession->name }}</option>
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

<!-- Follow-up Action Modal -->
<div class="modal fade" id="followUpActionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="followUpActionModalTitle">Update Enquiry Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="followUpActionForm">
        <div class="modal-body">
          <input type="hidden" id="follow_up_enquiry_id" name="enquiry_id">
          <input type="hidden" id="follow_up_status" name="status">

          <div class="mb-3">
            <label class="form-label">Selected Action</label>
            <input type="text" class="form-control" id="follow_up_status_label" readonly>
          </div>

          <div class="mb-0">
            <label for="follow_up_remark" class="form-label">Follow-up Remark <span class="text-danger">*</span></label>
            <textarea
              class="form-control"
              id="follow_up_remark"
              name="follow_up_remark"
              rows="4"
              placeholder="Enter follow-up remark"
              required
            ></textarea>
            <small class="text-muted">Remark is required for every follow-up action.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submitFollowUpAction">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Add/Edit Enquiry Modal -->
<div class="modal fade" id="enquiryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="enquiryModalTitle">Add New Enquiry</h5>
        <div class="d-flex align-items-center gap-2 ms-auto">
          <button type="button" class="btn btn-sm btn-primary d-none" id="enableEditBtn">
            <i class="ti ti-edit me-1"></i>Edit
          </button>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body">
        <form id="enquiryModalForm">
          @include('content.enquiries.partials.form-fields')
        </form>
        <div id="followupHistorySection" class="mt-4 d-none">
          <h6 class="mb-3">Follow-up History</h6>
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Next Date</th>
                  <th>Remark</th>
                  <th>By</th>
                  <th>At</th>
                </tr>
              </thead>
              <tbody id="followupHistoryBody">
                <tr>
                  <td colspan="7" class="text-center text-muted">No follow-up history available.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="enquiryModalSubmitBtn" form="enquiryModalForm">Save Enquiry</button>
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
    professions: @json($professions),
    leadTypes: @json($leadTypes),
    viewStatus: '{{ $viewStatus }}'
  };
</script>
@vite(['resources/assets/js/app-enquiries.js'])
@endsection
