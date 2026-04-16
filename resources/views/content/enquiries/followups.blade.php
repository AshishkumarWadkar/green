@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Follow-ups')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('content')
<div class="card mb-4" data-followup-scope="{{ $scope ?? 'today' }}">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">{{ ($scope ?? 'today') === 'all' ? 'All Pending Follow-ups' : "Today's & Overdue Follow-ups" }}</h5>
    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFollowupFilter" aria-expanded="false" aria-controls="collapseFollowupFilter">
      <i class="ti ti-filter me-1"></i> Filters <i class="ti ti-chevron-down ms-1"></i>
    </button>
  </div>
  <div class="collapse" id="collapseFollowupFilter">
    <div class="card-body">
      <form id="followupFilterForm" class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Follow-up Date From</label>
          <input type="text" class="form-control flatpickr" id="filter_followup_date_from" name="date_from" placeholder="Select date">
        </div>
        <div class="col-md-3">
          <label class="form-label">Follow-up Date To</label>
          <input type="text" class="form-control flatpickr" id="filter_followup_date_to" name="date_to" placeholder="Select date">
        </div>
        @unless(auth()->user()->hasRole('Sales'))
        <div class="col-md-3">
          <label class="form-label">Assigned To</label>
          <select class="form-select select2" id="filter_followup_assigned" name="assigned_to">
            <option value="">All Users</option>
            @foreach($users as $user)
              <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
          </select>
        </div>
        @endunless
        <div class="col-md-12">
          <button type="button" class="btn btn-label-secondary" id="resetFollowupFilters">
            <i class="ti ti-refresh me-1"></i> Reset
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header border-bottom">
    <h6 class="card-title mb-0">Pending Follow-ups</h6>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-followups table border-top">
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>Follow-up Date</th>
          <th>Enquiry Date</th>
          <th>Customer</th>
          <th>Mobile</th>
          <th>Assigned To</th>
          <th>Last Follow-up Remark</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header border-bottom">
    <h6 class="card-title mb-0">Completed Follow-ups</h6>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-completed-followups table border-top">
      <thead>
        <tr>
          <th></th>
          <th>ID</th>
          <th>Follow-up Date</th>
          <th>Customer</th>
          <th>Mobile</th>
          <th>Assigned To</th>
          <th>Status</th>
          <th>Next Follow-up Date</th>
          <th>Remark</th>
          <th>Updated By</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<div class="modal fade" id="completeFollowupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Complete Follow-up</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="completeFollowupForm">
        <div class="modal-body">
          <input type="hidden" id="followup_record_id" name="followup_record_id">
          <input type="hidden" id="followup_enquiry_id" name="enquiry_id">
          <div class="mb-3">
            <label class="form-label">Customer</label>
            <input type="text" class="form-control" id="followup_customer_name" readonly>
          </div>
          <div class="mb-3">
            <label for="followup_status" class="form-label">Action <span class="text-danger">*</span></label>
            <select class="form-select" id="followup_status" name="status">
              <option value="Pending">Keep Pending</option>
              <option value="Accepted">Confirm Enquiry</option>
              <option value="Cancelled">Cancel Enquiry</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="followup_remark_input" class="form-label">Remark <span class="text-danger">*</span></label>
            <textarea class="form-control" id="followup_remark_input" name="remark" rows="3" placeholder="Enter follow-up remark"></textarea>
          </div>
          <div class="mb-0">
            <label for="followup_next_date" class="form-label">Next Follow-up Date <span class="text-danger" id="nextDateRequired">*</span></label>
            <input type="text" class="form-control followup-flatpickr" id="followup_next_date" name="next_follow_up_date" placeholder="Select next follow-up date">
            <small class="text-muted">Required only when action is Keep Pending.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Follow-up</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-enquiries-followups.js'])
@endsection
